<?php

namespace App\Http\Requests;

use App\Models\WritingSubmission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WritingSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['response_text' => trim((string) $this->input('response_text', ''))]);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(WritingSubmission::STATUSES)],
            'response_text' => ['nullable', 'string', 'max:100000'],
            'self_check' => ['nullable', 'array', 'max:5'],
            'self_check.content' => ['nullable', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'self_check.organization' => ['nullable', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'self_check.vocabulary' => ['nullable', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'self_check.grammar' => ['nullable', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'self_check.mechanics' => ['nullable', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'save_version' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->string('status')->toString() === 'submitted' && WritingSubmission::countWords($this->input('response_text')) < 1) {
                $validator->errors()->add('response_text', 'A submitted response must contain at least one word.');
            }
        });
    }

    public function submissionAttributes(): array
    {
        return [
            'response_text' => $this->validated('response_text', ''),
            'self_check' => $this->validated('self_check'),
        ];
    }
}
