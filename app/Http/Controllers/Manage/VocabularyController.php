<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manage\ContentStatusRequest;
use App\Http\Requests\Manage\VocabularyRequest;
use App\Models\Topic;
use App\Models\Vocabulary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VocabularyController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $status = $request->string('status')->toString();
        $sourceType = $request->string('source_type')->toString();
        $topicId = $request->integer('topic');

        $vocabularies = Vocabulary::query()
            ->with(['topic', 'progress'])
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('term', 'like', "%{$search}%")
                    ->orWhere('definition', 'like', "%{$search}%")
                    ->orWhere('translation', 'like', "%{$search}%");
            }))
            ->when(in_array($status, Vocabulary::STATUSES, true), fn (Builder $query) => $query->where('status', $status))
            ->when(in_array($sourceType, Vocabulary::SOURCE_TYPES, true), fn (Builder $query) => $query->where('source_type', $sourceType))
            ->when($topicId > 0, fn (Builder $query) => $query->where('topic_id', $topicId))
            ->orderBy('term')
            ->paginate(20)
            ->withQueryString();

        return view('manage.vocabulary.index', [
            'vocabularies' => $vocabularies,
            'topics' => $this->topics(),
        ]);
    }

    public function create(): View
    {
        return view('manage.vocabulary.create', ['topics' => $this->topics()]);
    }

    public function store(VocabularyRequest $request): RedirectResponse
    {
        $vocabulary = Vocabulary::query()->create($request->validated());

        return redirect()->route('manage.vocabulary.edit', $vocabulary)
            ->with('status', 'Vocabulary entry created.');
    }

    public function show(Vocabulary $vocabulary): View
    {
        $vocabulary->load(['topic', 'progress']);

        return view('vocabulary.show', [
            'vocabulary' => $vocabulary,
            'managePreview' => true,
        ]);
    }

    public function edit(Vocabulary $vocabulary): View
    {
        return view('manage.vocabulary.edit', [
            'vocabulary' => $vocabulary,
            'topics' => $this->topics(),
        ]);
    }

    public function update(VocabularyRequest $request, Vocabulary $vocabulary): RedirectResponse
    {
        $vocabulary->update($request->validated());

        return redirect()->route('manage.vocabulary.edit', $vocabulary)
            ->with('status', 'Vocabulary entry updated.');
    }

    public function updateStatus(ContentStatusRequest $request, Vocabulary $vocabulary): RedirectResponse
    {
        $vocabulary->update($request->validated());

        return back()->with('status', 'Vocabulary status updated.');
    }

    public function destroy(Request $request, Vocabulary $vocabulary): RedirectResponse
    {
        $request->validate(['confirm_delete' => ['accepted']]);

        if ($vocabulary->status !== 'draft' || $vocabulary->progress()->exists()) {
            return back()->with('error', 'This entry is published or has progress. Deactivate it instead.');
        }

        $vocabulary->delete();

        return redirect()->route('manage.vocabulary.index')
            ->with('status', 'Unused draft vocabulary entry deleted.');
    }

    private function topics()
    {
        return Topic::query()
            ->whereIn('area', ['vocabulary', 'general'])
            ->orderBy('position')
            ->orderBy('name')
            ->get();
    }
}
