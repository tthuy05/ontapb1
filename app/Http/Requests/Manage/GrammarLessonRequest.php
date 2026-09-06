<?php

namespace App\Http\Requests\Manage;

use App\Models\GrammarLesson;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GrammarLessonRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! is_string($this->input('examples_text'))) {
            return;
        }

        $lines = preg_split('/\R/u', $this->input('examples_text')) ?: [];
        $examples = [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            [$example, $explanation] = array_pad(explode('|', $line, 2), 2, null);
            $examples[] = [
                'example' => trim($example),
                'explanation' => is_string($explanation) ? trim($explanation) : null,
            ];
        }

        $this->merge(['examples' => $examples === [] ? null : $examples]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $grammarLesson = $this->route('grammarLesson');

        return [
            'topic_id' => [
                'required',
                'integer',
                Rule::exists('topics', 'id')->where(
                    fn (Builder $query): Builder => $query->whereIn('area', ['grammar', 'general']),
                ),
            ],
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'string', 'max:180', 'alpha_dash:ascii', Rule::unique('grammar_lessons', 'slug')->ignore($grammarLesson)],
            'objectives' => ['required', 'string', 'max:10000'],
            'prerequisites' => ['nullable', 'string', 'max:10000'],
            'body' => ['required', 'string', 'max:100000'],
            'examples_text' => ['nullable', 'string', 'max:20000'],
            'examples' => ['nullable', 'array', 'max:20'],
            'examples.*.example' => ['required', 'string', 'max:2000'],
            'examples.*.explanation' => ['required', 'string', 'max:2000'],
            'common_mistakes' => ['nullable', 'string', 'max:20000'],
            'position' => ['required', 'integer', 'min:0', 'max:65535'],
            'source_type' => ['required', 'string', Rule::in(GrammarLesson::SOURCE_TYPES)],
            'source_reference' => ['nullable', 'string', 'max:10000'],
            'license_name' => ['nullable', 'string', 'max:120'],
            'license_url' => ['nullable', 'url:http,https', 'max:2000'],
            'source_notes' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', 'string', Rule::in(GrammarLesson::STATUSES)],
        ];
    }

    public function contentAttributes(): array
    {
        $attributes = $this->validated();
        unset($attributes['examples_text']);

        return $attributes;
    }
}
