<?php
namespace Database\Seeders;

use App\Models\InvestmentPlan;
use Illuminate\Database\Seeder;

class InvestmentPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name'               => 'SaveDaily',
                'description'        => 'A perfect plan for short-term savings with daily returns.',
                'return_rate'        => 0.005, // 0.5% daily
                'lock_period'        => 7,     // 7 days
                'minimum_investment' => 1000.00,
                'is_active'          => true,
            ],
            [
                'name'               => 'GrowWeekly',
                'description'        => 'Medium-term investment plan with competitive returns.',
                'return_rate'        => 0.01, // 1% daily
                'lock_period'        => 30,   // 30 days
                'minimum_investment' => 5000.00,
                'is_active'          => true,
            ],
            [
                'name'               => 'GrowFast',
                'description'        => 'Higher risk, higher return investment option for long-term growth.',
                'return_rate'        => 0.015, // 1.5% daily
                'lock_period'        => 90,    // 90 days
                'minimum_investment' => 10000.00,
                'is_active'          => true,
            ],
        ];

        foreach ($plans as $plan) {
            InvestmentPlan::create($plan);
        }

    }
}
