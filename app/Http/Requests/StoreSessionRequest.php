<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\SessionModeEnum;

class StoreSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Session metadata
            'subject'               => ['required', 'string', 'max:255'],
            'mode'                  => ['required', 'string', 'in:' . implode(',', SessionModeEnum::toArray())],
            'time_used'             => ['required', 'integer', 'min:0'],
            'exam_board'            => ['nullable', 'string', 'in:WAEC,JAMB'],
            'time_per_question'     => ['nullable', 'array'],
            'time_per_question.*'   => ['nullable', 'integer', 'min:0'],
            'dropped_before_submit' => ['nullable', 'boolean'],

            // Question grading inputs (mutually exclusive paths)
            //
            //  Path A — server grading (preferred, secure):
            //    Send question_ids + answers → backend grades
            'question_ids'          => ['nullable', 'array'],
            'question_ids.*'        => ['nullable', 'integer', 'exists:questions,id'],
            'answers'               => ['nullable', 'array'],
            'answers.*'             => ['nullable', 'integer', 'min:0', 'max:3'],
        ];
    }

    public function messages(): array
    {
        return [
            'question_ids.*.exists' => 'One or more question IDs are invalid.',
        ];
    }
}
