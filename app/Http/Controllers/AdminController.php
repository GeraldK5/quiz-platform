<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\School;

use App\Import;

use App\Models\Question;

use App\Models\Participants;

use App\Models\Rejected;

use App\Models\Attempt;

use App\Models\Challenge;

use App\Models\Answers;

use App\Models\Questions;

use App\Models\Representative;

use Maatwebsite\Excel\Facades\Excel;

use App\Imports\QuestionsImport;

use App\Imports\AnswersImport;

use App\Models\Analytics;

use Illuminate\Support\Facades\DB;

use App\Models\Answered_questions;

class AdminController extends Controller

{

    public function addview()
    {
        $schools = School::all();

        return view('admin.add_schools', ['schools' => $schools]);
    }

    public function uploadschool()
    {

        return view('admin.addschool');
    }
    public function addschools(Request $request)
    {
        $school = new school;

        $school->name = $request->name;
        $school->district = $request->district;
        $school->registration_number = $request->registration_number;
        $school->representative_name = $request->representative_name;
        $school->representative_email = $request->representative_email;

        $school->save();

        return redirect()->back()->with('message', 'School added successfully!');
    }

    public function participantview()
    {
        $data = participants::all();
        return view('admin.participant', compact('data'));
    }

    public function add_challenges()
    {
        return view('admin.challenges');
    }
    public function representative_view()
    {
        $data1 = reprsentative::all();
        return view('admin.representative', compact('data1'));
    }

    //Rejected participants function
    public function viewrejected()
    {
        $data = rejected::all();
        return view('admin.rejected', compact('data'));
    }

    //View reports
    public function viewreport()
    {


        return view('admin.report');
    }
    public function viewschools()
    {
        $schools = School::all();
        return view('admin.rejected', ['schools' => $schools]);
    }
    //view challenges
    public function challengeview()
    {
        $challenges = Challenge::orderBy('start_date', 'asc')->get();
        return view('admin.challenges', ['challenges' => $challenges]);
    }

