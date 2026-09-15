<?php

use App\Http\Controllers\AttemptController;
use App\Http\Controllers\Auth\OwnerSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\GrammarController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ListeningController;
use App\Http\Controllers\Manage\DashboardController as ManageDashboardController;
use App\Http\Controllers\Manage\ExamController as ManageExamController;
use App\Http\Controllers\Manage\ExerciseController as ManageExerciseController;
use App\Http\Controllers\Manage\GrammarLessonController as ManageGrammarLessonController;
use App\Http\Controllers\Manage\ListeningContentController as ManageListeningContentController;
use App\Http\Controllers\Manage\PassageController as ManagePassageController;
use App\Http\Controllers\Manage\QuestionController as ManageQuestionController;
use App\Http\Controllers\Manage\SpeakingPromptController as ManageSpeakingPromptController;
use App\Http\Controllers\Manage\TopicController as ManageTopicController;
use App\Http\Controllers\Manage\VocabularyController as ManageVocabularyController;
use App\Http\Controllers\Manage\WritingPromptController as ManageWritingPromptController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\ReadingController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SpeakingController;
use App\Http\Controllers\VocabularyController;
use App\Http\Controllers\VocabularyReviewController;
use App\Http\Controllers\WritingController;
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
    Route::get('/vocabulary/review', [VocabularyReviewController::class, 'index'])->name('vocabulary.review.index');
    Route::post('/vocabulary/{vocabulary}/review', [VocabularyReviewController::class, 'store'])
        ->middleware('throttle:study-write')
        ->name('vocabulary.review.store');
    Route::get('/vocabulary/{vocabulary}', [VocabularyController::class, 'show'])->name('vocabulary.show');
    Route::patch('/vocabulary/{vocabulary}/progress', [VocabularyController::class, 'updateProgress'])
        ->middleware('throttle:study-write')
        ->name('vocabulary.progress.update');

    Route::get('/grammar', [GrammarController::class, 'index'])->name('grammar.index');
    Route::get('/grammar/{grammarLesson}', [GrammarController::class, 'show'])->name('grammar.show');

    Route::get('/reading', [ReadingController::class, 'index'])->name('reading.index');
    Route::get('/reading/{passage}', [ReadingController::class, 'show'])->name('reading.show');
    Route::get('/listening', [ListeningController::class, 'index'])->name('listening.index');
    Route::get('/listening/{listeningContent}', [ListeningController::class, 'show'])->name('listening.show');

    Route::get('/writing', [WritingController::class, 'index'])->name('writing.index');
    Route::get('/writing/submissions/{writingSubmission}', [WritingController::class, 'showSubmission'])->name('writing.submissions.show');
    Route::patch('/writing/submissions/{writingSubmission}', [WritingController::class, 'update'])
        ->middleware('throttle:study-write')
        ->name('writing.submissions.update');
    Route::post('/writing/{writingPrompt}/submissions', [WritingController::class, 'store'])
        ->middleware('throttle:study-write')
        ->name('writing.submissions.store');
    Route::get('/writing/{writingPrompt}', [WritingController::class, 'show'])->name('writing.show');

    Route::get('/speaking', [SpeakingController::class, 'index'])->name('speaking.index');
    Route::get('/speaking/submissions/{speakingSubmission}', [SpeakingController::class, 'showSubmission'])->name('speaking.submissions.show');
    Route::patch('/speaking/submissions/{speakingSubmission}', [SpeakingController::class, 'update'])
        ->middleware('throttle:study-write')
        ->name('speaking.submissions.update');
    Route::post('/speaking/{speakingPrompt}/submissions', [SpeakingController::class, 'store'])
        ->middleware('throttle:study-write')
        ->name('speaking.submissions.store');
    Route::get('/speaking/{speakingPrompt}', [SpeakingController::class, 'show'])->name('speaking.show');

    Route::get('/practice', [PracticeController::class, 'index'])->name('practice.index');
    Route::get('/practice/{exercise}', [PracticeController::class, 'show'])->name('practice.show');
    Route::post('/practice/{exercise}/attempts', [AttemptController::class, 'storeForExercise'])
        ->middleware('throttle:study-write')
        ->name('practice.attempts.store');
    Route::get('/attempts/{attempt}', [AttemptController::class, 'show'])->name('attempts.show');
    Route::get('/attempts/{attempt}/sections/{sectionPosition}', [AttemptController::class, 'showSection'])
        ->whereNumber('sectionPosition')
        ->name('attempts.sections.show');
    Route::put('/attempts/{attempt}/answers/{attemptAnswer}', [AttemptController::class, 'updateAnswer'])
        ->middleware('throttle:study-write')
        ->name('attempts.answers.update');
    Route::post('/attempts/{attempt}/submit', [AttemptController::class, 'submit'])
        ->middleware('throttle:study-write')
        ->name('attempts.submit');
    Route::get('/attempts/{attempt}/result', [ResultController::class, 'show'])->name('attempts.result');
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    Route::get('/review/wrong-answers', [ReviewController::class, 'index'])->name('review.wrong.index');
    Route::get('/review/wrong-answers/{attemptAnswer}', [ReviewController::class, 'show'])->name('review.wrong.show');

    Route::get('/exams', [ExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/{exam}', [ExamController::class, 'show'])->name('exams.show');
    Route::post('/exams/{exam}/attempts', [AttemptController::class, 'storeForExam'])
        ->middleware('throttle:study-write')
        ->name('exams.attempts.store');

    Route::prefix('manage')->name('manage.')->group(function (): void {
        Route::get('/', ManageDashboardController::class)->name('dashboard');

        Route::patch('/topics/{topic}/status', [ManageTopicController::class, 'updateStatus'])
            ->middleware('throttle:study-write')
            ->name('topics.status.update');
        Route::resource('topics', ManageTopicController::class)->except('show')
            ->middlewareFor(['store', 'update', 'destroy'], 'throttle:study-write');

        Route::patch('/vocabulary/{vocabulary}/status', [ManageVocabularyController::class, 'updateStatus'])
            ->middleware('throttle:study-write')
            ->name('vocabulary.status.update');
        Route::resource('vocabulary', ManageVocabularyController::class)
            ->middlewareFor(['store', 'update', 'destroy'], 'throttle:study-write');

        Route::patch('/grammar/{grammarLesson}/status', [ManageGrammarLessonController::class, 'updateStatus'])
            ->middleware('throttle:study-write')
            ->name('grammar.status.update');
        Route::resource('grammar', ManageGrammarLessonController::class)
            ->parameters(['grammar' => 'grammarLesson'])
            ->middlewareFor(['store', 'update', 'destroy'], 'throttle:study-write');

        Route::patch('/reading/{passage}/status', [ManagePassageController::class, 'updateStatus'])
            ->middleware('throttle:study-write')
            ->name('reading.status.update');
        Route::resource('reading', ManagePassageController::class)
            ->parameters(['reading' => 'passage'])
            ->middlewareFor(['store', 'update', 'destroy'], 'throttle:study-write');

        Route::patch('/listening/{listeningContent}/status', [ManageListeningContentController::class, 'updateStatus'])
            ->middleware('throttle:study-write')
            ->name('listening.status.update');
        Route::resource('listening', ManageListeningContentController::class)
            ->parameters(['listening' => 'listeningContent'])
            ->middlewareFor(['store', 'update', 'destroy'], 'throttle:study-write');

        Route::patch('/questions/{question}/status', [ManageQuestionController::class, 'updateStatus'])
            ->middleware('throttle:study-write')
            ->name('questions.status.update');
        Route::resource('questions', ManageQuestionController::class)
            ->middlewareFor(['store', 'update', 'destroy'], 'throttle:study-write');

        Route::patch('/exercises/{exercise}/status', [ManageExerciseController::class, 'updateStatus'])
            ->middleware('throttle:study-write')
            ->name('exercises.status.update');
        Route::get('/exercises/{exercise}/preview', [ManageExerciseController::class, 'preview'])
            ->name('exercises.preview');
        Route::resource('exercises', ManageExerciseController::class)
            ->middlewareFor(['store', 'update', 'destroy'], 'throttle:study-write');

        Route::patch('/exams/{exam}/status', [ManageExamController::class, 'updateStatus'])
            ->middleware('throttle:study-write')
            ->name('exams.status.update');
        Route::get('/exams/{exam}/preview', [ManageExamController::class, 'preview'])
            ->name('exams.preview');
        Route::patch('/exams/{exam}/items/order', [ManageExamController::class, 'updateOrder'])
            ->middleware('throttle:study-write')
            ->name('exams.items.order.update');
        Route::resource('exams', ManageExamController::class)
            ->middlewareFor(['store', 'update', 'destroy'], 'throttle:study-write');

        Route::patch('/writing/{writingPrompt}/status', [ManageWritingPromptController::class, 'updateStatus'])
            ->middleware('throttle:study-write')
            ->name('writing.status.update');
        Route::get('/writing/{writingPrompt}/preview', [ManageWritingPromptController::class, 'preview'])->name('writing.preview');
        Route::resource('writing', ManageWritingPromptController::class)
            ->parameters(['writing' => 'writingPrompt'])
            ->middlewareFor(['store', 'update', 'destroy'], 'throttle:study-write');
        Route::patch('/speaking/{speakingPrompt}/status', [ManageSpeakingPromptController::class, 'updateStatus'])
            ->middleware('throttle:study-write')
            ->name('speaking.status.update');
        Route::get('/speaking/{speakingPrompt}/preview', [ManageSpeakingPromptController::class, 'preview'])->name('speaking.preview');
        Route::resource('speaking', ManageSpeakingPromptController::class)
            ->parameters(['speaking' => 'speakingPrompt'])
            ->middlewareFor(['store', 'update', 'destroy'], 'throttle:study-write');
    });

    Route::post('/logout', [OwnerSessionController::class, 'destroy'])->name('logout');
});
