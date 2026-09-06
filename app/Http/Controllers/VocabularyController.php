<?php

namespace App\Http\Controllers;

use App\Http\Requests\VocabularyProgressRequest;
use App\Models\Topic;
use App\Models\Vocabulary;
use App\Models\VocabularyProgress;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VocabularyController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $topicId = $request->integer('topic');
        $state = $request->string('state')->toString();

        $vocabularies = Vocabulary::query()
            ->with(['topic', 'progress'])
            ->active()
            ->whereHas('topic', fn (Builder $query) => $query->active())
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('term', 'like', "%{$search}%")
                    ->orWhere('definition', 'like', "%{$search}%")
                    ->orWhere('translation', 'like', "%{$search}%");
            }))
            ->when($topicId > 0, fn (Builder $query) => $query->where('topic_id', $topicId))
            ->when($state === 'new', fn (Builder $query) => $query->where(function (Builder $query): void {
                $query->whereDoesntHave('progress')
                    ->orWhereHas('progress', fn (Builder $progressQuery) => $progressQuery->where('state', 'new'));
            }))
            ->when(in_array($state, ['learning', 'learned', 'review'], true), fn (Builder $query) => $query
                ->whereHas('progress', fn (Builder $progressQuery) => $progressQuery->where('state', $state)))
            ->orderBy('term')
            ->paginate(20)
            ->withQueryString();

        $topics = Topic::query()
            ->active()
            ->whereIn('area', ['vocabulary', 'general'])
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        return view('vocabulary.index', compact('vocabularies', 'topics'));
    }

    public function show(Vocabulary $vocabulary): View
    {
        $vocabulary->load(['topic', 'progress']);
        abort_unless($vocabulary->status === 'active' && $vocabulary->topic->status === 'active', 404);

        return view('vocabulary.show', compact('vocabulary'));
    }

    public function updateProgress(
        VocabularyProgressRequest $request,
        Vocabulary $vocabulary,
    ): RedirectResponse {
        $vocabulary->load('topic');
        abort_unless($vocabulary->status === 'active' && $vocabulary->topic->status === 'active', 404);

        VocabularyProgress::query()->updateOrCreate(
            ['vocabulary_id' => $vocabulary->id],
            [
                'state' => $request->string('state')->toString(),
                'last_reviewed_at' => now(),
            ],
        );

        return back()->with('status', 'Vocabulary progress updated.');
    }
}
