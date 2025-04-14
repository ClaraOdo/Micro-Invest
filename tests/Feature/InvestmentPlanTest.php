<?php
namespace Tests\Feature;

use App\Models\InvestmentPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use \App\Models\User;

class InvestmentPlanTest extends TestCase
{
    use RefreshDatabase;
    public function test_index_returns_active_plans()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        // Create 3 active investment plans
        $activePlans = InvestmentPlan::factory()->count(3)->create([
            'is_active' => true,
        ]);

        // Create 2 inactive investment plans
        $inactivePlans = InvestmentPlan::factory()->count(2)->create([
            'is_active' => false,
        ]);

        // Make the request
        $response = $this->getJson('/api/investment-plans');

        // Assert response status and structure
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'return_rate',
                        'lock_period',
                        'lock_period_text',
                        'return_rate_percentage',
                        'minimum_investment',
                        'created_at',
                    ],
                ],
            ])
            ->assertJson([
                'status' => 'success',
            ]);

        // Assert only active plans are returned
        $this->assertEquals(3, count($response->json('data')));

        // Verify all active plan IDs are in the response
        $returnedIds = collect($response->json('data'))->pluck('id')->toArray();
        foreach ($activePlans as $plan) {
            $this->assertContains($plan->id, $returnedIds);
        }
    }
}