    //View attempt
    public function viewattempt()
    {

        // Retrieve all attempts from the database
        $attempts = DB::table('attempt')
            ->select('studentNumber', 'challengeNumber', 'username', 'score', 'isComplete')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('attempt')
                    ->groupBy('studentNumber');
            })
            ->get();

        // Debug the data
        //dd($attempts);

        // Pass the attempts to the Blade view
        return view('admin.attempt', compact('attempts'));
    }

    public function rankSchools()
    {
        // Fetch data from the attempt table
        $attempts = Attempt::select('studentNumber', 'score')->get();

        // Calculate average marks for each registration number
        $averages = $attempts->groupBy(function ($item) {
            // Extract registration number from studentNumber
            return explode('/', $item->studentNumber)[0];
        })->map(function ($group) {
            return $group->avg('score');
        });

        // Sort schools by average marks in descending order and assign ranks
        $rankedSchools = $averages->sortDesc()->values()->map(function ($avg, $key) use ($averages) {
            return [
                'registration_number' => $averages->keys()[$key],
                'average_marks' => $avg,
                'rank' => $key + 1,
            ];
        });



        // Retrieve school names
        $result = $rankedSchools->map(function ($item) {
            $school = School::where('registration_number', $item['registration_number'])->first();
            return [
                'rank' => $item['rank'],
                'school_name' => $school->name,
                'average_marks' => $item['average_marks'],
            ];
        });

        // Pass the result to the view
        return view('admin.schoolRankings', ['schools' => $result]);
    }

    public function uploadchallenge(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'questionsFile' => 'required|mimes:xlsx,xls',
            'answersFile' => 'required|mimes:xlsx,xls',
            'challengeNumber' => 'required',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'duration' => 'required|integer|min:1',
            'questionsPerAttempt' => 'required|integer|min:1',
        ]);

        try {
            // Import Excel files
            $questionsImport = new QuestionsImport;
            $answersImport = new AnswersImport;

            Excel::import($questionsImport, $request->file('questionsFile'));
            Excel::import($answersImport, $request->file('answersFile'));

            // Insert Challenge data
            $challenge = new Challenge;
            $challenge->challengeNumber = $request->challengeNumber;
            $challenge->start_date = $request->startDate;
            $challenge->end_date = $request->endDate;
            $challenge->duration = $request->duration;
            $challenge->num_questions = $request->questionsPerAttempt;
            $challenge->save();

            // Retrieve the ID of the inserted challenge
            $challengeId = $challenge->id;

            // Insert Questions and Answers data
            $questionsData = $questionsImport->data;
            $answersData = $answersImport->data;


            foreach ($questionsData as $questionRow) {
                $question = new Questions;
                $question->question_text = $questionRow['question'];
                $question->marks = $questionRow['marks']; 
                $question->challengeNumber = $request->challengeNumber;
                $question->save();


                // Find corresponding answer from answers data
                $answerText = $this->findAnswerByQuestionNumber($questionRow['number'], $answersData);

                if ($answerText !== null) {
                    $answer = new Answers;
                    $answer->question_id = $question->id;
                    $answer->answer_text = $answerText; // Adjust according to your column names
                    $answer->save();
                }
            }

            session()->flash('success', 'Challenge Setup successfully.');
            $challenges = Challenge::orderBy('start_date', 'asc')->get();
            return view('admin.challenges', ['challenges' => $challenges]);
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            //Log::error('Error during data import: ' . $e->getMessage());


            // Flash an error message to the session
            session()->flash('error', 'An error occurred while importing data. Please try again.');
            dd($e);
            // Redirect back to the import form with old input
            return redirect()->back()->withInput();
        }
    }
    private function findAnswerByQuestionNumber($questionNumber, $answersData)
    {
        //dd($answersData);
        foreach ($answersData as $answerRow) {

            if ($answerRow['question_number'] == $questionNumber) {
                return $answerRow['answer']; // Adjust according to your column names
            }
        }
        return null;
    }

    public function retrieveIncompleteChallenges()
    {
        // Fetch incomplete challenges with correct column names
        $incompleteChallenges = DB::table('attempt')
            ->join('participants', 'attempt.studentNumber', '=', 'participants.studentNumber')
            ->select('participants.firstname', 'participants.lastname', 'participants.studentNumber', 'attempt.challengeNumber')
            ->where('attempt.isComplete', 'no') // Replace 'isComplete' with the actual column name
            ->get();

        return view('admin.incopmlete_challenge', ['incompleteChallenges' => $incompleteChallenges]);
    }
    public function bestPerformingSchools()
    {
        // Calculate average marks per registration_number from attempt table
        $averageMarks = DB::table('attempt')
            ->select(DB::raw('SUBSTRING_INDEX(studentNumber, "/", 1) AS registration_number'), DB::raw('AVG(score) as average_marks'))
            ->groupBy('registration_number')
            ->orderByDesc('average_marks')
            ->limit(3) // Limit to top 3 schools
            ->get();

        // Retrieve school details for the top performing registration_numbers
        $bestSchools = [];
        foreach ($averageMarks as $avgMark) {
            $registrationNumber = $avgMark->registration_number;

            $schoolDetails = DB::table('schools')
                ->select('name', 'district', 'registration_number')
                ->where('registration_number', $registrationNumber)
                ->first();

            if ($schoolDetails) {
                $bestSchools[] = [
                    'name' => $schoolDetails->name,
                    'district' => $schoolDetails->district,
                    'registration_number' => $schoolDetails->registration_number,
                    'average_marks' => round($avgMark->average_marks, 2), // Round to two decimal places
                ];
            }
        }

        return view('admin.best_schools', ['bestSchools' => $bestSchools]);
    }

    public function getWorstPerformingSchool($challengeNumber)
    {
        // Step 1: Extract registration_number and calculate the average score for each school
        $averageScores = DB::table('scores')
            ->select(DB::raw('SUBSTRING_INDEX(studentNumber, "/", 1) as registration_number'), DB::raw('AVG(score) as average_score'))
            ->where('challenge_number', $challengeNumber)
            ->groupBy(DB::raw('SUBSTRING_INDEX(studentNumber, "/", 1)'))
            ->get();

        // Step 2: Find the registration_number with the lowest average score
        $worstSchool = $averageScores->sortBy('average_score')->first();

        if ($worstSchool) {
            $registrationNumber = $worstSchool->registration_number;

            // Step 3: Fetch the details of the school with the lowest average score
            $schoolDetails = DB::table('schools')
                ->select('name', 'district')
                ->where('registration_number', $registrationNumber)
                ->first();

            if ($schoolDetails) {
                // Step 4: Print the name, district, and challenge number
                return response()->json([
                    'name' => $schoolDetails->name,
                    'district' => $schoolDetails->district,
                    'challenge_number' => $challengeNumber,
                ]);
            }
        }

        return response()->json(['message' => 'No data found'], 404);
    }
    public function searchChallenge(Request $request)
    {
        $challengeNumber = $request->input('challengeNumber');

        // Fetch the three worst performing schools' details
        $worstSchools = DB::table('attempt')
            ->select(
                DB::raw('SUBSTRING_INDEX(studentNumber, "/", 1) as registration_number'),
                DB::raw('AVG(score) as average_score')
            )
            ->where('challengeNumber', $challengeNumber)
            ->groupBy('registration_number')
            ->orderBy('average_score', 'asc')
            ->limit(3)
            ->get();

        $schoolDetailsList = [];

        foreach ($worstSchools as $worstSchool) {
            $schoolDetails = DB::table('schools')
                ->where('registration_number', $worstSchool->registration_number)
                ->first();

            if ($schoolDetails) {
                $schoolDetailsList[] = [
                    'name' => $schoolDetails->name,
                    'district' => $schoolDetails->district,
                    'challengeNumber' => $challengeNumber,
                    'average_score' => $worstSchool->average_score
                ];
            }
        }

        if (!empty($schoolDetailsList)) {
            return view('admin.searched_challenge', ['schoolDetailsList' => $schoolDetailsList]);
        }

        return view('admin.searched_challenge', ['error' => 'No data found for the given challenge number.']);
    }

    #most correctly answered question
    public function showMostCorrectlyAnsweredQuestions()
    {
        // Fetch analytics data from the database
        $analytics = DB::table('analytics')
            ->join('answers', 'analytics.answer_id', '=', 'answers.id')
            ->join('questions', 'answers.question_id', '=', 'questions.id')
            ->select(
                'questions.question_text',
                'answers.answer_text as answer_text',
                'analytics.entered_ans',
                'answers.question_id'
            )
            ->get();  // Complete the query by fetching the results

        // Calculate the percentage of correctly answered questions
        $questionStats = $analytics->groupBy('question_id')->map(function ($group) {
            $total = $group->count();
            $correct = $group->filter(function ($item) {
                return $item->answer_text === $item->entered_ans;
            })->count();

            // Calculate percentage using if statement
            if ($total > 0) {
                $percentage = ($correct / $total) * 100;
            } else {
                $percentage = 0;
            }

            return [
                'question_text' => $group->first()->question_text,  // Assuming all entries for a question_id have the same question_text
                'total' => $total,
                'correct' => $correct,
                'percentage' => $percentage,
            ];
        });

        // Convert to a collection and sort by percentage
        $sortedStats = collect($questionStats)->sortByDesc('percentage');

        // Prepare data for the view with rank
        $results = $sortedStats->map(function ($stats, $index) {
            return [
                'question_text' => $stats['question_text'],
                'percentage' => $stats['percentage'],
                'rank' => $index + 0,  // Rank starts at 1
            ];
        })->values();

        // Return the view with the analytics data
        return view('admin.analytics', compact('results'));
    }
    // In AdminController.php
    public function showrepetition($studentNumber)
    {
        printf($studentNumber);
        // Get all answered questions for the given participant
        $answeredQuestions = Answered_questions::where('studentNumber', $studentNumber)->get();
        if ($answeredQuestions->isEmpty()) {

            // Count the occurrences of each questionid
            $questionCounts = $answeredQuestions->groupBy('questionid')->map(function ($row) {
                return $row->count();
            });

            // Get the total number of answered questions
            $totalAnswers = $answeredQuestions->count();

            // Fetch the question text and calculate the repetition percentage
            $repetitionData = $questionCounts->map(function ($count, $questionid) use ($totalAnswers) {
                $questionText = Questions::where('id', $questionid)->pluck('question_text')->first();
                $percentage = ($count / $totalAnswers) * 100;
                return ['question_text' => $questionText, 'percentage' => $percentage, 'count' => $count];
            });

            return view('admin.repetition', ['repetitionData' => $repetitionData]);
        } else {
            $repetitionData = [];
            return view('admin.repetition', ['repetitionData' => $repetitionData]);
        }
    }
    public function showQuestions($id)
    {
        // Get the challenge based on the provided id
        $challenge = Challenge::findOrFail($id);

        // Get questions for the challenge
        $questions = Questions::where('challenge_id', $id)->get();

        // Prepare an array to store questions and their answers
        $questionsWithAnswers = [];

        foreach ($questions as $question) {
            // Find corresponding answer for each question
            $answer = Answers::where('question_id', $question->id)->first();

            // Push question and answer to the array
            $questionsWithAnswers[] = [
                'question' => $question->question_text,
                'answer' => $answer ? $answer->answer_text : 'No answer found',
            ];
        }

        // Pass data to the view
        return view('admin.questions', [
            'challenge' => $challenge,
            'questionsWithAnswers' => $questionsWithAnswers,
        ]);
    }
}
