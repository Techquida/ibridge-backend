<?php

namespace App\Filament\Widgets;

use App\Enums\RoleEnum;
use App\Models\Commission;
use App\Models\Partner;
use App\Models\School;
use App\Models\Session;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalUsers = User::where('role', '!=', RoleEnum::SYSTEM_ADMIN)->count();
        $activeUsers = User::where('role', '!=', RoleEnum::SYSTEM_ADMIN)
            ->where('is_suspended', false)
            ->where('subscription_expiry', '>', now())
            ->count();

        $totalSchools = School::count();
        $activeSchools = School::where('is_suspended', false)->count();

        $totalPartners = Partner::count();
        $totalSessions = Session::count();

        $totalCommissions = (float) Commission::sum('amount');
        $unpaidCommissions = (float) Commission::where('is_paid', false)->sum('amount');

        return [
            Stat::make('Total Users', $totalUsers)
                ->description('All registered students & partners')
                ->color('primary')
                ->icon('heroicon-o-users'),

            Stat::make('Active Users', $activeUsers)
                ->description('Active subscriptions')
                ->color('success')
                ->icon('heroicon-o-user-check'),

            Stat::make('Total Schools', $totalSchools)
                ->color('info')
                ->icon('heroicon-o-academic-cap'),

            Stat::make('Active Schools', $activeSchools)
                ->description('Not suspended')
                ->color('success')
                ->icon('heroicon-o-academic-cap'),

            Stat::make('Total Partners', $totalPartners)
                ->color('warning')
                ->icon('heroicon-o-briefcase'),

            Stat::make('Total Sessions', $totalSessions)
                ->color('gray')
                ->icon('heroicon-o-book-open'),

            Stat::make('Commissions Generated', '₦'.number_format($totalCommissions, 2))
                ->color('success')
                ->icon('heroicon-o-currency-dollar'),

            Stat::make('Unpaid Commissions', '₦'.number_format($unpaidCommissions, 2))
                ->description('Awaiting payment')
                ->color($unpaidCommissions > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-exclamation-circle'),
        ];
    }
}
