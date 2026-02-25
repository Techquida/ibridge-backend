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
            'subject'               => ['required', 'string', 'max:255'],
            'mode'                  => ['required', 'string', 'in:' . implode(',', SessionModeEnum::toArray())],
            'score'                 => ['required', 'integer', 'min:0'],
            'accuracy'              => ['required', 'numeric', 'min:0', 'max:100'],
            'time_used'             => ['required', 'integer', 'min:0'],
            'total_questions'       => ['required', 'integer', 'min:1'],
            'exam_board'            => ['nullable', 'string', 'in:WAEC,JAMB'],
            'weakest_topic'         => ['nullable', 'string', 'max:255'],
            'topic_breakdown'       => ['nullable', 'array'],
            'time_per_question'     => ['nullable', 'array'],
            'time_per_question.*'   => ['nullable', 'integer', 'min:0'],
            'dropped_before_submit' => ['nullable', 'boolean'],
        ];
    }
}
