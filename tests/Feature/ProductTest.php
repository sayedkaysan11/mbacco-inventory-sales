<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInventory;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase, CreatesInventory;

    private function payload(array $over = []): array
    {
        $existing = $this->makeProduct();

        return array_merge([
            'sku' => 'NEW-001', 'name' => 'Produk Baru', 'category_id' => $existing->category_id,
            'purchase_price' => 1000, 'selling_price' => 2000, 'minimum_stock' => 5,
        ], $over);
    }

    public function test_admin_can_create_product(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->post('/products', $this->payload())
            ->assertRedirect('/products');

        $this->assertDatabaseHas('products', ['sku' => 'NEW-001', 'stock' => 0]);
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        $dup = $this->makeProduct(['sku' => 'DUP-1']);

        $this->actingAs($this->makeUser('admin'))
            ->post('/products', $this->payload(['sku' => 'DUP-1', 'category_id' => $dup->category_id]))
            ->assertSessionHasErrors('sku');

        $this->assertSame(1, Product::where('sku', 'DUP-1')->count());
    }

    public function test_negative_price_is_rejected(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->post('/products', $this->payload(['selling_price' => -1]))
            ->assertSessionHasErrors('selling_price');
    }

    public function test_product_used_in_transaction_cannot_be_deleted(): void
    {
        $admin = $this->makeUser('admin');
        $product = $this->makeProduct();

        $this->actingAs($admin)->post('/sales', ['items' => [['product_id' => $product->id, 'quantity' => 1]]]);
        $this->actingAs($admin)->delete("/products/{$product->id}")->assertSessionHas('error');

        $this->assertNotSoftDeleted($product);
    }
}
