<?php

namespace App\Http\Requests\Manage;

use App\Models\Passage;
use App\Models\Topic;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PassageRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $body = is_string($this->input('body')) ? trim($this->input('body')) : '';
        preg_match_all('/\S+/u', $body, $matches);

        $this->merge(['word_count' => count($matches[0] ?? [])]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $passage = $this->route('passage');

        return [
            'topic_id' => [
                'nullable', 'integer',
                Rule::exists('topics', 'id')->where(
                    fn (Builder $query): Builder => $query->whereIn('area', ['reading', 'general']),
                ),
            ],
            'title' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:200000'],
            'word_count' => ['required', 'integer', 'min:1', 'max:65535'],
            'cefr_level' => ['required', 'string', 'size:2', Rule::in(['B1'])],
            'difficulty' => ['required', 'integer', Rule::in(Passage::DIFFICULTIES)],
            'source_type' => ['required', 'string', Rule::in(Passage::SOURCE_TYPES)],
            'source_reference' => ['nullable', 'string', 'max:10000'],
            'license_name' => ['nullable', 'string', 'max:120'],
            'license_url' => ['nullable', 'url:http,https', 'max:2000'],
            'source_notes' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', 'string', Rule::in(Passage::STATUSES)],
        ];
    }

    public function contentAttributes(): array
    {
        return $this->validated();
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->string('status')->toString() !== 'active') {
                return;
            }

            if ($this->integer('topic_id') > 0 && ! $this->topicIsActive()) {
                $validator->errors()->add('topic_id', 'An active passage must use an active Reading or general topic.');
            }

            if ($this->string('source_type')->toString() !== 'original'
                && trim((string) $this->input('source_reference')) === '') {
                $validator->errors()->add('source_reference', 'Active non-original content requires a source reference.');
            }

            if ($this->string('source_type')->toString() === 'original'
                && trim((string) $this->input('source_notes')) === ''
                && trim((string) $this->input('source_reference')) === '') {
                $validator->errors()->add('source_notes', 'Active original content requires a provenance note.');
            }
        });
    }

    private function topicIsActive(): bool
    {
        return Topic::query()
            ->whereKey($this->integer('topic_id'))
            ->whereIn('area', ['reading', 'general'])
            ->where('status', 'active')
            ->exists();
    }
}
