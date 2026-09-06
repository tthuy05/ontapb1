<?php

namespace App\Http\Requests\Manage;

use Illuminate\Foundation\Http\FormRequest;

class ExamOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sections' => ['required', 'array', 'min:1', 'max:20'],
            'sections.*.id' => ['required', 'integer', 'distinct', 'exists:exam_sections,id'],
            'sections.*.position' => ['required', 'integer', 'min:0', 'max:65535'],
            'sections.*.items' => ['required', 'array', 'min:1', 'max:200'],
            'sections.*.items.*.id' => ['required', 'integer', 'distinct', 'exists:exam_section_items,id'],
            'sections.*.items.*.position' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
