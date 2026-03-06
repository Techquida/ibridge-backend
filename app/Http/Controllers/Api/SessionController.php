<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSessionRequest;
use App\Http\Resources\GradedSessionResource;
use App\Http\Resources\SessionResource;
use App\Models\Session;
use App\Services\QuestionService;
use App\Services\SessionService;
use App\Services\SubscriptionService;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    use ResponseTrait;

    private SubscriptionService $subscriptionService;
    private SessionService $sessionService;
    private QuestionService $questionService;

    public function __construct(
        SubscriptionService $subscriptionService,
        SessionService $sessionService,
        QuestionService $questionService,
    ) {
        $this->subscriptionService = $subscriptionService;
        $this->sessionService      = $sessionService;
        $this->questionService     = $questionService;
    }

    /**
     * GET /sessions — paginated list with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Session::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc');

        if ($request->filled('subject')) {
            $query->where('subject', $request->subject);
        }
        if ($request->filled('mode')) {
            $query->where('mode', $request->mode);
        }

        $sessions = $query->paginate(20);

        return $this->paginatedResponse(
            SessionResource::collection($sessions),
            'Sessions retrieved successfully',
        );
    }

    /**
     * POST /sessions — grade answers server-side, persist session, return enriched result.
     *
     * The client sends question_ids + answers. The backend grades, awards XP/streak,
     * and returns GradedSessionResource (which includes explanations — safe post-submission).
     */
    public function store(StoreSessionRequest $request): JsonResponse
    {
        if (!$this->subscriptionService->isUserActive($request->user())) {
            return $this->forbiddenResponse('Your subscription is inactive. Please renew to log sessions.');
        }

        $data          = $request->validated();
        $questionIds   = $data['question_ids'] ?? [];
        $answers       = $data['answers'] ?? [];

        // ── Server-side grading ───────────────────────────────────────────────
        $graded = $this->questionService->grade($questionIds, $answers);

        // Merge graded results into the data array for SessionService
        $sessionData = array_merge($data, [
            'score'           => $graded['score'],
            'accuracy'        => $graded['accuracy'],
            'total_questions' => count($questionIds),
            'weakest_topic'   => $graded['weakestTopic'],
            'topic_breakdown' => $graded['topicBreakdown'],
        ]);

        $session = $this->sessionService->store($request->user()->id, $sessionData);

        // Attach per-question review data (revealed only in this response)
        $session->review = $graded['review'];

        return $this->createdResponse(
            new GradedSessionResource($session),
            'Session graded and saved successfully',
        );
    }

    /**
     * GET /sessions/{id} — single session detail.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $session = Session::where('user_id', $request->user()->id)
            ->findOrFail($id);

        return $this->successResponse(new SessionResource($session));
    }
}
