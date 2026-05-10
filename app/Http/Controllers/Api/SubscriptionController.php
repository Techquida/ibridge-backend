<?php

namespace App\Http\Controllers\Api;

use App\Enums\SubscriptionTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Payment;
use App\Models\Plan;
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
    private const PAYSTACK_INITIALIZE_URL = 'https://api.paystack.co/transaction/initialize';

    public function plans(): JsonResponse
    {
        $plans = Plan::all()->map(function ($plan) {
            return [
                'key' => $plan->key,
                'label' => $plan->label,
                'price' => $plan->price_display,
                'period' => $plan->period_display,
                'amount_kobo' => $plan->amount_kobo,
                'popular' => $plan->is_popular,
                'desc' => $plan->description,
            ];
        });

        return $this->successResponse($plans, 'Subscription plans retrieved successfully.');
    }

    public function initialize(Request $request): JsonResponse
    {
        $request->validate([
            'plan' => 'required|string|in:monthly,term,annual'
        ]);

        $plan = $request->input('plan');
        $amountKobo = $this->getPlanAmount($plan);
        $user = $request->user();

        $secret = config('services.paystack.secret');
        
        $response = Http::withToken($secret)->post(self::PAYSTACK_INITIALIZE_URL, [
            'email' => $user->email,
            'amount' => $amountKobo,
            'callback_url' => 'https://ibridge.app/payment/callback',
            'metadata' => [
                'user_id' => $user->id,
                'plan' => $plan
            ]
        ]);

        if (! $response->successful()) {
            Log::error('Paystack initialize failed', ['response' => $response->body()]);
            return $this->errorResponse('Failed to initialize payment.', 500);
        }

        $data = $response->json('data');

        return $this->successResponse([
            'authorization_url' => $data['authorization_url'],
            'reference' => $data['reference'],
        ], 'Payment initialized');
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'reference' => 'required|string',
            'plan' => 'required|string|in:monthly,term,annual'
        ]);

        $reference = $request->input('reference');
        $plan = $request->input('plan');
        $user = $request->user();

        // Check if already processed
        $existingPayment = Payment::where('reference', $reference)->where('status', 'success')->first();
        if ($existingPayment) {
            // Already processed (maybe by webhook)
            return $this->successResponse([
                'user' => new UserResource($user->fresh()),
                'expiry' => $user->subscription_expiry ? $user->subscription_expiry->toISOString() : null,
                'plan' => $plan,
            ], 'Subscription renewed successfully.');
        }

        $secret = config('services.paystack.secret');
        $response = Http::withToken($secret)->get(self::PAYSTACK_VERIFY_URL.$reference);

        if (! $response->successful()) {
            return $this->errorResponse('Payment verification service unavailable.', 500);
        }

        $data = $response->json('data');
        $status = $data['status'] ?? null;

        if ($status !== 'success') {
            return $this->failureResponse('Payment was not successful. Status: '.$status);
        }

        $amountPaid = $data['amount'] ?? 0;
        $expectedAmount = $this->getPlanAmount($plan);

        if ($amountPaid < $expectedAmount) {
            Log::warning('Paystack amount mismatch', [
                'reference' => $reference,
                'paid' => $amountPaid,
                'expected' => $expectedAmount
            ]);
            return $this->failureResponse('Amount paid does not match plan price.');
        }

        Payment::create([
            'user_id' => $user->id,
            'reference' => $reference,
            'plan' => $plan,
            'amount_kobo' => $amountPaid,
            'status' => 'success',
            'paystack_response' => $data,
        ]);

        $daysToAdd = match($plan) {
            'annual' => 365,
            'term' => 90,
            default => 30,
        };

        $base = ($user->subscription_expiry && $user->subscription_expiry->isFuture())
            ? $user->subscription_expiry
            : Carbon::now();

        $newExpiry = $base->addDays($daysToAdd);

        $user->update([
            'subscription_type' => SubscriptionTypeEnum::INDIVIDUAL_ACTIVE->value,
            'subscription_expiry' => $newExpiry,
        ]);

        return $this->successResponse([
            'user' => new UserResource($user->fresh()),
            'expiry' => $newExpiry->toISOString(),
            'plan' => $plan,
        ], 'Subscription renewed successfully. Expires '.$newExpiry->toFormattedDateString());
    }

    private function getPlanAmount(string $planKey): int
    {
        $plan = Plan::where('key', $planKey)->first();
        return $plan ? $plan->amount_kobo : 200000;
    }
}
