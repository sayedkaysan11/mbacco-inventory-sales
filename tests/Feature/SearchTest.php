<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInventory;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase, CreatesInventory;

    public function test_search_filters_products_by_name_and_sku(): void
    {
        $this->makeProduct(['name' => 'Kopi Hitam', 'sku' => 'KPI-1']);
        $this->makeProduct(['name' => 'Teh Manis', 'sku' => 'TEH-1']);

        $this->actingAs($this->makeUser());

        $this->get('/products?q=Kopi')->assertSee('Kopi Hitam')->assertDontSee('Teh Manis');
        $this->get('/products?q=TEH-1')->assertSee('Teh Manis')->assertDontSee('Kopi Hitam');
    }

    public function test_pagination_limits_rows_and_keeps_filter(): void
    {
        foreach (range(1, 12) as $i) {
            $this->makeProduct(['name' => sprintf('Barang %02d', $i)]);
        }

        $this->actingAs($this->makeUser());

        $this->get('/products?q=Barang')->assertSee('Barang 01')->assertDontSee('Barang 12');
        $this->get('/products?q=Barang&page=2')->assertSee('Barang 12')->assertDontSee('Barang 01');
    }

    public function test_dashboard_counts_come_from_database(): void
    {
        $this->makeProduct(stock: 1); // stok 1 <= minimum 2 => rendah
        $this->makeProduct(stock: 50);

        $this->actingAs($this->makeUser('admin'))->get('/dashboard')
            ->assertOk()->assertViewHas('totalProducts', 2)->assertViewHas('lowStockCount', 1);
    }
}
