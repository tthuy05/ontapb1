<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manage\ContentStatusRequest;
use App\Http\Requests\Manage\ExamOrderRequest;
use App\Http\Requests\Manage\ExamRequest;
use App\Models\Exam;
use App\Models\ExamSection;
use App\Models\Question;
use App\Services\ExamCompositionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function __construct(private readonly ExamCompositionService $composition) {}

    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $format = $request->string('format')->toString();
        $status = $request->string('status')->toString();

        $exams = Exam::query()
            ->withCount(['sections', 'attempts'])
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->when(in_array($format, Exam::FORMAT_LABELS, true), fn (Builder $query) => $query->where('format_label', $format))
            ->when(in_array($status, Exam::STATUSES, true), fn (Builder $query) => $query->where('status', $status))
            ->orderBy('status')->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        return view('manage.exams.index', compact('exams'));
    }

    public function create(): View
    {
        return view('manage.exams.create', array_merge(['exam' => new Exam()], $this->formOptions()));
    }

    public function store(ExamRequest $request): RedirectResponse
    {
        $exam = DB::transaction(function () use ($request): Exam {
            $exam = Exam::query()->create($request->contentAttributes());
            $this->syncComposition($exam, $request);

            return $exam;
        });

        return redirect()->route('manage.exams.edit', $exam)->with('status', 'Exam created.');
    }

    public function show(Exam $exam): View
    {
        $this->composition->load($exam);

        return view('manage.exams.show', compact('exam'));
    }

    public function preview(Exam $exam): View
    {
        $this->composition->load($exam);

        return view('manage.exams.preview', compact('exam'));
    }

    public function edit(Exam $exam): View
    {
        $this->composition->load($exam);

        return view('manage.exams.edit', array_merge([
            'exam' => $exam,
            'sectionsText' => $this->sectionsText($exam),
            'itemsText' => $this->itemsText($exam),
        ], $this->formOptions()));
    }

    public function update(ExamRequest $request, Exam $exam): RedirectResponse
    {
        DB::transaction(function () use ($request, $exam): void {
            $exam->update($request->contentAttributes());
            $this->syncComposition($exam, $request);
        });

        return redirect()->route('manage.exams.edit', $exam)->with('status', 'Exam updated.');
    }

    public function updateStatus(ContentStatusRequest $request, Exam $exam): RedirectResponse
    {
        if ($request->validated('status') === 'active' && ($errors = $this->composition->activationErrors($exam)) !== []) {
            return back()->with('error', implode(' ', $errors));
        }

        $exam->update($request->validated());

        return back()->with('status', 'Exam status updated.');
    }

    public function updateOrder(ExamOrderRequest $request, Exam $exam): RedirectResponse
    {
        $exam->load('sections.items');
        $payload = collect($request->validated('sections'));
        $sectionIds = $exam->sections->pluck('id')->sort()->values()->all();
        $submittedSectionIds = $payload->pluck('id')->map(fn ($id): int => (int) $id)->sort()->values()->all();

        if ($sectionIds !== $submittedSectionIds) {
            return back()->with('error', 'The submitted section order does not belong to this exam.');
        }

        $sectionPositions = $payload->pluck('position')->map(fn ($position): int => (int) $position)->sort()->values()->all();
        if ($sectionPositions !== range(0, max(0, $payload->count() - 1))) {
            return back()->with('error', 'Exam sections must use contiguous positions starting from zero.');
        }

        DB::transaction(function () use ($payload, $exam): void {
            foreach ($payload as $sectionData) {
                $section = $exam->sections->firstWhere('id', (int) $sectionData['id']);
                $itemIds = $section->items->pluck('id')->sort()->values()->all();
                $submittedItemIds = collect($sectionData['items'])->pluck('id')->map(fn ($id): int => (int) $id)->sort()->values()->all();
                abort_unless($itemIds === $submittedItemIds, 422, 'The submitted item order does not belong to this section.');

                $itemPositions = collect($sectionData['items'])->pluck('position')->map(fn ($position): int => (int) $position)->sort()->values()->all();
                abort_unless($itemPositions === range(0, max(0, count($itemIds) - 1)), 422, 'Exam items must use contiguous positions starting from zero.');

                $section->update(['position' => (int) $sectionData['position']]);
                foreach ($sectionData['items'] as $itemData) {
                    $section->items->firstWhere('id', (int) $itemData['id'])?->update(['position' => (int) $itemData['position']]);
                }
            }
        });

        return back()->with('status', 'Exam order updated.');
    }

    public function destroy(Request $request, Exam $exam): RedirectResponse
    {
        $request->validate(['confirm_delete' => ['accepted']]);
        if ($exam->status !== 'draft' || $exam->attempts()->exists()) {
            return back()->with('error', 'Published or attempted exams are preserved. Deactivate them instead.');
        }

        $exam->delete();

        return redirect()->route('manage.exams.index')->with('status', 'Unused draft exam deleted.');
    }

    private function syncComposition(Exam $exam, ExamRequest $request): void
    {
        $exam->sections()->delete();
        $sections = [];
        foreach ($request->sectionsAttributes() as $position => $attributes) {
            $sections[$position] = $exam->sections()->create($attributes);
        }

        foreach ($request->itemsAttributes() as $item) {
            $section = $sections[$item['section_position']] ?? null;
            abort_unless($section instanceof ExamSection, 422, 'Every exam item must belong to a section.');
            $section->items()->create([
                'question_id' => $item['question_id'],
                'position' => $item['position'],
                'points' => $item['points'],
            ]);
        }
    }

    private function formOptions(): array
    {
        return [
            'questions' => Question::query()
                ->with(['topic', 'passage', 'listeningContent'])
                ->whereIn('status', ['draft', 'active'])
                ->whereIn('skill', Exam::SKILLS)
                ->orderBy('skill')->orderBy('id')->get(),
        ];
    }

    private function sectionsText(Exam $exam): string
    {
        return $exam->sections->map(fn (ExamSection $section): string => implode(' | ', [
            $section->skill,
            $section->title,
            $section->time_limit_seconds ?? '',
            $section->navigation_mode,
        ]))->implode(PHP_EOL);
    }

    private function itemsText(Exam $exam): string
    {
        return $exam->sections->flatMap(fn (ExamSection $section) => $section->items->map(fn ($item): string => implode(' | ', [
            $section->position,
            $item->question_id,
            $item->points,
        ])))->implode(PHP_EOL);
    }
}
