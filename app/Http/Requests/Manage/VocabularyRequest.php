<?php

namespace App\Http\Requests\Manage;

use App\Models\Vocabulary;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VocabularyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $vocabulary = $this->route('vocabulary');
        $partOfSpeech = $this->string('part_of_speech')->trim()->toString();

        $duplicateRule = Rule::unique('vocabularies', 'term')
            ->where(function (Builder $query) use ($partOfSpeech): Builder {
                $query->where('topic_id', $this->integer('topic_id'));

                return $partOfSpeech === ''
                    ? $query->whereNull('part_of_speech')
                    : $query->where('part_of_speech', $partOfSpeech);
            })
            ->ignore($vocabulary);

        return [
            'topic_id' => [
                'required',
                'integer',
                Rule::exists('topics', 'id')->where(
                    fn (Builder $query): Builder => $query->whereIn('area', ['vocabulary', 'general']),
                ),
            ],
            'term' => ['required', 'string', 'max:160', $duplicateRule],
            'part_of_speech' => ['nullable', 'string', 'max:40'],
            'phonetic' => ['nullable', 'string', 'max:120'],
            'definition' => ['required', 'string', 'max:10000'],
            'translation' => ['nullable', 'string', 'max:10000'],
            'example_sentence' => ['nullable', 'string', 'max:10000'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'pronunciation_audio_path' => [
                'nullable',
                'string',
                'max:500',
                'not_regex:/\.\./',
                'regex:/\Aaudio\/pronunciation\/[A-Za-z0-9_\.\/-]+\.(mp3|ogg|wav|m4a)\z/i',
            ],
            'source_type' => ['required', 'string', Rule::in(Vocabulary::SOURCE_TYPES)],
            'source_reference' => ['nullable', 'string', 'max:10000'],
            'license_name' => ['nullable', 'string', 'max:120'],
            'license_url' => ['nullable', 'url:http,https', 'max:2000'],
            'source_notes' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', 'string', Rule::in(Vocabulary::STATUSES)],
        ];
    }
}
