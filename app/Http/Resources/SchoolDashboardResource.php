<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolDashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_students' => $this->resource['total_students'] ?? 0,
            'active_students' => $this->resource['active_students'] ?? 0,
            'subscription_type' => $this->resource['subscription_type'] ?? null,
            'subscription_expiry' => $this->resource['subscription_expiry'] ?? null,
        ];
    }
}
