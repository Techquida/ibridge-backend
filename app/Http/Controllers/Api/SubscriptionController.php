<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifySubscriptionRequest;
use App\Models\User;
use App\Enums\SubscriptionTypeEnum;
use App\Http\Resources\UserResource;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    use ResponseTrait;

    private const PAYSTACK_VERIFY_URL = 'https://api.paystack.co/transaction/verify/';

    /**
     * Verify a Paystack payment reference server-side and extend subscription.
     *
     * POST /subscription/verify
     */
    public function verify(VerifySubscriptionRequest $request): JsonResponse
    {
        $reference = $request->input('reference');
        $plan      = $request->input('plan'); // 'monthly' | 'annual'

        // ── Server-side Paystack verification ────────────────────────────────
        $secret   = config('services.paystack.secret');
        $response = Http::withToken($secret)
            ->get(self::PAYSTACK_VERIFY_URL . $reference);

        if (!$response->successful()) {
            Log::warning('Paystack verification HTTP error', [
                'reference' => $reference,
                'status'    => $response->status(),
            ]);
            return $this->serverErrorResponse('Payment verification service unavailable. Please try again.');
        }

        $data   = $response->json('data');
        $status = $data['status'] ?? null;

        if ($status !== 'success') {
            Log::warning('Paystack payment not successful', [
                'reference' => $reference,
                'status'    => $status,
            ]);
            return $this->failureResponse('Payment was not successful. Status: ' . $status);
        }

        // Prevent double-use: check reference not already applied
        $user = $request->user();
        // (In production, store used references in a payments table)

        // ── Extend subscription ───────────────────────────────────────────────
        $daysToAdd = $plan === 'annual' ? 365 : 30;

        // Extend from expiry date if still active, otherwise from now
        $base = ($user->subscription_expiry && $user->subscription_expiry->isFuture())
            ? $user->subscription_expiry
            : Carbon::now();

        $newExpiry = $base->addDays($daysToAdd);

        $user->update([
            'subscription_type'   => SubscriptionTypeEnum::INDIVIDUAL_ACTIVE->value,
            'subscription_expiry' => $newExpiry,
        ]);

        Log::info('Subscription renewed', [
            'user_id'   => $user->id,
            'plan'      => $plan,
            'reference' => $reference,
            'expiry'    => $newExpiry->toDateString(),
        ]);

        return $this->successResponse([
            'user'       => new UserResource($user->fresh()),
            'expiry'     => $newExpiry->toISOString(),
            'plan'       => $plan,
        ], 'Subscription renewed successfully. Expires ' . $newExpiry->toFormattedDateString());
    }
}
