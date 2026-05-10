<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Returned after a session is submitted and graded.
 * This is the ONLY response that includes explanations — after the user
 * has already committed their answers, so it cannot be used to cheat.
 */
class GradedSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'session_id' => $this->id,
            'score' => $this->score,
            'total_questions' => $this->total_questions,
            'accuracy' => $this->accuracy,
            'time_used' => $this->time_used,
            'xp_earned' => $this->xp_earned,            // virtual, set by SessionService
            'level' => $this->level,                // virtual
            'streak_days' => $this->streak_days,          // virtual
            'weakest_topic' => $this->weakest_topic,
            'topic_breakdown' => $this->topic_breakdown,      // per-topic stats

            // Per-question review data (correct answer + explanation revealed post-submission)
            'review' => $this->review,               // virtual array
        ];
    }
}
