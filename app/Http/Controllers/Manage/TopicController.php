<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manage\ContentStatusRequest;
use App\Http\Requests\Manage\TopicRequest;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TopicController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $status = $request->string('status')->toString();
        $area = $request->string('area')->toString();

        $topics = Topic::query()
            ->withCount(['vocabularies', 'grammarLessons'])
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            }))
            ->when(in_array($status, Topic::STATUSES, true), fn (Builder $query) => $query->where('status', $status))
            ->when(in_array($area, Topic::AREAS, true), fn (Builder $query) => $query->where('area', $area))
            ->orderBy('area')
            ->orderBy('position')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('manage.topics.index', compact('topics'));
    }

    public function create(): View
    {
        return view('manage.topics.create');
    }

    public function store(TopicRequest $request): RedirectResponse
    {
        $topic = Topic::query()->create($request->validated());

        return redirect()->route('manage.topics.edit', $topic)
            ->with('status', 'Topic created.');
    }

    public function edit(Topic $topic): View
    {
        return view('manage.topics.edit', compact('topic'));
    }

    public function update(TopicRequest $request, Topic $topic): RedirectResponse
    {
        $topic->update($request->validated());

        return redirect()->route('manage.topics.edit', $topic)
            ->with('status', 'Topic updated.');
    }

    public function updateStatus(ContentStatusRequest $request, Topic $topic): RedirectResponse
    {
        $topic->update($request->validated());

        return back()->with('status', 'Topic status updated.');
    }

    public function destroy(Request $request, Topic $topic): RedirectResponse
    {
        $request->validate(['confirm_delete' => ['accepted']]);

        if ($topic->status !== 'draft' || $topic->vocabularies()->exists() || $topic->grammarLessons()->exists()) {
            return back()->with('error', 'This topic is in use or published. Deactivate it instead.');
        }

        $topic->delete();

        return redirect()->route('manage.topics.index')->with('status', 'Unused draft topic deleted.');
    }
}
