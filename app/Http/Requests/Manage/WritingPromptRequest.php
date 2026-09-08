<?php

namespace App\Http\Requests\Manage;

use App\Models\Topic;
use App\Models\Vocabulary;
use App\Models\WritingPrompt;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WritingPromptRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $checklist = collect(preg_split('/\R/u', (string) $this->input('checklist_text', '')) ?: [])
            ->map(fn (string $item): string => trim($item))
            ->filter()
            ->values()
            ->all();

        $this->merge(['checklist' => $checklist]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'topic_id' => ['nullable', 'integer', Rule::exists('topics', 'id')->where(fn (Builder $query): Builder => $query->whereIn('area', ['writing', 'general']))],
            'task_type' => ['required', 'string', Rule::in(WritingPrompt::TASK_TYPES)],
            'title' => ['required', 'string', 'max:200'],
            'instructions' => ['required', 'string', 'max:100000'],
            'minimum_words' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'recommended_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'guidance' => ['nullable', 'string', 'max:100000'],
            'checklist_text' => ['nullable', 'string', 'max:20000'],
            'checklist' => ['nullable', 'array', 'max:10'],
            'checklist.*' => ['required', 'string', 'max:500'],
            'model_answer' => ['nullable', 'string', 'max:100000'],
            'source_type' => ['required', 'string', Rule::in(Vocabulary::SOURCE_TYPES)],
            'source_reference' => ['nullable', 'string', 'max:10000'],
            'license_name' => ['nullable', 'string', 'max:120'],
            'license_url' => ['nullable', 'url:http,https', 'max:2000'],
            'source_notes' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', 'string', Rule::in(WritingPrompt::STATUSES)],
        ];
    }

    public function contentAttributes(): array
    {
        $attributes = $this->validated();
        unset($attributes['checklist_text']);

        return $attributes;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->string('status')->toString() !== 'active') {
                return;
            }

            if ($this->integer('topic_id') > 0 && ! Topic::query()->whereKey($this->integer('topic_id'))->whereIn('area', ['writing', 'general'])->where('status', 'active')->exists()) {
                $validator->errors()->add('topic_id', 'An active writing prompt must use an active Writing or general topic.');
            }
            if ($this->string('source_type')->toString() === 'original' && trim((string) $this->input('source_notes')) === '' && trim((string) $this->input('source_reference')) === '') {
                $validator->errors()->add('source_notes', 'Active original content requires a provenance note.');
            }
            if ($this->string('source_type')->toString() !== 'original' && trim((string) $this->input('source_reference')) === '') {
                $validator->errors()->add('source_reference', 'Active non-original content requires a source reference.');
            }
        });
    }
}
