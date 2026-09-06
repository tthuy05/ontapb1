<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\Exam;
use App\Services\ExamCompositionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function __construct(private readonly ExamCompositionService $composition) {}

    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $format = $request->string('format')->toString();

        $exams = Exam::query()
            ->withCount('sections')
            ->active()
            ->whereHas('sections.items')
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->when(in_array($format, Exam::FORMAT_LABELS, true), fn (Builder $query) => $query->where('format_label', $format))
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        return view('exams.index', compact('exams'));
    }

    public function show(Exam $exam): View
    {
        $this->composition->load($exam);
        abort_unless($exam->status === 'active' && $this->composition->activationErrors($exam) === [], 404);

        $resumableAttempt = Attempt::query()
            ->where('exam_id', $exam->id)
            ->inProgress()
            ->latest('started_at')
            ->first();

        return view('exams.show', compact('exam', 'resumableAttempt'));
    }
}
