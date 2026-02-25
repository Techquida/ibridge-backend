<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerDashboardResource;
use App\Services\PartnerService;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    use ResponseTrait;

    public function __construct(private readonly PartnerService $partnerService) {}

    public function dashboard(Request $request): JsonResponse
    {
        $partner = $this->partnerService->resolvePartnerForUser($request->user());
        $summary = $this->partnerService->getDashboardSummary($partner);

        return $this->successResponse(new PartnerDashboardResource($summary), 'Partner dashboard loaded');
    }
}
