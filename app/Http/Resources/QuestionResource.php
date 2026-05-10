<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public question resource — NEVER exposes correct_answer or explanation.
 * Explanation is sent only inside GradedSessionResource after grading.
 */
class QuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'topic' => $this->topic,
            'year' => $this->year,
            'question_text' => $this->question_text,
            'options' => $this->options,          // array of 4 strings
            'difficulty' => $this->difficulty,
            // ⚠️  correct_answer and explanation intentionally omitted
        ];
    }
}
