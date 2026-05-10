<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Plan;
use Illuminate\Support\Facades\Http;
use App\Enums\RoleEnum;
use Illuminate\Support\Str;

$baseUrl = 'http://localhost:8004/api';
$secret = config('services.paystack.secret') ?: 'test-secret';
config(['services.paystack.secret' => $secret]);

echo "Starting Security Tests...\n";
echo "============================\n\n";

// --- Helpers ---
function pass($msg) { echo "✅ PASS: $msg\n"; }
function fail($msg) { echo "❌ FAIL: $msg\n"; }

// Ensure a test plan exists
$plan = Plan::firstOrCreate(
    ['key' => 'annual'],
    ['label' => 'Annual Plan', 'price_display' => 'N1000', 'period_display' => '/year', 'amount_kobo' => 100000, 'is_popular' => false]
);

// 1. Test Webhook Validation
echo "1. Testing Webhook Validation...\n";
$webhookPayload = json_encode([
    'event' => 'charge.success',
    'data' => [
        'reference' => 'test_ref_' . Str::random(10),
        'amount' => 10, // Only 10 kobo, much less than 100000
        'metadata' => [
            'user_id' => 1,
            'plan' => 'annual'
        ]
    ]
]);
$signature = hash_hmac('sha512', $webhookPayload, $secret);

$response = Http::withHeaders(['x-paystack-signature' => $signature])
    ->withBody($webhookPayload, 'application/json')
    ->post("$baseUrl/paystack/webhook");

if ($response->status() === 400 && str_contains($response->json('message'), 'amount mismatch')) {
    pass("Webhook correctly rejected invalid amount.");
} else {
    fail("Webhook did not reject invalid amount. Status: {$response->status()}");
}

// 2. Test Suspended User
echo "\n2. Testing Suspended User Access...\n";
$studentEmail = Str::random(10) . '@test.com';
$student = User::create([
    'name' => 'Student Test', 'email' => $studentEmail, 'password' => bcrypt('password'), 'role' => RoleEnum::STUDENT->value, 'is_suspended' => false, 'account_type' => \App\Enums\AccountTypeEnum::INDIVIDUAL->value
]);
$student->is_suspended = false;
$student->save();

$loginRes = Http::post("$baseUrl/login", ['email' => $studentEmail, 'password' => 'password']);
$token = $loginRes->json('token');

if ($token) {
    // Should work initially
    $profRes = Http::withToken($token)->get("$baseUrl/profile");
    if ($profRes->status() === 200) {
        // Suspend user
        $student->is_suspended = true;
        $student->save();

        // Try again
        $profResSuspended = Http::withToken($token)->get("$baseUrl/profile");
        if ($profResSuspended->status() === 403) {
            pass("Suspended user correctly blocked (403).");
        } else {
            fail("Suspended user not blocked! Status: {$profResSuspended->status()}");
        }
    } else {
        fail("Initial profile fetch failed.");
    }
} else {
    fail("Failed to login test student.");
}

// 3. Test BOLA (Role Access)
echo "\n3. Testing Role-Based Access (BOLA)...\n";
$partnerEmail = Str::random(10) . '@test.com';
$partner = User::create([
    'name' => 'Partner Test', 'email' => $partnerEmail, 'password' => bcrypt('password'), 'role' => RoleEnum::PARTNER->value, 'account_type' => \App\Enums\AccountTypeEnum::INDIVIDUAL->value
]);
$loginResP = Http::post("$baseUrl/login", ['email' => $partnerEmail, 'password' => 'password']);
$tokenP = $loginResP->json('token');

if ($tokenP) {
    // Try to hit student route (/questions)
    $qRes = Http::withToken($tokenP)->get("$baseUrl/questions?subject=Maths&board=WAEC&mode=light");
    if ($qRes->status() === 403) {
        pass("Partner correctly blocked from student routes (403).");
    } else {
        fail("Partner accessed student route! Status: {$qRes->status()}");
    }
} else {
    fail("Failed to login test partner.");
}

// 4. Test AI Rate Limiting
echo "\n4. Testing AI Rate Limiting...\n";
// Create a fresh student for rate limiting to avoid hitting other throttles
$aiEmail = Str::random(10) . '@test.com';
$aiStudent = User::create([
    'name' => 'AI Test', 'email' => $aiEmail, 'password' => bcrypt('password'), 'role' => RoleEnum::STUDENT->value, 'is_suspended' => false, 'subscription_type' => 'INDIVIDUAL_ACTIVE', 'subscription_expiry' => now()->addDays(30), 'account_type' => \App\Enums\AccountTypeEnum::INDIVIDUAL->value
]);
$aiStudent->is_suspended = false;
$aiStudent->save();
$loginResAI = Http::post("$baseUrl/login", ['email' => $aiEmail, 'password' => 'password']);
$tokenAI = $loginResAI->json('token');

$hitLimit = false;
for ($i = 0; $i < 12; $i++) {
    $aiRes = Http::withToken($tokenAI)->get("$baseUrl/ai-chats");
    if ($aiRes->status() === 429) {
        $hitLimit = true;
        break;
    }
}
if ($hitLimit) {
    pass("AI endpoints correctly rate limited (429).");
} else {
    fail("AI endpoints NOT rate limited!");
}

// 5. Test Study Group Rate Limiting
echo "\n5. Testing Study Group Join Rate Limiting...\n";
$hitGroupLimit = false;
for ($i = 0; $i < 7; $i++) {
    $groupRes = Http::withToken($tokenAI)->post("$baseUrl/groups/join", ['code' => 'INVALID']);
    if ($groupRes->status() === 429) {
        $hitGroupLimit = true;
        break;
    }
}
if ($hitGroupLimit) {
    pass("Group join endpoint correctly rate limited (429).");
} else {
    fail("Group join endpoint NOT rate limited!");
}

echo "\nDone!\n";
