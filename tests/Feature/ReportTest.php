<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DefaultDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        DefaultDataService::provision($this->user);
        $this->actingAs($this->user);

        $this->category = $this->user->categories()->where('type', 'expense')->firstOrFail();
        $wallet = $this->user->wallets()->firstOrFail();

        Transaction::create([
            'wallet_id' => $wallet->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => 125000,
            'date' => now()->toDateString(),
            'note' => 'Belanja bulanan',
        ]);
    }

    public function test_monthly_report_shows_summary(): void
    {
        $response = $this->get('/reports?month='.now()->format('Y-m'));

        $response->assertOk();
        $response->assertSee('Laporan');
        $response->assertSee($this->category->name);
        $response->assertSee(rupiah(125000));
    }

    public function test_report_can_be_exported_to_csv(): void
    {
        $response = $this->get('/reports/export?month='.now()->format('Y-m'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('Tanggal,Jenis,Kategori,Dompet,Catatan,Nominal', $content);
        $this->assertStringContainsString('Belanja bulanan', $content);
    }

    public function test_report_can_be_downloaded_as_pdf(): void
    {
        $response = $this->get('/reports/pdf?month='.now()->format('Y-m'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertHeader('content-disposition', 'attachment; filename="laporan-'.now()->format('Y-m').'.pdf"');

        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertGreaterThan(1000, strlen($response->getContent()));
    }
}
