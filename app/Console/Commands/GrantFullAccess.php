<?php

namespace App\Console\Commands;

use App\Enums\RoleEnum;
use App\Enums\SubscriptionTypeEnum;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class GrantFullAccess extends Command
{
    protected $signature = 'ibridge:grant-access {email}';
    protected $description = 'Grant system_admin role + legendary lifetime subscription to a user';

    public function handle(): int
    {
        $email = $this->argument('email');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $password = 'Admin@iBridge!';
            $user = User::create([
                'name'                => 'System Admin',
                'email'               => $email,
                'password'            => Hash::make($password),
                'role'                => RoleEnum::SYSTEM_ADMIN,
                'subscription_type'   => SubscriptionTypeEnum::SCHOOL_LEGENDARY,
                'subscription_expiry' => now()->addYears(100),
                'is_suspended'        => false,
            ]);
            $this->info("✅ Created new user: {$email}");
            $this->warn("🔑 Temporary password: {$password}  (change this immediately!)");
        } else {
            $user->update([
                'role'                => RoleEnum::SYSTEM_ADMIN,
                'subscription_type'   => SubscriptionTypeEnum::SCHOOL_LEGENDARY,
                'subscription_expiry' => now()->addYears(100),
                'is_suspended'        => false,
            ]);
            $this->info("✅ Updated existing user: {$email}");
        }

        $this->table(
            ['Field', 'Value'],
            [
                ['ID',           $user->id],
                ['Name',         $user->name],
                ['Email',        $user->email],
                ['Role',         $user->role->value],
                ['Subscription', $user->subscription_type->value],
                ['Expires',      $user->subscription_expiry?->toDateString()],
                ['Suspended',    $user->is_suspended ? 'Yes' : 'No'],
            ]
        );

        return self::SUCCESS;
    }
}
