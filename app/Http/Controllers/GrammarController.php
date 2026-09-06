<?php

namespace App\Http\Controllers;

use App\Models\GrammarLesson;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GrammarController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $topicId = $request->integer('topic');

        $lessons = GrammarLesson::query()
            ->with('topic')
            ->active()
            ->whereHas('topic', fn (Builder $query) => $query->active())
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('objectives', 'like', "%{$search}%");
            }))
            ->when($topicId > 0, fn (Builder $query) => $query->where('topic_id', $topicId))
            ->orderBy('position')
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        $topics = Topic::query()
            ->active()
            ->whereIn('area', ['grammar', 'general'])
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        return view('grammar.index', compact('lessons', 'topics'));
    }

    public function show(GrammarLesson $grammarLesson): View
    {
        $grammarLesson->load('topic');
        abort_unless($grammarLesson->status === 'active' && $grammarLesson->topic->status === 'active', 404);

        return view('grammar.show', compact('grammarLesson'));
    }
}
