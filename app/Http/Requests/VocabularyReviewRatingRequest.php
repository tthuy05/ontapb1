<?php

namespace App\Http\Requests;

use App\Models\VocabularyReviewSchedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VocabularyReviewRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'string', Rule::in(VocabularyReviewSchedule::RATINGS)],
        ];
    }
}
