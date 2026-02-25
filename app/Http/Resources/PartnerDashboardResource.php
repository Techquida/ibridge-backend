<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerDashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_referred_students' => $this['total_referred_students'] ?? 0,
            'active_referred_students' => $this['active_referred_students'] ?? 0,
            'total_commissions_earned' => $this['total_commissions_earned'] ?? 0.0,
            'unpaid_commissions' => $this['unpaid_commissions'] ?? 0.0,
        ];
    }
}
