<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use App\Services\DefaultDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Category $expenseCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        DefaultDataService::provision($this->user);
        $this->actingAs($this->user);

        $this->expenseCategory = $this->user->categories()->where('type', 'expense')->firstOrFail();
    }

    public function test_user_can_create_a_budget_for_a_month(): void
    {
        $this->post('/budgets', [
            'category_id' => $this->expenseCategory->id,
            'month' => now()->format('Y-m'),
            'amount' => 500000,
        ])->assertRedirect();

        $budget = Budget::firstOrFail();

        $this->assertSame($this->expenseCategory->id, $budget->category_id);
        $this->assertSame(500000.0, (float) $budget->amount);
        $this->assertSame($this->user->id, $budget->user_id);
    }

    public function test_budget_cannot_be_created_twice_for_the_same_category_and_month(): void
    {
        Budget::create([
            'category_id' => $this->expenseCategory->id,
            'month' => now()->startOfMonth()->toDateString(),
            'amount' => 500000,
        ]);

        $response = $this->from('/budgets')->post('/budgets', [
            'category_id' => $this->expenseCategory->id,
            'month' => now()->format('Y-m'),
            'amount' => 750000,
        ]);

        $response->assertRedirect('/budgets');
        $response->assertSessionHasErrors('category_id');
        $this->assertSame(1, Budget::count());
    }

    public function test_budget_requires_an_expense_category(): void
    {
        $incomeCategory = $this->user->categories()->where('type', 'income')->firstOrFail();

        $response = $this->from('/budgets')->post('/budgets', [
            'category_id' => $incomeCategory->id,
            'month' => now()->format('Y-m'),
            'amount' => 500000,
        ]);

        $response->assertRedirect('/budgets');
        $response->assertSessionHasErrors('category_id');
        $this->assertSame(0, Budget::count());
    }

    public function test_user_can_update_a_budget(): void
    {
        $budget = Budget::create([
            'category_id' => $this->expenseCategory->id,
            'month' => now()->startOfMonth()->toDateString(),
            'amount' => 500000,
        ]);

        $this->put("/budgets/{$budget->id}", [
            'category_id' => $this->expenseCategory->id,
            'month' => now()->format('Y-m'),
            'amount' => 900000,
        ])->assertRedirect();

        $this->assertSame(900000.0, (float) $budget->fresh()->amount);
    }
}
