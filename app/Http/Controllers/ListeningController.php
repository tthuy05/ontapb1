<?php

namespace App\Http\Controllers;

use App\Models\ListeningContent;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListeningController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $topicId = $request->integer('topic');
        $difficulty = $request->integer('difficulty');

        $contents = ListeningContent::query()
            ->with('topic')
            ->withCount(['questions' => fn (Builder $query) => $query->active()])
            ->active()
            ->where(function (Builder $query): void {
                $query->whereNull('topic_id')->orWhereHas('topic', fn (Builder $topic): Builder => $topic->active());
            })
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")->orWhere('transcript', 'like', "%{$search}%");
            }))
            ->when($topicId > 0, fn (Builder $query) => $query->where('topic_id', $topicId))
            ->when(in_array($difficulty, ListeningContent::DIFFICULTIES, true), fn (Builder $query) => $query->where('difficulty', $difficulty))
            ->orderBy('difficulty')
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        $topics = Topic::query()->active()->whereIn('area', ['listening', 'general'])->orderBy('position')->orderBy('name')->get();

        return view('listening.index', compact('contents', 'topics'));
    }

    public function show(ListeningContent $listeningContent): View
    {
        $listeningContent->load([
            'topic',
            'questions' => fn ($query) => $query->active()->with([
                'options' => fn ($options) => $options
                    ->select(['id', 'question_id', 'option_key', 'content', 'position'])
                    ->orderBy('position'),
            ]),
        ]);

        abort_unless($listeningContent->status === 'active' && ($listeningContent->topic === null || $listeningContent->topic->status === 'active'), 404);

        return view('listening.show', compact('listeningContent'));
    }
}
