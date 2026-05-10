<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'exam_board',
        'subject',
        'topic',
        'year',
        'question_text',
        'options',
        'correct_answer',
        'explanation',
        'difficulty',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'correct_answer' => 'integer',
        'is_active' => 'boolean',
    ];

    // ── Query Scopes ──────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForBoard(Builder $query, string $board): Builder
    {
        return $query->where('exam_board', strtoupper($board));
    }

    public function scopeForSubject(Builder $query, string $subject): Builder
    {
        return $query->where('subject', $subject);
    }

    public function scopeForDifficulty(Builder $query, string $difficulty): Builder
    {
        return $query->where('difficulty', $difficulty);
    }

    public function scopeForTopic(Builder $query, string $topic): Builder
    {
        return $query->where('topic', $topic);
    }
}
