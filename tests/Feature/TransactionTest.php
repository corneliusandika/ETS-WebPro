<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\DefaultDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Wallet $wallet;

    private Category $expenseCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        DefaultDataService::provision($this->user);
        $this->actingAs($this->user);

        $this->wallet = $this->user->wallets()->firstOrFail();
        $this->expenseCategory = $this->user->categories()->where('type', 'expense')->firstOrFail();
    }

    public function test_user_can_create_a_transaction(): void
    {
        $response = $this->post('/transactions', [
            'type' => 'expense',
            'amount' => 75000,
            'date' => now()->toDateString(),
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->expenseCategory->id,
            'note' => 'Makan siang',
        ]);

        $response->assertRedirect(route('transactions.index'));

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'amount' => 75000,
            'type' => 'expense',
        ]);
    }

    public function test_transaction_type_must_match_the_category_type(): void
    {
        $response = $this->from('/transactions/create')->post('/transactions', [
            'type' => 'income',
            'amount' => 75000,
            'date' => now()->toDateString(),
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->expenseCategory->id,
        ]);

        $response->assertRedirect('/transactions/create');
        $response->assertSessionHasErrors('category_id');
        $this->assertSame(0, Transaction::count());
    }

    public function test_user_can_update_and_delete_a_transaction(): void
    {
        $transaction = Transaction::create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->expenseCategory->id,
            'type' => 'expense',
            'amount' => 20000,
            'date' => now()->toDateString(),
            'note' => 'Awal',
        ]);

        $this->put("/transactions/{$transaction->id}", [
            'type' => 'expense',
            'amount' => 35000,
            'date' => now()->toDateString(),
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->expenseCategory->id,
            'note' => 'Diperbarui',
        ])->assertRedirect(route('transactions.index'));

        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'amount' => 35000, 'note' => 'Diperbarui']);

        $this->delete("/transactions/{$transaction->id}")->assertRedirect(route('transactions.index'));

        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }

    public function test_user_cannot_touch_another_users_transaction(): void
    {
        $transaction = Transaction::create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->expenseCategory->id,
            'type' => 'expense',
            'amount' => 20000,
            'date' => now()->toDateString(),
        ]);

        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        $this->get("/transactions/{$transaction->id}/edit")->assertNotFound();
        $this->delete("/transactions/{$transaction->id}")->assertNotFound();
    }

    public function test_transactions_can_be_filtered_by_type_and_keyword(): void
    {
        Transaction::create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->expenseCategory->id,
            'type' => 'expense',
            'amount' => 20000,
            'date' => now()->toDateString(),
            'note' => 'Kopi pagi',
        ]);

        Transaction::create([
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->user->categories()->where('type', 'income')->firstOrFail()->id,
            'type' => 'income',
            'amount' => 5000000,
            'date' => now()->toDateString(),
            'note' => 'Proyek sampingan',
        ]);

        $response = $this->get('/transactions?type=expense&q=Kopi');

        $response->assertOk();
        $response->assertSee('Kopi pagi');
        $response->assertDontSee('Proyek sampingan');
    }
}
