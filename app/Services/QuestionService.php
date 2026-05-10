<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Session;
use Illuminate\Support\Collection;

class QuestionService
{
    /**
     * Mode → question count mapping.
     */
    private const MODE_COUNTS = [
        'light' => 20,
        'deep' => 40,
        'real' => 60,
    ];

    /**
     * Difficulty distribution per mode (% of total count).
     * Ensures a balanced but randomized set — no two requests are the same.
     */
    private const DIFFICULTY_SPLIT = [
        'light' => ['easy' => 0.50, 'medium' => 0.40, 'hard' => 0.10],
        'deep' => ['easy' => 0.30, 'medium' => 0.45, 'hard' => 0.25],
        'real' => ['easy' => 0.25, 'medium' => 0.45, 'hard' => 0.30],
    ];

    /**
     * Get stratified-randomized questions for a session.
     * Each call to this method will return a DIFFERENT random set.
     * `RANDOM()` (SQLite) or `RAND()` (MySQL) is applied at the DB level,
     * so even concurrent requests for the same subject/board produce different sets.
     */
    public function getQuestions(string $subject, string $examBoard, string $mode): Collection
    {
        $total = self::MODE_COUNTS[$mode] ?? 20;
        $split = self::DIFFICULTY_SPLIT[$mode] ?? self::DIFFICULTY_SPLIT['light'];

        $results = collect();

        foreach ($split as $difficulty => $fraction) {
            $count = (int) round($total * $fraction);
            $chunk = Question::query()
                ->active()
                ->forSubject($subject)
                ->forBoard($examBoard)
                ->forDifficulty($difficulty)
                ->inRandomOrder()          // DB-level randomization — unique per request
                ->limit($count)
                ->get();

            $results = $results->merge($chunk);
        }

        // If stratified fetch fell short (sparse question bank), fill remainder with random
        if ($results->count() < $total) {
            $existing = $results->pluck('id');
            $remainder = Question::query()
                ->active()
                ->forSubject($subject)
                ->forBoard($examBoard)
                ->whereNotIn('id', $existing)
                ->inRandomOrder()
                ->limit($total - $results->count())
                ->get();

            $results = $results->merge($remainder);
        }

        // Shuffle result collection so difficulty grouping isn't visible to client
        return $results->shuffle();
    }

    /**
     * Grade a submitted session and return enriched result data.
     *
     * @param  array<int>  $questionIds  Ordered IDs from the session
     * @param  array<int|null>  $answers  Submitted answer indices (null = unanswered)
     * @return array{score: int, accuracy: int, topic_breakdown: array, weakest_topic: string|null, review: array}
     */
    public function grade(array $questionIds, array $answers): array
    {
        $questions = Question::whereIn('id', $questionIds)
            ->get()
            ->keyBy('id');

        $score = 0;
        $topics = [];
        $review = [];

        foreach ($questionIds as $i => $qid) {
            $question = $questions[$qid] ?? null;
            if (! $question) {
                continue;
            }

            $submitted = $answers[$i] ?? null;
            $correct = $question->correct_answer;
            $isCorrect = $submitted !== null && (int) $submitted === $correct;
            $topic = $question->topic;

            if ($isCorrect) {
                $score++;
            }

            // Per-topic accumulation
            if (! isset($topics[$topic])) {
                $topics[$topic] = ['correct' => 0, 'total' => 0];
            }
            $topics[$topic]['total']++;
            if ($isCorrect) {
                $topics[$topic]['correct']++;
            }

            // Per-question review entry (safe — only sent after submission)
            $review[] = [
                'question_id' => $qid,
                'question_text' => $question->question_text,
                'options' => $question->options,
                'submitted' => $submitted,
                'correct_answer' => $correct,        // revealed only in graded response
                'is_correct' => $isCorrect,
                'explanation' => $question->explanation,
                'topic' => $topic,
            ];
        }

        $total = count($questionIds);
        $accuracy = $total > 0 ? (int) round(($score / $total) * 100) : 0;

        // Build topic breakdown (matches existing AnalyticsService/SessionService shape)
        $topicBreakdown = [];
        $weakestAcc = 101;
        $weakestTopic = null;

        foreach ($topics as $topic => $stats) {
            $topicAccuracy = $stats['total'] > 0
                ? (int) round(($stats['correct'] / $stats['total']) * 100)
                : 0;

            $label = match (true) {
                $topicAccuracy >= 70 => 'Strong',
                $topicAccuracy >= 40 => 'Improving',
                default => 'Weak',
            };

            $topicBreakdown[] = [
                'topic' => $topic,
                'correct' => $stats['correct'],
                'total' => $stats['total'],
                'accuracy' => $topicAccuracy,
                'label' => $label,
            ];

            if ($topicAccuracy < $weakestAcc) {
                $weakestAcc = $topicAccuracy;
                $weakestTopic = $topic;
            }
        }

        return compact('score', 'accuracy', 'topicBreakdown', 'weakestTopic', 'review');
    }
}
