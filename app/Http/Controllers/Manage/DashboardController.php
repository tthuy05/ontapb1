<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\Exam;
use App\Models\GrammarLesson;
use App\Models\ListeningContent;
use App\Models\Passage;
use App\Models\Question;
use App\Models\Topic;
use App\Models\Vocabulary;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('manage.index', [
            'counts' => [
                'topics' => Topic::query()->count(),
                'vocabulary' => Vocabulary::query()->count(),
                'grammar' => GrammarLesson::query()->count(),
                'reading' => Passage::query()->count(),
                'listening' => ListeningContent::query()->count(),
                'questions' => Question::query()->count(),
                'exercises' => Exercise::query()->count(),
                'exams' => Exam::query()->count(),
                'drafts' => Topic::query()->where('status', 'draft')->count()
                    + Vocabulary::query()->where('status', 'draft')->count()
                    + GrammarLesson::query()->where('status', 'draft')->count()
                    + Passage::query()->where('status', 'draft')->count()
                    + ListeningContent::query()->where('status', 'draft')->count()
                    + Question::query()->where('status', 'draft')->count()
                    + Exercise::query()->where('status', 'draft')->count()
                    + Exam::query()->where('status', 'draft')->count(),
            ],
        ]);
    }
}
