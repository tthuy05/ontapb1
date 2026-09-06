<?php

namespace App\Http\Controllers;

use App\Models\GrammarLesson;
use App\Models\Vocabulary;
use App\Models\VocabularyProgress;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $activeVocabularyCount = Vocabulary::query()
            ->active()
            ->whereHas('topic', fn ($query) => $query->active())
            ->count();

        $progressCounts = VocabularyProgress::query()
            ->whereHas('vocabulary', fn ($query) => $query
                ->active()
                ->whereHas('topic', fn ($topicQuery) => $topicQuery->active()))
            ->selectRaw('state, COUNT(*) as aggregate')
            ->groupBy('state')
            ->pluck('aggregate', 'state');

        $trackedCount = collect(['learning', 'learned', 'review'])
            ->sum(fn (string $state): int => (int) $progressCounts->get($state, 0));

        return view('dashboard.index', [
            'activeVocabularyCount' => $activeVocabularyCount,
            'activeGrammarCount' => GrammarLesson::query()
                ->active()
                ->whereHas('topic', fn ($query) => $query->active())
                ->count(),
            'progressCounts' => [
                'new' => max(0, $activeVocabularyCount - $trackedCount),
                'learning' => (int) $progressCounts->get('learning', 0),
                'learned' => (int) $progressCounts->get('learned', 0),
                'review' => (int) $progressCounts->get('review', 0),
            ],
        ]);
    }
}
