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

    public function store(int $userId, array $data): Session
    {
        return DB::transaction(function () use ($userId, $data) {
            $mode = $data['mode'];

            $session = Session::create([
                'user_id'              => $userId,
                'subject'              => $data['subject'],
                'mode'                 => SessionModeEnum::from($mode),
                'score'                => $data['score'],
                'accuracy'             => $data['accuracy'],
                'time_used'            => $data['time_used'],
                'total_questions'      => $data['total_questions'] ?? 10,
                'exam_board'           => $data['exam_board'] ?? null,
                'weakest_topic'        => $data['weakest_topic'] ?? null,
                'topic_breakdown'      => $data['topic_breakdown'] ?? null,
                'time_per_question'    => $data['time_per_question'] ?? null,
                'dropped_before_submit'=> $data['dropped_before_submit'] ?? false,
            ]);

            // Award XP and update streak
            $this->awardXpAndStreak($userId, $data['accuracy'], $mode);

            return $session;
        });
    }

    private function awardXpAndStreak(int $userId, float $accuracy, string $mode): void
    {
        $user = User::lockForUpdate()->find($userId);
        if (!$user) return;

        // XP = accuracy * mode_multiplier (rounded)
        $multiplier = self::XP_MULTIPLIERS[$mode] ?? 1.0;
        $xpEarned   = (int) round($accuracy * $multiplier);

        // Streak logic
        $today         = Carbon::today()->toDateString();
        $lastActivity  = $user->last_activity_date;
        $currentStreak = $user->streak_days;

        if ($lastActivity === null) {
            $newStreak = 1;
        } elseif ($lastActivity->toDateString() === $today) {
            // Already played today — no change
            $newStreak = $currentStreak;
        } elseif ($lastActivity->toDateString() === Carbon::yesterday()->toDateString()) {
            // Consecutive day
            $newStreak = $currentStreak + 1;
        } else {
            // Streak broken
            $newStreak = 1;
        }

        $user->update([
            'xp'                 => $user->xp + $xpEarned,
            'streak_days'        => $newStreak,
            'best_streak'        => max($user->best_streak, $newStreak),
            'last_activity_date' => $today,
        ]);
    }
}
