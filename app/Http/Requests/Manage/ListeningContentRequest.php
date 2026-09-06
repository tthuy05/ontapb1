<?php

namespace App\Http\Requests\Manage;

use App\Models\ListeningContent;
use App\Models\Topic;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListeningContentRequest extends FormRequest
{
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
                    fn (Builder $query): Builder => $query->whereIn('area', ['listening', 'general']),
                ),
            ],
            'title' => ['required', 'string', 'max:200'],
            'transcript' => ['required', 'string', 'max:200000'],
            'audio_path' => [
                'required', 'string', 'max:500', 'not_regex:/\.\./',
                'regex:/\Aaudio\/listening\/[A-Za-z0-9_\.\/-]+\.(mp3|ogg|wav|m4a)\z/i',
            ],
            'audio_mime' => ['required', 'string', Rule::in(ListeningContent::AUDIO_MIMES)],
            'audio_size_bytes' => ['required', 'integer', 'min:1', 'max:52428800'],
            'duration_seconds' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'speaker_count' => ['nullable', 'integer', 'min:1', 'max:255'],
            'accent_notes' => ['nullable', 'string', 'max:255'],
            'cefr_level' => ['required', 'string', 'size:2', Rule::in(['B1'])],
            'difficulty' => ['required', 'integer', Rule::in(ListeningContent::DIFFICULTIES)],
            'source_type' => ['required', 'string', Rule::in(ListeningContent::SOURCE_TYPES)],
            'source_reference' => ['nullable', 'string', 'max:10000'],
            'license_name' => ['nullable', 'string', 'max:120'],
            'license_url' => ['nullable', 'url:http,https', 'max:2000'],
            'source_notes' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', 'string', Rule::in(ListeningContent::STATUSES)],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->string('status')->toString() !== 'active') {
                return;
            }

            if ($this->integer('topic_id') > 0 && ! Topic::query()
                ->whereKey($this->integer('topic_id'))
                ->whereIn('area', ['listening', 'general'])
                ->where('status', 'active')
                ->exists()) {
                $validator->errors()->add('topic_id', 'Active listening content must use an active Listening or general topic.');
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
}
