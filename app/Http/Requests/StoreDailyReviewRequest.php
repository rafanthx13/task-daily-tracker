<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDailyReviewRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'content' => ['nullable', 'string'],
            'mood' => ['nullable', 'integer', 'between:1,5'],
            'energy' => ['nullable', 'integer', 'between:1,5'],
        ];
    }
}
