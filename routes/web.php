<?php

use App\Http\Controllers\AttemptController;
use App\Http\Controllers\Auth\OwnerSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GrammarController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\ListeningController;
use App\Http\Controllers\Manage\DashboardController as ManageDashboardController;
use App\Http\Controllers\Manage\ExerciseController as ManageExerciseController;
use App\Http\Controllers\Manage\GrammarLessonController as ManageGrammarLessonController;
use App\Http\Controllers\Manage\ListeningContentController as ManageListeningContentController;
use App\Http\Controllers\Manage\PassageController as ManagePassageController;
use App\Http\Controllers\Manage\QuestionController as ManageQuestionController;
use App\Http\Controllers\Manage\TopicController as ManageTopicController;
use App\Http\Controllers\Manage\VocabularyController as ManageVocabularyController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\ReadingController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\VocabularyController;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

Route::get('/health', HealthController::class)
    ->withoutMiddleware([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        ShareErrorsFromSession::class,
        PreventRequestForgery::class,
    ])
    ->middleware('throttle:health')
    ->name('health');

Route::get('/login', [OwnerSessionController::class, 'create'])->name('login');
Route::post('/login', [OwnerSessionController::class, 'store'])
    ->middleware('throttle:owner-login')
    ->name('login.store');

Route::middleware('owner')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/vocabulary', [VocabularyController::class, 'index'])->name('vocabulary.index');
    Route::get('/vocabulary/{vocabulary}', [VocabularyController::class, 'show'])->name('vocabulary.show');
    Route::patch('/vocabulary/{vocabulary}/progress', [VocabularyController::class, 'updateProgress'])
        ->name('vocabulary.progress.update');

    Route::get('/grammar', [GrammarController::class, 'index'])->name('grammar.index');
    Route::get('/grammar/{grammarLesson}', [GrammarController::class, 'show'])->name('grammar.show');

    Route::get('/reading', [ReadingController::class, 'index'])->name('reading.index');
    Route::get('/reading/{passage}', [ReadingController::class, 'show'])->name('reading.show');
    Route::get('/listening', [ListeningController::class, 'index'])->name('listening.index');
    Route::get('/listening/{listeningContent}', [ListeningController::class, 'show'])->name('listening.show');

    Route::get('/practice', [PracticeController::class, 'index'])->name('practice.index');
    Route::get('/practice/{exercise}', [PracticeController::class, 'show'])->name('practice.show');
    Route::post('/practice/{exercise}/attempts', [AttemptController::class, 'storeForExercise'])
        ->name('practice.attempts.store');
    Route::get('/attempts/{attempt}', [AttemptController::class, 'show'])->name('attempts.show');
    Route::put('/attempts/{attempt}/answers/{attemptAnswer}', [AttemptController::class, 'updateAnswer'])
        ->name('attempts.answers.update');
    Route::post('/attempts/{attempt}/submit', [AttemptController::class, 'submit'])->name('attempts.submit');
    Route::get('/attempts/{attempt}/result', [ResultController::class, 'show'])->name('attempts.result');

    Route::prefix('manage')->name('manage.')->group(function (): void {
        Route::get('/', ManageDashboardController::class)->name('dashboard');

        Route::patch('/topics/{topic}/status', [ManageTopicController::class, 'updateStatus'])
            ->name('topics.status.update');
        Route::resource('topics', ManageTopicController::class)->except('show');

        Route::patch('/vocabulary/{vocabulary}/status', [ManageVocabularyController::class, 'updateStatus'])
            ->name('vocabulary.status.update');
        Route::resource('vocabulary', ManageVocabularyController::class);

        Route::patch('/grammar/{grammarLesson}/status', [ManageGrammarLessonController::class, 'updateStatus'])
            ->name('grammar.status.update');
        Route::resource('grammar', ManageGrammarLessonController::class)
            ->parameters(['grammar' => 'grammarLesson']);

        Route::patch('/reading/{passage}/status', [ManagePassageController::class, 'updateStatus'])
            ->name('reading.status.update');
        Route::resource('reading', ManagePassageController::class)
            ->parameters(['reading' => 'passage']);

        Route::patch('/listening/{listeningContent}/status', [ManageListeningContentController::class, 'updateStatus'])
            ->name('listening.status.update');
        Route::resource('listening', ManageListeningContentController::class)
            ->parameters(['listening' => 'listeningContent']);

        Route::patch('/questions/{question}/status', [ManageQuestionController::class, 'updateStatus'])
            ->name('questions.status.update');
        Route::resource('questions', ManageQuestionController::class);

        Route::patch('/exercises/{exercise}/status', [ManageExerciseController::class, 'updateStatus'])
            ->name('exercises.status.update');
        Route::get('/exercises/{exercise}/preview', [ManageExerciseController::class, 'preview'])
            ->name('exercises.preview');
        Route::resource('exercises', ManageExerciseController::class);
    });

    Route::post('/logout', [OwnerSessionController::class, 'destroy'])->name('logout');
});
