<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;

use App\Http\Controllers\AdminController;

use App\Http\Controllers\UploadController;

use Maatwebsite\Excel\Facades\Excel;

use App\Imports\QuestionsImport;

use App\Imports\AnswersImport;

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

Route::get('/add_schools_view', [AdminController::class, 'addview']);//From the sidebar.blade.php

Route::post('/addschool', [AdminController::class, 'addschools']); //From the school.blade.php

Route::get('/retrieve_participants', [AdminController::class, 'participantview']);

Route::get('/retrieve_representatives', [AdminController::class, 'representative_view']);

Route::get('/participant_rejected', [AdminController::class, 'viewrejected']);

Route::get('/view_reports', [AdminController::class, 'viewreport']);

Route::get('/view_attempts', [AdminController::class, 'viewattempt']);

Route::get('/view_upload', [UploadController::class, 'upload']); //from the sidebar.blade.php

Route::post('/upload', [UploadController::class, 'viewupload'])->name('upload'); //from the upload.blade.php

// Route::get('/challenge', [AdminController::class, 'add_challenge']);
Route::get('/challenge', function () {
    $challenges = Challenge::orderBy('start_date', 'asc')->get();
    return view('admin.challenges', ['challenges' => $challenges]);
});
