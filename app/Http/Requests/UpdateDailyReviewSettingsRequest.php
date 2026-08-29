<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDailyReviewSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'show_extra_lane' => ['required', 'boolean'],
            'show_reminders' => ['required', 'boolean'],
            'show_mood_energy' => ['required', 'boolean'],
            'return_to' => ['nullable', 'string'],
        ];
    }
}
