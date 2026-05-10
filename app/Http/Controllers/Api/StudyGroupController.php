<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudyGroup;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StudyGroupController extends Controller
{
    use ResponseTrait;

    /**
     * List groups the authenticated user belongs to.
     * - School student: only their school's groups.
     * - Individual student: only non-school groups.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $groups = StudyGroup::with(['members'])
            ->whereHas('members', fn ($q) => $q->where('user_id', $user->id))
            ->when(
                $user->school_id,
                fn ($q) => $q->where('school_id', $user->school_id),
                fn ($q) => $q->whereNull('school_id'),
            )
            ->latest()
            ->get()
            ->map(fn ($g) => $this->formatGroup($g, $user->id));

        return $this->successResponse($groups, 'Groups retrieved');
    }

    /**
     * Browse discoverable groups (non-school students only, shows non-school groups).
     */
    public function browse(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->school_id) {
            return $this->errorResponse('School students cannot browse public groups.', 403);
        }

        $groups = StudyGroup::with(['members'])
            ->whereNull('school_id')
            ->latest()
            ->get()
            ->map(fn ($g) => $this->formatGroup($g, $user->id));

        return $this->successResponse($groups, 'Groups retrieved');
    }

    /**
     * Create a new group.
     * School students are not permitted — their groups are managed by the school.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->school_id) {
            return $this->errorResponse('School students cannot create groups.', 403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:80',
            'subject' => 'nullable|string|max:60',
            'emoji' => 'nullable|string|max:8',
        ]);

        $group = StudyGroup::create([
            ...$data,
            'created_by' => $user->id,
        ]);

        $group->members()->attach($user->id, ['role' => 'admin']);

        return $this->successResponse($this->formatGroup($group->load('members'), $user->id), 'Group created', 201);
    }

    /** Join a group via invite code. */
    public function join(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'code' => 'required|string',
        ]);

        $group = StudyGroup::with('members')->where('invite_code', strtoupper(trim($data['code'])))->first();

        if (! $group) {
            throw ValidationException::withMessages(['code' => 'Invalid invite code.']);
        }

        // School student → must match their school's group
        if ($user->school_id) {
            if ($group->school_id !== $user->school_id) {
                return $this->errorResponse('You can only join groups created by your school.', 403);
            }
        } else {
            // Individual student → cannot join school groups
            if ($group->school_id) {
                return $this->errorResponse('This group is restricted to school members.', 403);
            }
        }

        if ($group->members()->where('user_id', $user->id)->exists()) {
            return $this->successResponse($this->formatGroup($group, $user->id), 'Already a member');
        }

        $group->members()->attach($user->id, ['role' => 'member']);

        return $this->successResponse($this->formatGroup($group->load('members'), $user->id), 'Joined group');
    }

    /** Leave a group. */
    public function leave(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $group = StudyGroup::findOrFail($id);

        $group->members()->detach($user->id);

        return $this->successResponse(null, 'Left group');
    }

    /** Show a single group with members. */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $group = StudyGroup::with(['members'])->findOrFail($id);

        // Verify the user is a member
        if (! $group->members()->where('user_id', $user->id)->exists()) {
            return $this->errorResponse('You are not a member of this group.', 403);
        }

        $formatted = $this->formatGroup($group, $user->id);
        $formatted['members'] = $group->members->map(fn ($m) => [
            'id' => $m->id,
            'name' => $m->name,
            'accuracy' => 0, // could be joined from analytics if desired
            'streak' => (int) $m->streak_days,
            'role' => $m->pivot->role,
        ]);

        return $this->successResponse($formatted, 'Group loaded');
    }

    // ─── Messages ────────────────────────────────────────────────────────────

    public function messages(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $group = StudyGroup::findOrFail($id);

        if (! $group->members()->where('user_id', $user->id)->exists()) {
            return $this->errorResponse('Not a member.', 403);
        }

        $messages = $group->messages()
            ->with('sender')
            ->latest()
            ->limit(100)
            ->get()
            ->reverse()
            ->values()
            ->map(fn ($msg) => [
                'id' => (string) $msg->id,
                'sender_id' => (string) $msg->user_id,
                'sender_name' => $msg->sender?->name ?? 'Unknown',
                'text' => $msg->text,
                'created_at' => $msg->created_at->toISOString(),
                'is_own' => $msg->user_id === $user->id,
            ]);

        return $this->successResponse($messages, 'Messages loaded');
    }

    public function sendMessage(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $group = StudyGroup::findOrFail($id);

        if (! $group->members()->where('user_id', $user->id)->exists()) {
            return $this->errorResponse('Not a member.', 403);
        }

        $data = $request->validate(['text' => 'required|string|max:2000']);

        $msg = $group->messages()->create([
            'user_id' => $user->id,
            'text' => $data['text'],
        ]);

        $msg->load('sender');

        return $this->successResponse([
            'id' => (string) $msg->id,
            'sender_id' => (string) $msg->user_id,
            'sender_name' => $msg->sender?->name ?? 'Unknown',
            'text' => $msg->text,
            'created_at' => $msg->created_at->toISOString(),
            'is_own' => true,
        ], 'Message sent', 201);
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function formatGroup(StudyGroup $group, int $userId): array
    {
        return [
            'id' => $group->id,
            'name' => $group->name,
            'subject' => $group->subject,
            'emoji' => $group->emoji,
            'invite_code' => $group->invite_code,
            'member_count' => $group->members->count(),
            'weekly_avg' => 0, // placeholder – wire to analytics if desired
            'is_school_group' => $group->isSchoolGroup(),
            'is_member' => $group->members->contains('id', $userId),
            'created_at' => $group->created_at?->toISOString(),
        ];
    }
}
