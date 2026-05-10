<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionCheckController extends Controller
{
    use ResponseTrait;

    /**
     * POST /questions/{id}/check
     *
     * Used exclusively in Light Mode for instant per-question feedback.
     * Returns correct_answer and explanation ONLY — no other question data.
     *
     * Body: { "answer": <int> }
     */
    public function check(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'answer' => ['required', 'integer', 'min:0', 'max:3'],
        ]);

        $question = Question::where('is_active', true)->findOrFail($id);

        $submittedAnswer = $request->integer('answer');
        $correctAnswer = $question->correct_answer;
        $isCorrect = $submittedAnswer === $correctAnswer;

        return $this->successResponse([
            'is_correct' => $isCorrect,
            'correct_answer' => $correctAnswer,
            'explanation' => $question->explanation,
        ], 'Answer checked');
    }
}
