<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInventory;
use Tests\TestCase;

class StockForecastTest extends TestCase
{
    use RefreshDatabase, CreatesInventory;

    private function rowFor($response, Product $product)
    {
        return $response->viewData('rows')->firstWhere('id', $product->id);
    }

    public function test_staff_can_open_forecast(): void
    {
        $this->actingAs($this->makeUser('staff'))->get('/stock-forecast')->assertOk();
    }

    public function test_estimate_is_computed_from_last_30_days_sales(): void
    {
        $admin = $this->makeUser('admin');
        $product = $this->makeProduct(stock: 100);
        // jual 60 => stok 40, rata-rata 60/30 = 2 per hari
        $this->actingAs($admin)->post('/sales', ['items' => [['product_id' => $product->id, 'quantity' => 60]]]);

        $row = $this->rowFor($this->actingAs($admin)->get('/stock-forecast'), $product);

        $this->assertSame(60, $row->sold);
        $this->assertEqualsWithDelta(2.0, $row->avg, 0.001);
        $this->assertSame(20, $row->days_left);  // 40 / 2
        $this->assertSame(20, $row->suggest);    // 2 x 30 - 40
        $this->assertSame('aman', $row->status); // > 14 hari
    }

    public function test_low_days_left_is_marked_urgent(): void
    {
        $admin = $this->makeUser('admin');
        $product = $this->makeProduct(stock: 70);
        // jual 60 => stok 10, rata-rata 2/hari => 5 hari lagi
        $this->actingAs($admin)->post('/sales', ['items' => [['product_id' => $product->id, 'quantity' => 60]]]);

        $row = $this->rowFor($this->actingAs($admin)->get('/stock-forecast'), $product);

        $this->assertSame(5, $row->days_left);
        $this->assertSame('segera habis', $row->status);
    }

    public function test_product_without_sales_has_no_estimate(): void
    {
        $product = $this->makeProduct(stock: 10);

        $row = $this->rowFor($this->actingAs($this->makeUser('admin'))->get('/stock-forecast'), $product);

        $this->assertNull($row->days_left);
        $this->assertSame('belum laku', $row->status);
    }

    public function test_cancelled_sale_is_not_counted(): void
    {
        $admin = $this->makeUser('admin');
        $product = $this->makeProduct(stock: 50);
        $this->actingAs($admin)->post('/sales', ['items' => [['product_id' => $product->id, 'quantity' => 10]]]);
        $this->actingAs($admin)->post('/sales/'.Sale::first()->id.'/cancel');

        $row = $this->rowFor($this->actingAs($admin)->get('/stock-forecast'), $product);

        $this->assertSame(0, $row->sold);
        $this->assertNull($row->days_left);
    }
}
