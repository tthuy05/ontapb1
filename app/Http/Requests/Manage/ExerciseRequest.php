<?php

namespace App\Http\Requests\Manage;

use App\Models\Exercise;
use App\Models\Question;
use App\Models\Topic;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExerciseRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! is_string($this->input('items_text'))) {
            return;
        }

        $items = [];
        foreach (preg_split('/\R/u', $this->input('items_text')) ?: [] as $position => $line) {
            if (trim($line) === '') {
                continue;
            }

            [$questionId, $points] = array_pad(explode('|', $line, 2), 2, '1.00');
            $items[] = [
                'question_id' => trim((string) $questionId),
                'points' => trim((string) $points) === '' ? '1.00' : trim((string) $points),
                'position' => $position,
            ];
        }

        $this->merge(['items' => $items]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'topic_id' => ['nullable', 'integer', 'exists:topics,id'],
            'title' => ['required', 'string', 'max:200'],
            'skill' => ['required', 'string', Rule::in(Exercise::SKILLS)],
            'instructions' => ['nullable', 'string', 'max:10000'],
            'difficulty' => ['required', 'integer', Rule::in(Exercise::DIFFICULTIES)],
            'time_limit_seconds' => ['nullable', 'integer', 'min:1', 'max:86400'],
            'metadata' => ['nullable', 'array'],
            'status' => ['required', 'string', Rule::in(Exercise::STATUSES)],
            'items_text' => ['nullable', 'string', 'max:20000'],
            'items' => ['nullable', 'array', 'max:100'],
            'items.*.question_id' => ['required', 'integer', 'distinct', 'exists:questions,id'],
            'items.*.points' => ['required', 'numeric', 'min:0.01', 'max:9999.99'],
            'items.*.position' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $items = $this->input('items', []);
            if (! is_array($items)) {
                return;
            }

            $ids = collect($items)->pluck('question_id')->filter()->map(fn ($id): int => (int) $id)->values();
            $questions = Question::query()
                ->with(['topic', 'passage', 'listeningContent', 'options'])
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');
            $skill = $this->string('skill')->toString();
            $isActive = $this->string('status')->toString() === 'active';

            if ($isActive && $ids->isEmpty()) {
                $validator->errors()->add('items', 'An active exercise needs at least one question.');
            }

            if ($this->filled('topic_id')) {
                $topic = Topic::query()->find($this->integer('topic_id'));
                if ($topic && ! in_array($topic->area, [$skill, 'general'], true)) {
                    $validator->errors()->add('topic_id', 'The exercise topic must match its skill or use the General area.');
                }
                if ($isActive && $topic && $topic->status !== 'active') {
                    $validator->errors()->add('topic_id', 'An active exercise needs an active topic.');
                }
            }

            foreach ($ids as $id) {
                $question = $questions->get($id);
                if (! $question) {
                    continue;
                }

                if ($question->skill !== $skill) {
                    $validator->errors()->add('items', "Question {$id} does not match the exercise skill.");
                }

                if (! $isActive) {
                    continue;
                }

                if ($question->status !== 'active') {
                    $validator->errors()->add('items', "Question {$id} must be active before the exercise can be activated.");
                }
                if ($question->topic_id !== null && (! $question->topic || $question->topic->status !== 'active')) {
                    $validator->errors()->add('items', "Question {$id} needs an active topic before the exercise can be activated.");
                }
                if ($question->passage_id !== null && (! $question->passage || $question->passage->status !== 'active')) {
                    $validator->errors()->add('items', "Question {$id} needs an active Reading context before the exercise can be activated.");
                }
                if ($question->listening_content_id !== null && (! $question->listeningContent || $question->listeningContent->status !== 'active')) {
                    $validator->errors()->add('items', "Question {$id} needs active Listening content before the exercise can be activated.");
                }

                $correct = $question->options->where('is_correct', true)->count();
                $validOptions = $question->type === 'true_false'
                    ? $question->options->count() === 2
                    : $question->options->count() >= 2;
                if (! $validOptions || $correct !== 1) {
                    $validator->errors()->add('items', "Question {$id} has an invalid answer structure.");
                }
            }
        });
    }

    public function contentAttributes(): array
    {
        $attributes = $this->validated();
        unset($attributes['items'], $attributes['items_text']);

        return $attributes;
    }

    public function itemsAttributes(): array
    {
        return collect($this->validated('items', []))->values()->map(
            fn (array $item, int $position): array => [
                'question_id' => (int) $item['question_id'],
                'points' => (float) $item['points'],
                'position' => $position,
            ],
        )->all();
    }
}
