<?php

namespace App\Http\Requests\Manage;

use App\Models\SpeakingPrompt;
use App\Models\Topic;
use App\Models\Vocabulary;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SpeakingPromptRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $lines = static fn (string $value): array => collect(preg_split('/\R/u', $value) ?: [])
            ->map(fn (string $item): string => trim($item))
            ->filter()
            ->values()
            ->all();

        $this->merge([
            'suggested_ideas' => $lines((string) $this->input('suggested_ideas_text', '')),
            'follow_up_questions' => $lines((string) $this->input('follow_up_questions_text', '')),
            'checklist' => $lines((string) $this->input('checklist_text', '')),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'topic_id' => ['nullable', 'integer', Rule::exists('topics', 'id')->where(fn (Builder $query): Builder => $query->whereIn('area', ['speaking', 'general']))],
            'part_type' => ['required', 'string', Rule::in(SpeakingPrompt::PART_TYPES)],
            'title' => ['required', 'string', 'max:200'],
            'instructions' => ['required', 'string', 'max:100000'],
            'preparation_seconds' => ['nullable', 'integer', 'min:0', 'max:3600'],
            'speaking_seconds' => ['nullable', 'integer', 'min:1', 'max:3600'],
            'suggested_ideas_text' => ['nullable', 'string', 'max:20000'],
            'suggested_ideas' => ['nullable', 'array', 'max:10'],
            'suggested_ideas.*' => ['required', 'string', 'max:500'],
            'follow_up_questions_text' => ['nullable', 'string', 'max:20000'],
            'follow_up_questions' => ['nullable', 'array', 'max:10'],
            'follow_up_questions.*' => ['required', 'string', 'max:500'],
            'checklist_text' => ['nullable', 'string', 'max:20000'],
            'checklist' => ['nullable', 'array', 'max:10'],
            'checklist.*' => ['required', 'string', 'max:500'],
            'source_type' => ['required', 'string', Rule::in(Vocabulary::SOURCE_TYPES)],
            'source_reference' => ['nullable', 'string', 'max:10000'],
            'license_name' => ['nullable', 'string', 'max:120'],
            'license_url' => ['nullable', 'url:http,https', 'max:2000'],
            'source_notes' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', 'string', Rule::in(SpeakingPrompt::STATUSES)],
        ];
    }

    public function contentAttributes(): array
    {
        $attributes = $this->validated();
        unset($attributes['suggested_ideas_text'], $attributes['follow_up_questions_text'], $attributes['checklist_text']);

        return $attributes;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->string('status')->toString() !== 'active') {
                return;
            }

            $topicId = $this->integer('topic_id');
            if ($topicId > 0 && ! Topic::query()->whereKey($topicId)->whereIn('area', ['speaking', 'general'])->where('status', 'active')->exists()) {
                $validator->errors()->add('topic_id', 'An active speaking prompt must use an active Speaking or general topic.');
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
