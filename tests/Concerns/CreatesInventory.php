<?php

namespace Tests\Concerns;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

trait CreatesInventory
{
    protected function makeUser(string $role = 'staff'): User
    {
        return User::create([
            'name' => ucfirst($role).' Test',
            'email' => Str::random(8).'@example.test',
            'password' => Hash::make('password123'),
            'role' => $role,
        ]);
    }

    protected function makeProduct(array $attrs = [], int $stock = 10): Product
    {
        $category = Category::firstOrCreate(['name' => 'Umum']);

        $product = new Product(array_merge([
            'category_id' => $category->id,
            'sku' => 'SKU-'.strtoupper(Str::random(6)),
            'name' => 'Produk '.Str::random(4),
            'purchase_price' => 1000,
            'selling_price' => 1500,
            'minimum_stock' => 2,
        ], $attrs));
        $product->stock = $stock; // stock tidak mass-assignable
        $product->save();

        return $product;
    }
}
