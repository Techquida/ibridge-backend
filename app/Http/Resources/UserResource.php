<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // is_active: not suspended + subscription valid
        $isActive = !$this->is_suspended && (
            ($this->account_type?->value === 'school' &&
             $this->whenLoaded('school', fn() => $this->school?->subscription_expiry?->isFuture(), false))
            || ($this->subscription_expiry && $this->subscription_expiry->isFuture())
        );

        return [
            'id'                     => $this->id,
            'name'                   => $this->name,
            'email'                  => $this->email,
            'role'                   => $this->role?->value ?? $this->role,
            'account_type'           => $this->account_type?->value ?? $this->account_type,
            'exam_board'             => $this->exam_board,
            'subscription_type'      => $this->subscription_type?->value ?? $this->subscription_type,
            'subscription_expiry'    => $this->subscription_expiry?->toISOString(),
            'school_id'              => $this->school_id,
            'referred_by_partner_id' => $this->referred_by_partner_id,
            'is_suspended'           => (bool) $this->is_suspended,
            // Gamification
            'xp'                     => (int) $this->xp,
            'streak_days'            => (int) $this->streak_days,
            'best_streak'            => (int) $this->best_streak,
            // School info if loaded
            'school'                 => $this->whenLoaded('school', fn() => [
                'id'                  => $this->school?->id,
                'name'                => $this->school?->name,
                'subscription_expiry' => $this->school?->subscription_expiry?->toISOString(),
                'is_suspended'        => (bool) $this->school?->is_suspended,
            ]),
            'created_at'             => $this->created_at,
        ];
    }
}
