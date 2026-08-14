<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncTimeEntriesRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'entries' => ['nullable', 'array'],
            'entries.*.task_name' => ['nullable', 'string', 'max:255'],
            'entries.*.start_time' => ['nullable', 'date_format:H:i'],
            'entries.*.end_time' => ['nullable', 'date_format:H:i'],
            'entries.*.tag_id' => ['nullable', 'integer', 'exists:time_management_tags,id'],
        ];
    }
}
