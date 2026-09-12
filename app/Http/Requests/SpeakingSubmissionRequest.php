<?php

namespace App\Http\Requests;

use App\Models\SpeakingSubmission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SpeakingSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(SpeakingSubmission::STATUSES)],
            'duration_seconds' => ['nullable', 'integer', 'min:0', 'max:3600'],
            'self_assessment' => ['nullable', 'array', 'max:5'],
            'self_assessment.content' => ['nullable', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'self_assessment.fluency' => ['nullable', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'self_assessment.pronunciation' => ['nullable', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'self_assessment.interaction' => ['nullable', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'self_assessment.language_control' => ['nullable', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'notes' => ['nullable', 'string', 'max:20000'],
            'save_version' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->string('status')->toString() === 'submitted' && (int) $this->input('duration_seconds', 0) < 1) {
                $validator->errors()->add('duration_seconds', 'A submitted speaking practice must include a local recording duration.');
            }
        });
    }

    public function submissionAttributes(): array
    {
        return [
            'duration_seconds' => $this->validated('duration_seconds'),
            'self_assessment' => $this->validated('self_assessment'),
            'notes' => $this->validated('notes'),
        ];
    }
}
