<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionResource;
use App\Services\QuestionService;
use App\Services\SubscriptionService;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    use ResponseTrait;

    private QuestionService $questionService;

    private SubscriptionService $subscriptionService;

    public function __construct(
        QuestionService $questionService,
        SubscriptionService $subscriptionService,
    ) {
        $this->questionService = $questionService;
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * GET /questions
     *
     * Returns a freshly randomized (RAND at DB level) set of questions.
     * Correct answers and explanations are NEVER included in this response.
     *
     * Query params:
     *   - subject  (required) e.g. Mathematics
     *   - board    (required) WAEC | JAMB
     *   - mode     (required) light | deep | real
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'subject' => ['required', 'string'],
            'board' => ['required', 'string', 'in:WAEC,JAMB'],
            'mode' => ['required', 'string', 'in:light,deep,real'],
        ]);

        if (! $this->subscriptionService->isUserActive($request->user())) {
            return $this->forbiddenResponse('Your subscription is inactive.');
        }

        $questions = $this->questionService->getQuestions(
            $request->subject,
            $request->board,
            $request->mode,
        );

        return $this->successResponse(
            QuestionResource::collection($questions),
            'Questions retrieved successfully',
        );
    }
}
