<?php
namespace Database\Factories;

use App\Models\InvestmentPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvestmentPlanFactory extends Factory
{
    protected $model = InvestmentPlan::class;

    public function definition()
    {
        return [
            'name'               => $this->faker->word,
            'description'        => $this->faker->sentence,
            'return_rate'        => $this->faker->randomFloat(2, 1, 20),
            'lock_period'        => $this->faker->numberBetween(1, 12),
            'minimum_investment' => $this->faker->randomFloat(2, 100, 10000),
            'is_active'          => true,
        ];
    }

    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
}
