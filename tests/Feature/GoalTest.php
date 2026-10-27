<?php

namespace Tests\Feature;

use App\Models\Goal;
use App\Models\User;
use App\Services\DefaultDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        DefaultDataService::provision($this->user);
        $this->actingAs($this->user);
    }

    public function test_user_can_create_a_savings_goal(): void
    {
        $this->post('/goals', [
            'name' => 'Dana Darurat',
            'target_amount' => 5000000,
            'target_date' => now()->addMonths(6)->toDateString(),
            'color' => '#8b5cf6',
        ])->assertRedirect(route('goals.index'));

        $this->assertDatabaseHas('goals', [
            'user_id' => $this->user->id,
            'name' => 'Dana Darurat',
            'target_amount' => 5000000,
        ]);
    }

    public function test_user_can_deposit_into_a_goal(): void
    {
        $goal = Goal::create([
            'name' => 'Laptop Baru',
            'target_amount' => 1000000,
            'saved_amount' => 0,
            'color' => '#0ea5e9',
        ]);

        $this->post("/goals/{$goal->id}/deposit", ['amount' => 250000])
            ->assertRedirect(route('goals.index'));

        $this->assertSame(250000.0, (float) $goal->fresh()->saved_amount);
    }

    public function test_deposit_cannot_exceed_the_remaining_target(): void
    {
        $goal = Goal::create([
            'name' => 'Sepeda',
            'target_amount' => 1000000,
            'saved_amount' => 900000,
            'color' => '#10b981',
        ]);

        $response = $this->from('/goals')->post("/goals/{$goal->id}/deposit", ['amount' => 200000]);

        $response->assertRedirect('/goals');
        $response->assertSessionHasErrors('amount');
        $this->assertSame(900000.0, (float) $goal->fresh()->saved_amount);
    }

    public function test_goal_progress_is_calculated_from_saved_amount(): void
    {
        $goal = Goal::create([
            'name' => 'Liburan',
            'target_amount' => 2000000,
            'saved_amount' => 500000,
            'color' => '#6366f1',
        ]);

        $this->assertSame(25.0, $goal->progress);
        $this->assertSame(1500000.0, $goal->remaining);
    }
}
