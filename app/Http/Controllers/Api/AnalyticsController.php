<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentAnalyticsResource;
use App\Models\Session;
use App\Services\AnalyticsService;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    use ResponseTrait;

    private AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index(Request $request): JsonResponse
    {
        $analytics = $this->analyticsService->getStudentAnalytics($request->user());

        return $this->successResponse($analytics, 'Analytics retrieved successfully');
    }
}
