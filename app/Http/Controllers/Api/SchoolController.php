<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SchoolDashboardResource;
use App\Http\Resources\UserResource;
use App\Services\SchoolService;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    use ResponseTrait;

    public function __construct(private readonly SchoolService $schoolService) {}

    public function students(Request $request): JsonResponse
    {
        $school = $this->schoolService->resolveSchoolForUser($request->user());
        $students = $school->users()->paginate(15);

        return $this->paginatedResponse(UserResource::collection($students), 'School students retrieved successfully');
    }

    public function activeStudentsCount(Request $request): JsonResponse
    {
        $school = $this->schoolService->resolveSchoolForUser($request->user());
        $summary = $this->schoolService->getSummary($school);

        return $this->successResponse(['active_students_count' => $summary['active_students']]);
    }

    public function summary(Request $request): JsonResponse
    {
        $school = $this->schoolService->resolveSchoolForUser($request->user());
        $summary = $this->schoolService->getSummary($school);

        return $this->successResponse(new SchoolDashboardResource($summary), 'School summary loaded successfully');
    }
}
