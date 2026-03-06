<?php

namespace App\Services;

use App\Models\Session;
use App\Models\User;
use App\Enums\SessionModeEnum;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SessionService
{
    /**
     * XP multipliers per mode.
     */
    private const XP_MULTIPLIERS = [
        'light' => 1.0,
        'deep'  => 1.2,
        'real'  => 1.5,
    ];

    /**
     * Store a graded session (answer grading is done by QuestionService before calling this).
     * Accepts the already-computed score/accuracy/breakdown from the controller.
     */
    public function store(int $userId, array $data): Session
    {
        return DB::transaction(function () use ($userId, $data) {
            $mode = $data['mode'];

            $session = Session::create([
                'user_id'               => $userId,
                'subject'               => $data['subject'],
                'mode'                  => SessionModeEnum::from($mode),
                'score'                 => $data['score'],
                'accuracy'              => $data['accuracy'],
                'time_used'             => $data['time_used'],
                'total_questions'       => $data['total_questions'],
                'exam_board'            => $data['exam_board'] ?? null,
                'weakest_topic'         => $data['weakest_topic'] ?? null,
                'topic_breakdown'       => $data['topic_breakdown'] ?? null,
                'time_per_question'     => $data['time_per_question'] ?? null,
                'dropped_before_submit' => $data['dropped_before_submit'] ?? false,
                'answers'               => $data['answers'] ?? null,
                'question_ids'          => $data['question_ids'] ?? null,
            ]);

            // Award XP and update streak
            $xpEarned = $this->awardXpAndStreak($userId, $data['accuracy'], $mode);

            // Attach virtual fields for GradedSessionResource
            $user = User::find($userId);
            $session->xp_earned  = $xpEarned;
            $session->streak_days = $user?->streak_days;
            $session->level       = $this->calculateLevel($user?->xp ?? 0);

            return $session;
        });
    }

    /**
     * Award XP and manage streak. Returns XP earned.
     */
    public function awardXpAndStreak(int $userId, float $accuracy, string $mode): int
    {
        $user = User::lockForUpdate()->find($userId);
        if (!$user) return 0;

        $multiplier = self::XP_MULTIPLIERS[$mode] ?? 1.0;
        $xpEarned   = (int) round($accuracy * $multiplier);

        $today        = Carbon::today()->toDateString();
        $lastActivity = $user->last_activity_date;

        if ($lastActivity === null) {
            $newStreak = 1;
        } elseif ($lastActivity->toDateString() === $today) {
            $newStreak = $user->streak_days; // Already practiced today
        } elseif ($lastActivity->toDateString() === Carbon::yesterday()->toDateString()) {
            $newStreak = $user->streak_days + 1; // Consecutive
        } else {
            $newStreak = 1; // Streak broken
        }

        $user->update([
            'xp'                 => $user->xp + $xpEarned,
            'streak_days'        => $newStreak,
            'best_streak'        => max($user->best_streak, $newStreak),
            'last_activity_date' => $today,
        ]);

        return $xpEarned;
    }

    /**
     * Level = floor(sqrt(xp / 100)) + 1
     */
    public function calculateLevel(int $xp): int
    {
        return (int) floor(sqrt($xp / 100)) + 1;
    }
}
