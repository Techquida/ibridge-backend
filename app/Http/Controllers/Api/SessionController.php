<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSessionRequest;
use App\Http\Resources\SessionResource;
use App\Models\Session;
use App\Services\SessionService;
use App\Services\SubscriptionService;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    use ResponseTrait;

    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly SessionService $sessionService,
    ) {}

    /**
     * GET /sessions — paginated list with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Session::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc');

        // Optional filter by subject
        if ($request->filled('subject')) {
            $query->where('subject', $request->subject);
        }

        // Optional filter by mode
        if ($request->filled('mode')) {
            $query->where('mode', $request->mode);
        }

        $sessions = $query->paginate(20);

        return $this->paginatedResponse(
            SessionResource::collection($sessions),
            'Sessions retrieved successfully'
        );
    }

    /**
     * POST /sessions — store a completed session.
     */
    public function store(StoreSessionRequest $request): JsonResponse
    {
        if (!$this->subscriptionService->isUserActive($request->user())) {
            return $this->forbiddenResponse('Your subscription is inactive. Please renew to log sessions.');
        }

        $session = $this->sessionService->store($request->user()->id, $request->validated());

        return $this->createdResponse(new SessionResource($session), 'Session saved successfully');
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
