<?php

namespace App\Http\Requests\Manage;

use App\Models\Exam;
use App\Models\ExamSection;
use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExamRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $sections = [];
        if (is_string($this->input('sections_text'))) {
            foreach (preg_split('/\R/u', $this->input('sections_text')) ?: [] as $line) {
                if (trim($line) === '') {
                    continue;
                }
                [$skill, $title, $timeLimit, $navigation] = array_pad(explode('|', $line, 4), 4, '');
                $sections[] = [
                    'skill' => trim((string) $skill),
                    'title' => trim((string) $title),
                    'time_limit_seconds' => trim((string) $timeLimit) === '' ? null : trim((string) $timeLimit),
                    'navigation_mode' => trim((string) $navigation) === '' ? ExamSection::NAVIGATION_MODES[0] : trim((string) $navigation),
                ];
            }
        }

        $items = [];
        $sectionPositions = [];
        if (is_string($this->input('items_text'))) {
            foreach (preg_split('/\R/u', $this->input('items_text')) ?: [] as $line) {
                if (trim($line) === '') {
                    continue;
                }
                [$sectionPosition, $questionId, $points] = array_pad(explode('|', $line, 3), 3, '');
                $sectionPosition = (int) trim((string) $sectionPosition);
                $items[] = [
                    'section_position' => $sectionPosition,
                    'question_id' => trim((string) $questionId),
                    'points' => trim((string) $points) === '' ? '1.00' : trim((string) $points),
                    'position' => $sectionPositions[$sectionPosition] ?? 0,
                ];
                $sectionPositions[$sectionPosition] = ($sectionPositions[$sectionPosition] ?? 0) + 1;
            }
        }

        $this->merge([
            'sections' => is_string($this->input('sections_text')) ? $sections : $this->input('sections', $sections),
            'items' => is_string($this->input('items_text')) ? $items : $this->input('items', $items),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'format_label' => ['required', 'string', Rule::in(Exam::FORMAT_LABELS)],
            'description' => ['nullable', 'string', 'max:10000'],
            'instructions' => ['nullable', 'string', 'max:10000'],
            'time_limit_seconds' => ['nullable', 'integer', 'min:1', 'max:86400'],
            'metadata' => ['nullable', 'array'],
            'status' => ['required', 'string', Rule::in(Exam::STATUSES)],
            'sections_text' => ['nullable', 'string', 'max:20000'],
            'sections' => ['required', 'array', 'min:1', 'max:20'],
            'sections.*.skill' => ['required', 'string', Rule::in(Exam::SKILLS)],
            'sections.*.title' => ['required', 'string', 'max:160'],
            'sections.*.instructions' => ['nullable', 'string', 'max:5000'],
            'sections.*.time_limit_seconds' => ['nullable', 'integer', 'min:1', 'max:86400'],
            'sections.*.navigation_mode' => ['required', 'string', Rule::in(ExamSection::NAVIGATION_MODES)],
            'items_text' => ['nullable', 'string', 'max:30000'],
            'items' => ['nullable', 'array', 'max:200'],
            'items.*.section_position' => ['required', 'integer', 'min:0', 'max:19'],
            'items.*.question_id' => ['required', 'integer', 'distinct', 'exists:questions,id'],
            'items.*.points' => ['required', 'numeric', 'min:0.01', 'max:9999.99'],
            'items.*.position' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $sections = collect($this->input('sections', []));
            $items = collect($this->input('items', []));
            $sectionCount = $sections->count();
            $isActive = $this->string('status')->toString() === 'active';

            $positions = range(0, max(0, $sectionCount - 1));
            if ($sections->keys()->map(fn ($position): int => (int) $position)->all() !== $positions) {
                $validator->errors()->add('sections', 'Exam sections must use contiguous order.');
            }

            $questionIds = $items->pluck('question_id')->filter()->map(fn ($id): int => (int) $id)->values();
            if ($questionIds->count() !== $questionIds->unique()->count()) {
                $validator->errors()->add('items', 'An objective question may appear only once in an exam.');
            }

            $questions = Question::query()
                ->with(['topic', 'passage', 'listeningContent', 'options'])
                ->whereIn('id', $questionIds)
                ->get()
                ->keyBy('id');
            $sectionSkills = $sections->mapWithKeys(fn (array $section, int $position): array => [$position => $section['skill'] ?? null]);
            $counts = [];

            foreach ($items as $item) {
                $sectionPosition = (int) ($item['section_position'] ?? -1);
                $counts[$sectionPosition] = ($counts[$sectionPosition] ?? 0) + 1;
                if (! $sectionSkills->has($sectionPosition)) {
                    $validator->errors()->add('items', 'Every exam item must belong to an existing section.');
                    continue;
                }
                $question = $questions->get((int) ($item['question_id'] ?? 0));
                if (! $question) {
                    continue;
                }
                if ($question->skill !== $sectionSkills->get($sectionPosition)) {
                    $validator->errors()->add('items', "Question {$question->id} does not match its exam section skill.");
                }
                if (! $isActive) {
                    continue;
                }
                if ($question->status !== 'active') {
                    $validator->errors()->add('items', "Question {$question->id} must be active before the exam can be activated.");
                }
                if ($question->topic && $question->topic->status !== 'active') {
                    $validator->errors()->add('items', "Question {$question->id} needs an active topic before activation.");
                }
                if ($question->passage_id !== null && (! $question->passage || $question->passage->status !== 'active')) {
                    $validator->errors()->add('items', "Question {$question->id} needs an active Reading context before activation.");
                }
                if ($question->listening_content_id !== null && (! $question->listeningContent || $question->listeningContent->status !== 'active')) {
                    $validator->errors()->add('items', "Question {$question->id} needs active Listening content before activation.");
                }
                $correct = $question->options->where('is_correct', true)->count();
                $validCount = $question->type === 'true_false'
                    ? $question->options->count() === 2
                    : $question->options->count() >= 2;
                if (! in_array($question->type, Question::TYPES, true) || ! $validCount || $correct !== 1) {
                    $validator->errors()->add('items', "Question {$question->id} has an invalid objective answer structure.");
                }
            }

            if ($isActive) {
                if ($items->isEmpty()) {
                    $validator->errors()->add('items', 'An active exam needs at least one objective question.');
                }
                foreach (range(0, max(0, $sectionCount - 1)) as $position) {
                    if (($counts[$position] ?? 0) === 0) {
                        $validator->errors()->add('sections', "Exam section {$position} needs at least one item.");
                    }
                }
                $sectionTime = $sections->sum(fn (array $section): int => (int) ($section['time_limit_seconds'] ?? 0));
                if ($this->filled('time_limit_seconds') && $sectionTime > (int) $this->input('time_limit_seconds')) {
                    $validator->errors()->add('time_limit_seconds', 'The overall exam time must cover its configured section limits.');
                }
            }
        });
    }

    public function contentAttributes(): array
    {
        $attributes = $this->validated();
        unset($attributes['sections'], $attributes['items'], $attributes['sections_text'], $attributes['items_text']);

        return $attributes;
    }

    public function sectionsAttributes(): array
    {
        return collect($this->validated('sections', []))->values()->map(
            fn (array $section, int $position): array => [
                'skill' => $section['skill'],
                'title' => $section['title'],
                'instructions' => $section['instructions'] ?? null,
                'position' => $position,
                'time_limit_seconds' => $section['time_limit_seconds'] ?? null,
                'navigation_mode' => $section['navigation_mode'],
            ],
        )->all();
    }

    public function itemsAttributes(): array
    {
        return collect($this->validated('items', []))->values()->map(
            fn (array $item): array => [
                'section_position' => (int) $item['section_position'],
                'question_id' => (int) $item['question_id'],
                'position' => (int) $item['position'],
                'points' => (float) $item['points'],
            ],
        )->all();
    }
}
