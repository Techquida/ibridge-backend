<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    use ResponseTrait;

    /**
     * GET /subjects?board=WAEC
     *
     * Returns distinct subjects that have active questions for the given board.
     * Used by the frontend to dynamically build the subject selection list.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'board' => ['nullable', 'string', 'in:WAEC,JAMB'],
        ]);

        $query = Question::where('is_active', true)->select('subject')->distinct();

        if ($request->filled('board')) {
            $query->where('exam_board', $request->board);
        }

        $subjects = $query->orderBy('subject')->pluck('subject');

        return $this->successResponse($subjects, 'Subjects retrieved successfully');
    }
}
