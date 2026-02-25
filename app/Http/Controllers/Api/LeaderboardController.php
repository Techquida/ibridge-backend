<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\RoleEnum;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    use ResponseTrait;

    private const TOP_N = 50;

    public function index(Request $request): JsonResponse
    {
        $authUser = $request->user();

        $query = User::where('role', RoleEnum::STUDENT->value)
            ->where('is_suspended', false)
            ->orderBy('xp', 'desc');

        // Optional filter by exam board
        if ($request->filled('board') && in_array($request->board, ['WAEC', 'JAMB'])) {
            $query->where('exam_board', $request->board);
        }

        $top = $query->take(self::TOP_N)->get(['id', 'name', 'xp', 'streak_days', 'exam_board']);

        $entries = $top->map(function ($user, $index) use ($authUser) {
            return [
                'rank'       => $index + 1,
                'name'       => $this->maskName($user->name),
                'xp'         => $user->xp,
                'streak'     => $user->streak_days,
                'exam_board' => $user->exam_board,
                'is_me'      => $user->id === $authUser->id,
            ];
        })->values();

        // Inject current user's rank if they're not in the top N
        $userInTop = $entries->contains('is_me', true);
        $myRank = null;

        if (!$userInTop) {
            $myRank = [
                'rank'       => $this->getUserRank($authUser, $request->board ?? null),
                'name'       => $this->maskName($authUser->name),
                'xp'         => $authUser->xp,
                'streak'     => $authUser->streak_days,
                'exam_board' => $authUser->exam_board,
                'is_me'      => true,
            ];
        }

        return $this->successResponse([
            'entries'  => $entries,
            'my_rank'  => $myRank,
        ], 'Leaderboard retrieved successfully');
    }

    private function maskName(string $name): string
    {
        $parts = explode(' ', trim($name));
        if (count($parts) === 1) {
            return $parts[0];
        }
        $lastName = end($parts);
        return $parts[0] . ' ' . strtoupper(substr($lastName, 0, 1)) . '.';
    }

    private function getUserRank(User $user, ?string $board): int
    {
        $query = User::where('role', RoleEnum::STUDENT->value)
            ->where('is_suspended', false)
            ->where('xp', '>', $user->xp);

        if ($board && in_array($board, ['WAEC', 'JAMB'])) {
            $query->where('exam_board', $board);
        }

        return $query->count() + 1;
    }
}
