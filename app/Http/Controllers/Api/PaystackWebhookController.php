<?php

namespace App\Http\Controllers\Api;

use App\Enums\SubscriptionTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class PaystackWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = config('services.paystack.secret');
        
        $payload = $request->getContent();
        $signature = $request->header('x-paystack-signature');

        if (! $signature || hash_hmac('sha512', $payload, $secret) !== $signature) {
            Log::warning('Paystack webhook invalid signature');
            return response()->json(['status' => 'unauthorized'], 401);
        }

        $event = json_decode($payload, true);

        if (($event['event'] ?? '') === 'charge.success') {
            $data = $event['data'] ?? [];
            $reference = $data['reference'] ?? '';
            
            // Check idempotency
            if (Payment::where('reference', $reference)->exists()) {
                return response()->json(['status' => 'success', 'message' => 'already processed'], 200);
            }

            // Trust our metadata for user_id and plan
            $metadata = $data['metadata'] ?? [];
            $userId = $metadata['user_id'] ?? null;
            $plan = $metadata['plan'] ?? 'monthly';

            if (! $userId) {
                // If metadata is missing user_id, try finding user by email as fallback
                $email = $data['customer']['email'] ?? null;
                if ($email) {
                    $user = User::where('email', $email)->first();
                    $userId = $user?->id;
                }
            } else {
                $user = User::find($userId);
            }

            if (! $user) {
                Log::error('Paystack webhook user not found', ['reference' => $reference]);
                return response()->json(['status' => 'error', 'message' => 'user not found'], 404);
            }

            $amountPaid = $data['amount'] ?? 0;

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

            Log::info('Paystack webhook processed successfully', ['reference' => $reference, 'user_id' => $user->id]);
        }

        return response()->json(['status' => 'success']);
    }
}
