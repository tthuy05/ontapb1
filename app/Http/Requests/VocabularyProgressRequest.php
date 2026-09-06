<?php

namespace App\Http\Requests;

use App\Models\VocabularyProgress;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VocabularyProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'state' => ['required', 'string', Rule::in(VocabularyProgress::STATES)],
        ];
    }
}
