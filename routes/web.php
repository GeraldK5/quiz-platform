<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;

use App\Http\Controllers\PerformanceController;

use App\Http\Controllers\ImportController;

//use App\Http\Controllers\challengeController;

use App\Http\Controllers\AdminController;

use Maatwebsite\Excel\Facades\Excel;

use App\Models\Question;

use App\Models\Answer;

use App\Models\Challenge;



Route::get('/', [HomeController::class, 'index']);

Route::get('/home', [HomeController::class, 'redirect']);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
Route::get('/admin', 'AdminController@home')->name('admin.home');

Route::get('/add_schools_view', [AdminController::class, 'addview']); //From the sidebar.blade.php

Route::get('/uploadschool', [AdminController::class, 'uploadschool'])->name('uploadschool'); //From the sidebar.blade.php

Route::post('/addschool', [AdminController::class, 'addschools']); //From the school.blade.php

Route::get('/retrieve_participants', [AdminController::class, 'participantview']);

Route::get('/retrieve_representatives', [AdminController::class, 'representative_view']);

Route::get('/participant_rejected', [AdminController::class, 'viewrejected']);

Route::get('/challenge', [AdminController::class, 'challengeview']);

Route::get('/addchallenge', function () {
    return view('admin.uploadchallenge');
})->name('addchallenge');

Route::post('/uploadchallenge', [AdminController::class, 'uploadchallenge'])->name('uploadchallenge');

Route::get('/view_reports', [AdminController::class, 'viewreport']);

Route::get('/view_attempts', [AdminController::class, 'viewattempt']);

Route::get('/school_performance', [PerformanceController::class, 'showSchoolPerformance']);

Route::get('/participant_performance/{participantId}', [PerformanceController::class, 'showParticipantPerformance']);

//For import the files
Route::get('/upload_files', [ImportController::class, 'showUploadForm']);
Route::get('/import_files', [ImportController::class, 'importFiles']);

//For challenges
// Route::get('challenges_create', [ChallengeController::class, 'create']);
// Route::post('challenges', [ChallengeController::class, 'store']);
Route::get('/school_ranking', [AdminController::class, 'rankSchools']);
Route::get('/incomplete_challenges', [AdminController::class, 'retrieveIncompleteChallenges']);
Route::get('/best_schools', [AdminController::class, 'bestPerformingSchools']);
Route::get('/worst_schools', function () {
    return view('admin.worst_school');
});
Route::get('/search-challenge', [AdminController::class, 'searchChallenge']);

#Most correctly answered question
Route::get('/analytics', [AdminController::class, 'showMostCorrectlyAnsweredQuestions']);

// In routes/web.php
Route::get('/participant/{studentNumber}/questions/', [AdminController::class, 'showrepetition']);

Route::get('/challenge/{id}/questions', [AdminController::class, 'showQuestions'])->name('challenge.questions');
