<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInventory;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase, CreatesInventory;

    private function sell($user, Product $product, int $qty): void
    {
        $this->actingAs($user)->post('/sales', ['items' => [['product_id' => $product->id, 'quantity' => $qty]]]);
    }

    public function test_staff_cannot_open_or_export_report(): void
    {
        $this->actingAs($this->makeUser('staff'));

        $this->get('/reports')->assertForbidden();
        $this->get('/reports/export')->assertForbidden();
    }

    public function test_profit_is_omzet_minus_modal(): void
    {
        $admin = $this->makeUser('admin');
        $product = $this->makeProduct(['purchase_price' => 1000, 'selling_price' => 1500], stock: 10);
        $this->sell($admin, $product, 4); // omzet 6000, modal 4000, laba 2000

        $this->actingAs($admin)->get('/reports')->assertOk()
            ->assertViewHas('summary', fn ($s) => abs($s['omzet'] - 6000) < 0.01
                && abs($s['modal'] - 4000) < 0.01
                && abs($s['laba'] - 2000) < 0.01
                && $s['transactions'] === 1);
    }

    public function test_old_profit_does_not_change_when_cost_price_changes_later(): void
    {
        $admin = $this->makeUser('admin');
        $product = $this->makeProduct(['purchase_price' => 1000, 'selling_price' => 1500], stock: 10);
        $this->sell($admin, $product, 4);

        Product::whereKey($product->id)->update(['purchase_price' => 9999]); // harga beli naik belakangan

        $this->actingAs($admin)->get('/reports')
            ->assertViewHas('summary', fn ($s) => abs($s['laba'] - 2000) < 0.01);
    }

    public function test_cancelled_sale_is_excluded(): void
    {
        $admin = $this->makeUser('admin');
        $product = $this->makeProduct(stock: 10);
        $this->sell($admin, $product, 2);
        $this->actingAs($admin)->post('/sales/'.Sale::first()->id.'/cancel');

        $this->actingAs($admin)->get('/reports')
            ->assertViewHas('summary', fn ($s) => $s['omzet'] == 0 && $s['transactions'] === 0);
    }

    public function test_date_filter_excludes_sales_outside_period(): void
    {
        $admin = $this->makeUser('admin');
        $product = $this->makeProduct(stock: 10);
        $this->sell($admin, $product, 2);

        $yesterday = now()->subDay()->toDateString();
        $this->actingAs($admin)->get("/reports?date_from={$yesterday}&date_to={$yesterday}")
            ->assertViewHas('summary', fn ($s) => $s['omzet'] == 0);
    }

    public function test_invalid_period_is_rejected(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->get('/reports?date_from=2026-10-20&date_to=2026-10-01')
            ->assertSessionHasErrors('date_to');
    }

    public function test_csv_export_contains_header_and_data(): void
    {
        $admin = $this->makeUser('admin');
        $product = $this->makeProduct(stock: 10);
        $this->sell($admin, $product, 1);

        $response = $this->actingAs($admin)->get('/reports/export');

        $response->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringContainsString('Tanggal,Transaksi,Omzet,Modal,Laba', $csv);
        $this->assertStringContainsString(now()->toDateString(), $csv);
    }
}
