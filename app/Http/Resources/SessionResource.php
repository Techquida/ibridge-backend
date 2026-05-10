<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'mode' => $this->mode?->value ?? $this->mode,
            'score' => $this->score,
            'accuracy' => $this->accuracy,
            'time_used' => $this->time_used,
            'total_questions' => $this->total_questions,
            'exam_board' => $this->exam_board,
            'weakest_topic' => $this->weakest_topic,
            'topic_breakdown' => $this->topic_breakdown,
            'dropped_before_submit' => (bool) $this->dropped_before_submit,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
