<?php

namespace App\Http\Controllers\Api;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
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
        $timeframe = $request->input('timeframe', 'weekly');
        $board = $request->input('board');

        $query = $this->buildBaseQuery($timeframe);

        // Optional filter by exam board
        if ($board && in_array($board, ['WAEC', 'JAMB'])) {
            $query->where('users.exam_board', $board);
        }

        $top = $query->take(self::TOP_N)->get();

        $entries = $top->map(function ($user, $index) use ($authUser) {
            return [
                'rank' => $index + 1,
                'name' => $this->maskName($user->name),
                'xp' => (int) $user->xp,
                'streak' => $user->streak_days,
                'exam_board' => $user->exam_board,
                'is_me' => $user->id === $authUser->id,
            ];
        })->values();

        // Inject current user's rank if they're not in the top N
        $userInTop = $entries->contains('is_me', true);
        $myRank = null;

        if (! $userInTop) {
            $myAuthUserRow = $this->buildBaseQuery($timeframe)
                ->where('users.id', $authUser->id)
                ->first();

            $myAuthXp = $myAuthUserRow ? (int) $myAuthUserRow->xp : 0;

            if ($timeframe === 'alltime' || $myAuthXp > 0) {
                $myRank = [
                    'rank' => $this->getUserRank($myAuthXp, $timeframe, $board),
                    'name' => $this->maskName($authUser->name),
                    'xp' => $myAuthXp,
                    'streak' => $authUser->streak_days,
                    'exam_board' => $authUser->exam_board,
                    'is_me' => true,
                ];
            }
        }

        return $this->successResponse([
            'entries' => $entries,
            'my_rank' => $myRank,
        ], 'Leaderboard retrieved successfully');
    }

    private function buildBaseQuery(string $timeframe)
    {
        $query = User::query()
            ->where('users.role', RoleEnum::STUDENT->value)
            ->where('users.is_suspended', false);

        if ($timeframe === 'weekly') {
            $startOfWeek = \Illuminate\Support\Carbon::now()->startOfWeek();
            $query->select('users.id', 'users.name', 'users.streak_days', 'users.exam_board', \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(exam_sessions.xp_earned), 0) as xp'))
                ->join('exam_sessions', function ($join) use ($startOfWeek) {
                    $join->on('users.id', '=', 'exam_sessions.user_id')
                         ->where('exam_sessions.created_at', '>=', $startOfWeek);
                })
                ->groupBy('users.id', 'users.name', 'users.streak_days', 'users.exam_board')
                ->having('xp', '>', 0)
                ->orderBy('xp', 'desc');
        } else {
            $query->select('users.id', 'users.name', 'users.streak_days', 'users.exam_board', 'users.xp')
                ->orderBy('users.xp', 'desc');
        }

        return $query;
    }

    private function maskName(string $name): string
    {
        $parts = explode(' ', trim($name));
        if (count($parts) === 1) {
            return $parts[0];
        }
        $lastName = end($parts);

        return $parts[0].' '.strtoupper(substr($lastName, 0, 1)).'.';
    }

    private function getUserRank(int $userXp, string $timeframe, ?string $board): int
    {
        $query = $this->buildBaseQuery($timeframe);

        if ($board && in_array($board, ['WAEC', 'JAMB'])) {
            $query->where('users.exam_board', $board);
        }

        // We can just use a subquery to count how many users have strictly greater XP
        $sql = $query->toSql();
        $bindings = $query->getBindings();

        $count = \Illuminate\Support\Facades\DB::table(\Illuminate\Support\Facades\DB::raw("({$sql}) as sub"))
            ->mergeBindings($query->getQuery())
            ->where('xp', '>', $userXp)
            ->count();

        return $count + 1;
    }
}
