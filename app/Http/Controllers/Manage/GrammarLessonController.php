<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manage\ContentStatusRequest;
use App\Http\Requests\Manage\GrammarLessonRequest;
use App\Models\GrammarLesson;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GrammarLessonController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $status = $request->string('status')->toString();
        $sourceType = $request->string('source_type')->toString();
        $topicId = $request->integer('topic');

        $lessons = GrammarLesson::query()
            ->with('topic')
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('objectives', 'like', "%{$search}%");
            }))
            ->when(in_array($status, GrammarLesson::STATUSES, true), fn (Builder $query) => $query->where('status', $status))
            ->when(in_array($sourceType, GrammarLesson::SOURCE_TYPES, true), fn (Builder $query) => $query->where('source_type', $sourceType))
            ->when($topicId > 0, fn (Builder $query) => $query->where('topic_id', $topicId))
            ->orderBy('position')
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        return view('manage.grammar.index', [
            'lessons' => $lessons,
            'topics' => $this->topics(),
        ]);
    }

    public function create(): View
    {
        return view('manage.grammar.create', ['topics' => $this->topics()]);
    }

    public function store(GrammarLessonRequest $request): RedirectResponse
    {
        $grammarLesson = GrammarLesson::query()->create($request->contentAttributes());

        return redirect()->route('manage.grammar.edit', $grammarLesson)
            ->with('status', 'Grammar lesson created.');
    }

    public function show(GrammarLesson $grammarLesson): View
    {
        $grammarLesson->load('topic');

        return view('grammar.show', [
            'grammarLesson' => $grammarLesson,
            'managePreview' => true,
        ]);
    }

    public function edit(GrammarLesson $grammarLesson): View
    {
        return view('manage.grammar.edit', [
            'grammarLesson' => $grammarLesson,
            'topics' => $this->topics(),
        ]);
    }

    public function update(GrammarLessonRequest $request, GrammarLesson $grammarLesson): RedirectResponse
    {
        $grammarLesson->update($request->contentAttributes());

        return redirect()->route('manage.grammar.edit', $grammarLesson)
            ->with('status', 'Grammar lesson updated.');
    }

    public function updateStatus(ContentStatusRequest $request, GrammarLesson $grammarLesson): RedirectResponse
    {
        $grammarLesson->update($request->validated());

        return back()->with('status', 'Grammar status updated.');
    }

    public function destroy(Request $request, GrammarLesson $grammarLesson): RedirectResponse
    {
        $request->validate(['confirm_delete' => ['accepted']]);

        if ($grammarLesson->status !== 'draft') {
            return back()->with('error', 'This lesson is published. Deactivate it instead.');
        }

        $grammarLesson->delete();

        return redirect()->route('manage.grammar.index')
            ->with('status', 'Unused draft grammar lesson deleted.');
    }

    private function topics()
    {
        return Topic::query()
            ->whereIn('area', ['grammar', 'general'])
            ->orderBy('position')
            ->orderBy('name')
            ->get();
    }
}
