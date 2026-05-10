<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'exam_board' => ['sometimes', 'string', 'in:WAEC,JAMB'],
            'department_id' => ['sometimes', 'nullable', 'exists:departments,id'],
        ];
    }
}
