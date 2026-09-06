<?php

namespace App\Http\Controllers;

use App\Models\Passage;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReadingController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $topicId = $request->integer('topic');
        $difficulty = $request->integer('difficulty');

        $passages = Passage::query()
            ->with('topic')
            ->withCount(['questions' => fn (Builder $query) => $query->active()])
            ->active()
            ->where(function (Builder $query): void {
                $query->whereNull('topic_id')->orWhereHas('topic', fn (Builder $topic): Builder => $topic->active());
            })
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")->orWhere('body', 'like', "%{$search}%");
            }))
            ->when($topicId > 0, fn (Builder $query) => $query->where('topic_id', $topicId))
            ->when(in_array($difficulty, Passage::DIFFICULTIES, true), fn (Builder $query) => $query->where('difficulty', $difficulty))
            ->orderBy('difficulty')
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        $topics = Topic::query()->active()->whereIn('area', ['reading', 'general'])->orderBy('position')->orderBy('name')->get();

        return view('reading.index', compact('passages', 'topics'));
    }

    public function show(Passage $passage): View
    {
        $passage->load([
            'topic',
            'questions' => fn ($query) => $query->active()->with([
                'options' => fn ($options) => $options
                    ->select(['id', 'question_id', 'option_key', 'content', 'position'])
                    ->orderBy('position'),
            ]),
        ]);

        abort_unless($passage->status === 'active' && ($passage->topic === null || $passage->topic->status === 'active'), 404);

        return view('reading.show', compact('passage'));
    }
}
