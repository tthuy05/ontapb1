<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manage\ContentStatusRequest;
use App\Http\Requests\Manage\PassageRequest;
use App\Models\Passage;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PassageController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $status = $request->string('status')->toString();
        $sourceType = $request->string('source_type')->toString();
        $topicId = $request->integer('topic');

        $passages = Passage::query()->with('topic')->withCount('questions')
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")->orWhere('body', 'like', "%{$search}%");
            }))
            ->when(in_array($status, Passage::STATUSES, true), fn (Builder $query) => $query->where('status', $status))
            ->when(in_array($sourceType, Passage::SOURCE_TYPES, true), fn (Builder $query) => $query->where('source_type', $sourceType))
            ->when($topicId > 0, fn (Builder $query) => $query->where('topic_id', $topicId))
            ->orderBy('title')->paginate(20)->withQueryString();

        return view('manage.reading.index', ['passages' => $passages, 'topics' => $this->topics()]);
    }

    public function create(): View
    {
        return view('manage.reading.create', ['topics' => $this->topics()]);
    }

    public function store(PassageRequest $request): RedirectResponse
    {
        $passage = Passage::query()->create($request->contentAttributes());

        return redirect()->route('manage.reading.edit', $passage)->with('status', 'Reading passage created.');
    }

    public function show(Passage $passage): View
    {
        $passage->load(['topic', 'questions.options']);

        return view('manage.reading.show', compact('passage'));
    }

    public function edit(Passage $passage): View
    {
        return view('manage.reading.edit', ['passage' => $passage, 'topics' => $this->topics()]);
    }

    public function update(PassageRequest $request, Passage $passage): RedirectResponse
    {
        $passage->update($request->contentAttributes());

        return redirect()->route('manage.reading.edit', $passage)->with('status', 'Reading passage updated.');
    }

    public function updateStatus(ContentStatusRequest $request, Passage $passage): RedirectResponse
    {
        if ($request->validated('status') === 'active' && ($errors = $this->activationErrors($passage)) !== []) {
            return back()->with('error', implode(' ', $errors));
        }

        $passage->update($request->validated());

        return back()->with('status', 'Reading passage status updated.');
    }

    public function destroy(Request $request, Passage $passage): RedirectResponse
    {
        $request->validate(['confirm_delete' => ['accepted']]);
        if ($passage->status !== 'draft' || $passage->questions()->exists()) {
            return back()->with('error', 'This passage is published or referenced by questions. Deactivate it instead.');
        }
        $passage->delete();

        return redirect()->route('manage.reading.index')->with('status', 'Unused draft passage deleted.');
    }

    private function topics()
    {
        return Topic::query()->whereIn('area', ['reading', 'general'])->orderBy('position')->orderBy('name')->get();
    }

    private function activationErrors(Passage $passage): array
    {
        $errors = [];
        if ($passage->word_count < 1 || trim($passage->body) === '') {
            $errors[] = 'A passage must contain readable text.';
        }
        if ($passage->cefr_level !== 'B1' || ! in_array($passage->difficulty, Passage::DIFFICULTIES, true)) {
            $errors[] = 'A passage must use the supported B1 difficulty settings.';
        }
        if ($passage->topic_id !== null && (! $passage->topic || $passage->topic->status !== 'active')) {
            $errors[] = 'The passage topic must be active before the passage can be activated.';
        }
        if ($passage->source_type === 'original' && trim((string) $passage->source_notes) === '' && trim((string) $passage->source_reference) === '') {
            $errors[] = 'Original passages require a provenance note.';
        }
        if ($passage->source_type !== 'original' && trim((string) $passage->source_reference) === '') {
            $errors[] = 'Non-original passages require a source reference.';
        }

        return $errors;
    }
}
