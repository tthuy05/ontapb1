<?php

namespace App\Http\Requests\Manage;

use App\Models\Topic;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $topic = $this->route('topic');

        return [
            'area' => ['required', 'string', Rule::in(Topic::AREAS)],
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:140', 'alpha_dash:ascii', Rule::unique('topics', 'slug')->ignore($topic)],
            'description' => ['nullable', 'string', 'max:5000'],
            'position' => ['required', 'integer', 'min:0', 'max:65535'],
            'priority' => ['required', 'integer', Rule::in([1, 2, 3])],
            'status' => ['required', 'string', Rule::in(Topic::STATUSES)],
        ];
    }
}
