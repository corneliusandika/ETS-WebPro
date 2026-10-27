<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\DefaultDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryWalletTest extends TestCase
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

    public function test_user_can_create_and_update_a_category(): void
    {
        $this->post('/categories', [
            'name' => 'Kopi',
            'type' => 'expense',
            'color' => '#f97316',
            'icon' => '☕',
        ])->assertRedirect(route('categories.index'));

        $category = Category::where('name', 'Kopi')->firstOrFail();

        $this->put("/categories/{$category->id}", [
            'name' => 'Kopi & Snack',
            'type' => 'expense',
            'color' => '#eab308',
            'icon' => '🍔',
        ])->assertRedirect(route('categories.index'));

        $this->assertSame('Kopi & Snack', $category->fresh()->name);
    }

    public function test_category_that_is_used_by_a_transaction_cannot_be_deleted(): void
    {
        $category = $this->user->categories()->where('type', 'expense')->firstOrFail();
        $wallet = $this->user->wallets()->firstOrFail();

        Transaction::create([
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 10000,
            'date' => now()->toDateString(),
        ]);

        $this->delete("/categories/{$category->id}")->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_user_can_create_update_and_delete_an_empty_wallet(): void
    {
        $this->post('/wallets', [
            'name' => 'Dompet Cadangan',
            'type' => 'cash',
            'initial_balance' => 150000,
            'color' => '#0ea5e9',
        ])->assertRedirect(route('wallets.index'));

        $wallet = Wallet::where('name', 'Dompet Cadangan')->firstOrFail();

        $this->put("/wallets/{$wallet->id}", [
            'name' => 'Dompet Cadangan',
            'type' => 'bank',
            'initial_balance' => 250000,
            'color' => '#6366f1',
            'is_active' => '1',
        ])->assertRedirect(route('wallets.index'));

        $wallet->refresh();
        $this->assertSame('bank', $wallet->type);
        $this->assertSame(250000.0, (float) $wallet->initial_balance);

        $this->delete("/wallets/{$wallet->id}")->assertRedirect(route('wallets.index'));
        $this->assertDatabaseMissing('wallets', ['id' => $wallet->id]);
    }

    public function test_wallet_with_transactions_cannot_be_deleted(): void
    {
        $wallet = $this->user->wallets()->firstOrFail();
        $category = $this->user->categories()->where('type', 'expense')->firstOrFail();

        Transaction::create([
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 10000,
            'date' => now()->toDateString(),
        ]);

        $this->delete("/wallets/{$wallet->id}")->assertRedirect(route('wallets.index'));

        $this->assertDatabaseHas('wallets', ['id' => $wallet->id]);
    }

    public function test_wallet_balance_accounts_for_transactions(): void
    {
        $wallet = Wallet::create([
            'name' => 'Dompet Uji',
            'type' => 'cash',
            'initial_balance' => 100000,
            'color' => '#0ea5e9',
        ]);

        $expense = $this->user->categories()->where('type', 'expense')->firstOrFail();
        $income = $this->user->categories()->where('type', 'income')->firstOrFail();

        Transaction::create([
            'wallet_id' => $wallet->id,
            'category_id' => $income->id,
            'type' => 'income',
            'amount' => 50000,
            'date' => now()->toDateString(),
        ]);

        Transaction::create([
            'wallet_id' => $wallet->id,
            'category_id' => $expense->id,
            'type' => 'expense',
            'amount' => 30000,
            'date' => now()->toDateString(),
        ]);

        $this->assertSame(120000.0, $wallet->fresh()->balance);
    }
}
