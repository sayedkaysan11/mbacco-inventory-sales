<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInventory;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase, CreatesInventory;

    public function test_sale_decreases_stock_and_total_is_computed_by_server(): void
    {
        $product = $this->makeProduct(['selling_price' => 2000], stock: 10);

        $this->actingAs($this->makeUser())->post('/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
            'total' => 1, 'unit_price' => 1, // input palsu dari browser harus diabaikan
        ])->assertRedirect();

        $this->assertSame(7, $product->fresh()->stock);
        $this->assertDatabaseHas('sales', ['total' => 6000]);
        $this->assertDatabaseHas('sale_items', ['unit_price' => 2000, 'subtotal' => 6000]);
    }

    public function test_insufficient_stock_is_rejected_without_changing_data(): void
    {
        $product = $this->makeProduct(stock: 4);

        $this->actingAs($this->makeUser())->post('/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 6]],
        ])->assertSessionHasErrors('items');

        $this->assertSame(4, $product->fresh()->stock);
        $this->assertSame(0, Sale::count());
        $this->assertSame(0, SaleItem::count());
    }

    public function test_failure_on_second_item_rolls_back_everything(): void
    {
        $ok = $this->makeProduct(stock: 10);
        $short = $this->makeProduct(stock: 1);

        $this->actingAs($this->makeUser())->post('/sales', [
            'items' => [
                ['product_id' => $ok->id, 'quantity' => 5],
                ['product_id' => $short->id, 'quantity' => 99],
            ],
        ])->assertSessionHasErrors('items');

        // Atomicity: item pertama TIDAK boleh tersimpan / mengurangi stok
        $this->assertSame(10, $ok->fresh()->stock);
        $this->assertSame(1, $short->fresh()->stock);
        $this->assertSame(0, Sale::count());
        $this->assertSame(0, SaleItem::count());
    }

    public function test_same_product_on_two_rows_is_merged_before_stock_check(): void
    {
        $product = $this->makeProduct(stock: 5);

        $this->actingAs($this->makeUser())->post('/sales', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
                ['product_id' => $product->id, 'quantity' => 3], // total 6 > stok 5
            ],
        ])->assertSessionHasErrors('items');

        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_cancel_sale_restores_stock(): void
    {
        $product = $this->makeProduct(stock: 10);
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post('/sales', ['items' => [['product_id' => $product->id, 'quantity' => 4]]]);
        $sale = Sale::first();
        $this->assertSame(6, $product->fresh()->stock);

        $this->actingAs($admin)->post("/sales/{$sale->id}/cancel");

        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame('cancelled', $sale->fresh()->status);

        // Pembatalan kedua tidak boleh mengembalikan stok dua kali
        $this->actingAs($admin)->post("/sales/{$sale->id}/cancel");
        $this->assertSame(10, $product->fresh()->stock);
    }
}
