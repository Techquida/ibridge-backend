<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiChat;
use App\Services\GeminiService;
use App\Services\SubscriptionService;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiChatController extends Controller
{
    use ResponseTrait;

    public function __construct(
        private readonly GeminiService $gemini,
        private readonly SubscriptionService $subscriptionService,
    ) {}

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Resolve the user's AI chat access level.
     *
     * Returns one of: 'full' | 'read_only' | 'denied'
     */
    private function chatAccess(Request $request): string
    {
        $user = $request->user();

        if ($this->subscriptionService->isUserActive($user)) {
            return 'full';
        }

        // No active subscription — check grace period
        $expiry = $user->subscription_expiry;

        if (! $expiry) {
            // Trial period already enforced by SubscriptionService; if we reach
            // here the trial has expired. Grant read-only for the grace window.
            $graceDays = (int) config('ai.chat_grace_days', 30);
            $daysSinceCreation = $user->created_at->diffInDays(now());

            // Trial = 7 days; grace starts after trial ends
            $daysIntoGrace = $daysSinceCreation - 7;

            return $daysIntoGrace <= $graceDays ? 'read_only' : 'denied';
        }

        $graceDays = (int) config('ai.chat_grace_days', 30);
        $daysSinceExpiry = $expiry->diffInDays(now());

        return $daysSinceExpiry <= $graceDays ? 'read_only' : 'denied';
    }

    // ── Endpoints ─────────────────────────────────────────────────────────────

    /**
     * GET /ai-chats
     * List the authenticated user's chats (most recent first).
     */
    public function index(Request $request): JsonResponse
    {
        $access = $this->chatAccess($request);

        if ($access === 'denied') {
            return $this->forbiddenResponse('Your AI chat access has expired. Please re-subscribe to continue.');
        }

        $chats = $request->user()
            ->aiChats()
            ->select('id', 'subject', 'topic', 'title', 'updated_at')
            ->latest('updated_at')
            ->get()
            ->map(fn ($c) => array_merge($c->toArray(), ['access' => $access]));

        return $this->successResponse($chats, 'Chats retrieved successfully');
    }

    /**
     * POST /ai-chats
     * Start a new chat. Requires an active subscription.
     */
    public function store(Request $request): JsonResponse
    {
        $access = $this->chatAccess($request);

        if ($access !== 'full') {
            return $this->forbiddenResponse(
                $access === 'read_only'
                    ? 'Your subscription has expired. You can read existing chats but cannot start new ones.'
                    : 'Your AI chat access has expired. Please re-subscribe to continue.'
            );
        }

        $data = $request->validate([
            'subject' => ['nullable', 'string', 'max:100'],
            'topic' => ['nullable', 'string', 'max:100'],
            'initial_message' => ['required', 'string', 'max:2000'],
        ]);

        // Generate a meaningful title from the actual first message
        $aiTitle = $this->gemini->generateChatTitle(
            $data['initial_message'],
            $data['subject'] ?? null,
        ) ?? $this->generateTitle($data['subject'] ?? null, $data['topic'] ?? null);

        $chat = $request->user()->aiChats()->create([
            'subject' => $data['subject'] ?? null,
            'topic' => $data['topic'] ?? null,
            'title' => $aiTitle,
        ]);

        // Save user message
        $chat->messages()->create([
            'role' => 'user',
            'content' => $data['initial_message'],
        ]);

        // Build history and get AI reply
        $history = [['role' => 'user', 'content' => $data['initial_message']]];
        $aiReply = $this->gemini->chat(
            $history,
            $chat->subject,
            $chat->topic,
            $request->user()->examBoard ?? null,
        );

        if (! $aiReply) {
            return response()->json([
                'status' => 'error',
                'message' => 'I was unable to generate a response at this time. Please try again in a moment.',
            ], 503);
        }

        $chat->messages()->create([
            'role' => 'model',
            'content' => $aiReply,
        ]);

        $chat->load('messages');

        return $this->successResponse($chat, 'Chat started successfully', 201);
    }

    /**
     * GET /ai-chats/{id}
     * Load a single chat with its full message history.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $access = $this->chatAccess($request);

        if ($access === 'denied') {
            return $this->forbiddenResponse('Your AI chat access has expired. Please re-subscribe to continue.');
        }

        $chat = $request->user()->aiChats()->with('messages')->find($id);

        if (! $chat) {
            return $this->notFoundResponse('Chat not found.');
        }

        return $this->successResponse(
            array_merge($chat->toArray(), ['access' => $access]),
            'Chat retrieved successfully'
        );
    }

    /**
     * POST /ai-chats/{id}/messages
     * Send a message and receive an AI reply. Requires an active subscription.
     */
    public function sendMessage(Request $request, int $id): JsonResponse
    {
        $access = $this->chatAccess($request);

        if ($access !== 'full') {
            return $this->forbiddenResponse(
                $access === 'read_only'
                    ? 'Your subscription has expired. You can read existing chats but cannot send new messages.'
                    : 'Your AI chat access has expired. Please re-subscribe to continue.'
            );
        }

        $data = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $chat = $request->user()->aiChats()->with('messages')->find($id);

        if (! $chat) {
            return $this->notFoundResponse('Chat not found.');
        }

        // Save user message
        $chat->messages()->create([
            'role' => 'user',
            'content' => $data['content'],
        ]);

        // Build full history for context (re-load to include the just-saved user message)
        $chat->load('messages');
        $history = $chat->messages
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();

        $aiReply = $this->gemini->chat(
            $history,
            $chat->subject,
            $chat->topic,
            $request->user()->examBoard ?? null,
        );

        if (! $aiReply) {
            return response()->json([
                'status' => 'error',
                'message' => 'I was unable to generate a response at this time. Please try again in a moment.',
            ], 503);
        }

        $chat->messages()->create([
            'role' => 'model',
            'content' => $aiReply,
        ]);

        // Touch the chat's updated_at so it bubbles to top of list
        $chat->touch();

        // Return the full updated chat with all messages
        $chat->load('messages');

        return $this->successResponse(
            array_merge($chat->toArray(), ['access' => 'full']),
            'Message sent successfully'
        );
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function generateTitle(?string $subject, ?string $topic): string
    {
        if ($subject && $topic) {
            return "{$subject} — {$topic}";
        }

        return $subject ?? 'General Chat';
    }

    /**
     * Stub — will be replaced if ResponseTrait doesn't already have notFoundResponse.
     */
    private function notFoundResponse(string $message): JsonResponse
    {
        return response()->json(['message' => $message], 404);
    }
}
