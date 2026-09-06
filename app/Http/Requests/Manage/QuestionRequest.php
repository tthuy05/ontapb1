<?php

namespace App\Http\Requests\Manage;

use App\Models\Question;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuestionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! is_string($this->input('options_text'))) {
            return;
        }

        $options = [];
        foreach (preg_split('/\R/u', $this->input('options_text')) ?: [] as $position => $line) {
            if (trim($line) === '') {
                continue;
            }

            [$key, $content, $correct] = array_pad(explode('|', $line, 3), 3, null);
            $options[] = [
                'option_key' => trim((string) $key),
                'content' => trim((string) $content),
                'is_correct' => in_array(strtolower(trim((string) $correct)), ['1', 'true', 'yes', 'correct', 'x'], true),
                'position' => $position,
            ];
        }

        $this->merge(['options' => $options]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'topic_id' => [
                'nullable', 'integer',
                Rule::exists('topics', 'id')->where(
                    fn (Builder $query): Builder => $query->whereIn('area', ['reading', 'listening', 'general']),
                ),
            ],
            'passage_id' => ['nullable', 'integer', 'exists:passages,id'],
            'listening_content_id' => ['nullable', 'integer', 'exists:listening_contents,id'],
            'skill' => ['required', 'string', Rule::in(Question::SKILLS)],
            'type' => ['required', 'string', Rule::in(Question::TYPES)],
            'prompt' => ['required', 'string', 'max:100000'],
            'explanation' => ['nullable', 'string', 'max:100000'],
            'cefr_level' => ['required', 'string', 'size:2', Rule::in(['B1'])],
            'difficulty' => ['required', 'integer', Rule::in(Question::DIFFICULTIES)],
            'source_type' => ['required', 'string', Rule::in(Question::SOURCE_TYPES)],
            'source_reference' => ['nullable', 'string', 'max:10000'],
            'license_name' => ['nullable', 'string', 'max:120'],
            'license_url' => ['nullable', 'url:http,https', 'max:2000'],
            'source_notes' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', 'string', Rule::in(Question::STATUSES)],
            'options_text' => ['nullable', 'string', 'max:30000'],
            'options' => ['nullable', 'array', 'max:20'],
            'options.*.option_key' => ['required', 'string', 'max:20', 'alpha_dash:ascii'],
            'options.*.content' => ['required', 'string', 'max:10000'],
            'options.*.is_correct' => ['required', 'boolean'],
            'options.*.position' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->filled('passage_id') && $this->filled('listening_content_id')) {
                $validator->errors()->add('passage_id', 'A question can link to a passage or listening item, not both.');
            }

            $skill = $this->string('skill')->toString();
            if ($skill === 'reading' && $this->filled('listening_content_id')) {
                $validator->errors()->add('listening_content_id', 'Reading questions must use a Reading passage.');
            }
            if ($skill === 'listening' && $this->filled('passage_id')) {
                $validator->errors()->add('passage_id', 'Listening questions must use Listening content.');
            }

            if ($this->string('status')->toString() !== 'active') {
                return;
            }

            if (trim((string) $this->input('explanation')) === '') {
                $validator->errors()->add('explanation', 'Active questions require an explanation.');
            }
            if ($this->string('source_type')->toString() !== 'original'
                && trim((string) $this->input('source_reference')) === '') {
                $validator->errors()->add('source_reference', 'Active non-original questions require a source reference.');
            }
            if ($this->string('source_type')->toString() === 'original'
                && trim((string) $this->input('source_notes')) === ''
                && trim((string) $this->input('source_reference')) === '') {
                $validator->errors()->add('source_notes', 'Active original questions require a provenance note.');
            }

            $options = $this->input('options', []);
            $correct = is_array($options) ? count(array_filter($options, fn ($option): bool => filter_var($option['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN))) : 0;
            $count = is_array($options) ? count($options) : 0;
            if ($this->string('type')->toString() === 'single_choice' && ($count < 2 || $correct !== 1)) {
                $validator->errors()->add('options', 'Active single-choice questions need at least two options and exactly one correct option.');
            }
            if ($this->string('type')->toString() === 'true_false' && ($count !== 2 || $correct !== 1)) {
                $validator->errors()->add('options', 'Active true/false questions need exactly two options and exactly one correct option.');
            }
        });
    }

    public function contentAttributes(): array
    {
        $attributes = $this->validated();
        unset($attributes['options'], $attributes['options_text']);

        return $attributes;
    }

    public function optionsAttributes(): array
    {
        return array_values($this->validated('options', []));
    }
}
