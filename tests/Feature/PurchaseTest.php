<?php

namespace Tests\Feature;

use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInventory;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase, CreatesInventory;

    public function test_purchase_increases_stock(): void
    {
        $product = $this->makeProduct(stock: 10);
        $supplier = Supplier::create(['name' => 'Pemasok Uji']);

        $this->actingAs($this->makeUser('staff'))->post('/purchases', [
            'supplier_id' => $supplier->id,
            'items' => [['product_id' => $product->id, 'quantity' => 15, 'unit_cost' => 1000]],
        ])->assertRedirect();

        $this->assertSame(25, $product->fresh()->stock);
        $this->assertDatabaseHas('purchases', ['supplier_id' => $supplier->id, 'total' => 15000]);
    }

    public function test_invalid_quantity_is_rejected(): void
    {
        $product = $this->makeProduct(stock: 10);
        $supplier = Supplier::create(['name' => 'Pemasok Uji']);

        $this->actingAs($this->makeUser())->post('/purchases', [
            'supplier_id' => $supplier->id,
            'items' => [['product_id' => $product->id, 'quantity' => 0, 'unit_cost' => 1000]],
        ])->assertSessionHasErrors('items.0.quantity');

        $this->assertSame(0, Purchase::count());
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_admin_cancel_reverts_stock(): void
    {
        $product = $this->makeProduct(stock: 10);
        $supplier = Supplier::create(['name' => 'Pemasok Uji']);
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post('/purchases', [
            'supplier_id' => $supplier->id,
            'items' => [['product_id' => $product->id, 'quantity' => 5, 'unit_cost' => 1000]],
        ]);
        $purchase = Purchase::first();

        $this->actingAs($admin)->post("/purchases/{$purchase->id}/cancel");

        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame('cancelled', $purchase->fresh()->status);
    }
}
