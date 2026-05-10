<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'key' => 'monthly',
                'label' => 'Monthly',
                'price_display' => '₦2,000',
                'period_display' => '/ month',
                'amount_kobo' => 200000,
                'is_popular' => false,
                'description' => 'Pay as you go',
            ],
            [
                'key' => 'term',
                'label' => 'Per Term',
                'price_display' => '₦5,000',
                'period_display' => '/ term',
                'amount_kobo' => 500000,
                'is_popular' => true,
                'description' => 'Best for short-term prep',
            ],
            [
                'key' => 'annual',
                'label' => 'Annual',
                'price_display' => '₦20,000',
                'period_display' => '/ year',
                'amount_kobo' => 2000000,
                'is_popular' => false,
                'description' => 'Save 16% vs per-term',
            ],
        ];

        foreach ($plans as $plan) {
            \App\Models\Plan::updateOrCreate(['key' => $plan['key']], $plan);
        }
    }
}
