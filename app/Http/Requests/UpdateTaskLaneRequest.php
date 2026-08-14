<?php

namespace App\Http\Requests;

use App\Constants\Lanes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskLaneRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['status' => ['required', 'string', Rule::in(Lanes::getAllAsArray())]];
    }
}
