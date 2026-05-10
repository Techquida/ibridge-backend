<?php

namespace App\Services;

use App\Models\Session;
use App\Models\User;

class AnalyticsService
{
    private const XP_PER_LEVEL = 500;

    public function getStudentAnalytics(User $user): array
    {
        $sessions = Session::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalSessions = $sessions->count();
        $avgAccuracy = $totalSessions > 0 ? round($sessions->avg('accuracy'), 1) : 0;
        $bestScore = $totalSessions > 0 ? $sessions->max('accuracy') : 0;

        // Per-subject breakdown
        $perSubject = $sessions->groupBy('subject')->map(function ($subjectSessions, $subject) {
            $scores = $subjectSessions->pluck('accuracy')->values()->toArray();
            $trend = array_slice($scores, 0, 5); // last 5 for chart
            $avgAcc = count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : 0;
            $lastPlayed = $subjectSessions->first()?->created_at?->toISOString();

            return [
                'subject' => $subject,
                'session_count' => count($scores),
                'avg_accuracy' => $avgAcc,
                'last_played' => $lastPlayed,
                'trend' => $trend,
                'weakest_topic' => $subjectSessions->whereNotNull('weakest_topic')
                    ->groupBy('weakest_topic')
                    ->map->count()
                    ->sortDesc()
                    ->keys()
                    ->first(),
            ];
        })->values();

        // Mode breakdown
        $modeBreakdown = $sessions->groupBy(fn ($s) => $s->mode->value ?? $s->mode)->map->count();

        // Weakest topic overall (most frequent weakest_topic across sessions)
        $weakestTopicOverall = $sessions->whereNotNull('weakest_topic')
            ->groupBy('weakest_topic')
            ->map->count()
            ->sortDesc()
            ->keys()
            ->first();

        // Weakest subject (lowest avg_accuracy — only subjects with 2+ sessions to be meaningful)
        $weakestSubjectEntry = $perSubject
            ->filter(fn ($s) => $s['session_count'] >= 2)
            ->sortBy('avg_accuracy')
            ->first();
        $weakestSubject = $weakestSubjectEntry['subject'] ?? $perSubject->sortBy('avg_accuracy')->first()['subject'] ?? null;

        // Recent sessions (last 10)
        $recentSessions = $sessions->take(10)->map(fn ($s) => [
            'id' => $s->id,
            'subject' => $s->subject,
            'mode' => $s->mode->value ?? $s->mode,
            'accuracy' => $s->accuracy,
            'score' => $s->score,
            'time_used' => $s->time_used,
            'created_at' => $s->created_at,
        ])->values();

        // Level
        $xp = $user->xp;
        $level = (int) floor($xp / self::XP_PER_LEVEL) + 1;
        $xpToNext = self::XP_PER_LEVEL - ($xp % self::XP_PER_LEVEL);

        return [
            'total_sessions' => $totalSessions,
            'avg_accuracy' => $avgAccuracy,
            'best_score' => $bestScore,
            'weakest_topic' => $weakestTopicOverall,
            'weakest_subject' => $weakestSubject,
            'xp' => $xp,
            'level' => $level,
            'xp_to_next_level' => $xpToNext,
            'streak_days' => $user->streak_days,
            'best_streak' => $user->best_streak,
            'per_subject' => $perSubject,
            'mode_breakdown' => $modeBreakdown,
            'recent_sessions' => $recentSessions,
        ];
    }
}
