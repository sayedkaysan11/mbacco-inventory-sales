<?php

namespace Tests\Feature;

use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInventory;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase, CreatesInventory;

    private function order($customer, $product, int $qty)
    {
        return $this->actingAs($customer)->post('/shop/order', [
            'items' => [['product_id' => $product->id, 'quantity' => $qty]],
        ]);
    }

    public function test_visitor_can_register_as_customer_and_role_cannot_be_forced(): void
    {
        $this->post('/register', [
            'name' => 'Pembeli Uji',
            'email' => 'pembeli@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin', // harus diabaikan server
        ])->assertRedirect(route('shop.index'));

        $this->assertDatabaseHas('users', ['email' => 'pembeli@example.test', 'role' => 'customer']);
        $this->assertAuthenticated();
    }

    public function test_customer_cannot_open_staff_or_admin_pages(): void
    {
        $this->actingAs($this->makeUser('customer'));

        foreach (['/products', '/low-stock', '/sales', '/sales/create', '/purchases',
            '/purchases/create', '/categories', '/suppliers', '/users', '/products/create',
            '/stock-forecast', '/reports', '/reports/export'] as $url) {
            $this->get($url)->assertForbidden();
        }

        $this->post('/sales', ['items' => [['product_id' => 1, 'quantity' => 1]]])->assertForbidden();
    }

    public function test_staff_cannot_open_customer_shop(): void
    {
        $this->actingAs($this->makeUser('staff'))->get('/shop')->assertForbidden();
    }

    public function test_customer_dashboard_redirects_to_shop(): void
    {
        $this->actingAs($this->makeUser('customer'))->get('/dashboard')->assertRedirect(route('shop.index'));
    }

    public function test_customer_sees_catalog_but_not_cost_price(): void
    {
        $this->makeProduct(['name' => 'Kopi Hitam', 'purchase_price' => 7351, 'selling_price' => 9999]);

        $this->actingAs($this->makeUser('customer'))->get('/shop')
            ->assertOk()
            ->assertSee('Kopi Hitam')
            ->assertSee('9.999')
            ->assertDontSee('7.351'); // harga beli (modal) tidak boleh tampil
    }

    public function test_customer_order_decreases_stock_and_belongs_to_customer(): void
    {
        $product = $this->makeProduct(['selling_price' => 2000], stock: 10);
        $customer = $this->makeUser('customer');

        $this->actingAs($customer)->post('/shop/order', [
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
            'total' => 1, // input palsu dari browser harus diabaikan
        ])->assertRedirect();

        $this->assertSame(7, $product->fresh()->stock);
        $this->assertDatabaseHas('sales', ['user_id' => $customer->id, 'total' => 6000]);
    }

    public function test_customer_order_with_insufficient_stock_is_rejected(): void
    {
        $product = $this->makeProduct(stock: 2);

        $this->order($this->makeUser('customer'), $product, 5)->assertSessionHasErrors('items');

        $this->assertSame(2, $product->fresh()->stock);
        $this->assertSame(0, Sale::count());
    }

    public function test_customer_cannot_see_another_customers_order(): void
    {
        $product = $this->makeProduct(stock: 10);
        $owner = $this->makeUser('customer');
        $this->order($owner, $product, 1);
        $sale = Sale::first();

        $this->actingAs($this->makeUser('customer'))->get("/shop/orders/{$sale->id}")->assertForbidden();
        $this->actingAs($owner)->get("/shop/orders/{$sale->id}")->assertOk();
    }

    public function test_customer_cannot_cancel_order(): void
    {
        $product = $this->makeProduct(stock: 10);
        $customer = $this->makeUser('customer');
        $this->order($customer, $product, 2);
        $sale = Sale::first();

        $this->actingAs($customer)->post("/sales/{$sale->id}/cancel")->assertForbidden();

        $this->assertSame(8, $product->fresh()->stock);
    }
}
