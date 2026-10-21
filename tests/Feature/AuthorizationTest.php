<?php

namespace Tests\Feature;

use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInventory;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase, CreatesInventory;

    public function test_staff_cannot_access_admin_pages_via_direct_url(): void
    {
        $this->actingAs($this->makeUser('staff'));

        $this->get('/categories')->assertForbidden();
        $this->get('/suppliers')->assertForbidden();
        $this->get('/users')->assertForbidden();
        $this->get('/products/create')->assertForbidden();
        $this->post('/products', [])->assertForbidden();
    }

    public function test_staff_cannot_edit_delete_or_cancel(): void
    {
        $staff = $this->makeUser('staff');
        $product = $this->makeProduct();

        $this->actingAs($staff)->post('/sales', ['items' => [['product_id' => $product->id, 'quantity' => 1]]]);
        $sale = Sale::first();

        $this->actingAs($staff)->put("/products/{$product->id}", [])->assertForbidden();
        $this->actingAs($staff)->delete("/products/{$product->id}")->assertForbidden();
        $this->actingAs($staff)->post("/sales/{$sale->id}/cancel")->assertForbidden();
    }

    public function test_staff_can_view_products_and_create_transactions(): void
    {
        $this->actingAs($this->makeUser('staff'));

        $this->get('/products')->assertOk();
        $this->get('/sales/create')->assertOk();
        $this->get('/purchases/create')->assertOk();
    }

    public function test_staff_cannot_see_another_users_sale(): void
    {
        $product = $this->makeProduct();
        $owner = $this->makeUser('staff');
        $this->actingAs($owner)->post('/sales', ['items' => [['product_id' => $product->id, 'quantity' => 1]]]);
        $sale = Sale::first();

        $this->actingAs($this->makeUser('staff'))->get("/sales/{$sale->id}")->assertForbidden();
        $this->actingAs($this->makeUser('admin'))->get("/sales/{$sale->id}")->assertOk();
    }

    public function test_admin_can_access_admin_pages(): void
    {
        $this->actingAs($this->makeUser('admin'));

        $this->get('/categories')->assertOk();
        $this->get('/suppliers')->assertOk();
        $this->get('/users')->assertOk();
        $this->get('/dashboard')->assertOk();
    }
}
