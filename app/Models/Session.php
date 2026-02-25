<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\SessionModeEnum;

class Session extends Model
{
    protected $table = 'exam_sessions';

    protected $fillable = [
        'user_id',
        'subject',
        'mode',
        'score',
        'accuracy',
        'time_used',
        'total_questions',
        'exam_board',
        'topic_breakdown',
        'weakest_topic',
        'time_per_question',
        'dropped_before_submit',
    ];

    protected $casts = [
        'mode'                  => SessionModeEnum::class,
        'topic_breakdown'       => 'array',
        'time_per_question'     => 'array',
        'dropped_before_submit' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
