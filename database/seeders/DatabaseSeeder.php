<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Akun demo (BUKAN kredensial asli) ----
        User::create([
            'name' => 'Owner MBACCO', 'email' => 'admin@example.com',
            'password' => Hash::make('password123'), 'role' => 'admin',
        ]);
        User::create([
            'name' => 'Staff MBACCO', 'email' => 'staff@example.com',
            'password' => Hash::make('password123'), 'role' => 'staff',
        ]);

        // ---- Kategori (toko MBACCO) ----
        $cats = collect(['Tembakau Linting', 'Rokok Kretek', 'Cerutu & Cigarillo', 'Aksesoris Merokok'])
            ->mapWithKeys(fn ($n) => [$n => Category::create(['name' => $n])]);

        // ---- Pemasok (data contoh) ----
        Supplier::create(['name' => 'CV Tembakau Nusantara', 'phone' => '061-4510001', 'email' => 'order@tembakaunusantara.test']);
        Supplier::create(['name' => 'UD Daun Emas', 'phone' => '0812-0000-1111', 'email' => 'sales@daunemas.test']);
        Supplier::create(['name' => 'PT Aksesori Perokok Jaya', 'phone' => '0813-0000-2222', 'email' => null]);

        // ---- Produk contoh: [kategori, sku, nama, harga beli, harga jual, stok, min stok] ----
        // Ganti dengan produk asli MBACCO bila perlu.
        $products = [
            ['Tembakau Linting', 'TMB-001', 'Tembakau Virginia 50g', 35000, 48000, 40, 10],
            ['Tembakau Linting', 'TMB-002', 'Tembakau Burley 50g', 30000, 42000, 35, 10],
            ['Tembakau Linting', 'TMB-003', 'Tembakau Rajang Halus 100g', 45000, 62000, 25, 8],
            ['Tembakau Linting', 'TMB-004', 'Tembakau Aroma Vanilla 50g', 38000, 52000, 6, 10],   // stok rendah
            ['Rokok Kretek', 'KRT-001', 'Kretek Filter 12 Batang', 17000, 21000, 80, 20],
            ['Rokok Kretek', 'KRT-002', 'Kretek Linting Tangan 10 Batang', 12000, 16000, 60, 15],
            ['Cerutu & Cigarillo', 'CRT-001', 'Cigarillo Mini Vanilla (isi 10)', 40000, 55000, 20, 5],
            ['Cerutu & Cigarillo', 'CRT-002', 'Cerutu Sumatera Premium (1 batang)', 60000, 85000, 4, 5], // stok rendah
            ['Aksesoris Merokok', 'ACC-001', 'Kertas Papir Slim (50 lembar)', 4000, 7000, 150, 30],
            ['Aksesoris Merokok', 'ACC-002', 'Filter Linting 6mm (100 pcs)', 6000, 10000, 90, 20],
            ['Aksesoris Merokok', 'ACC-003', 'Korek Gas Isi Ulang', 5000, 9000, 70, 20],
            ['Aksesoris Merokok', 'ACC-004', 'Pipa Kayu Klasik', 55000, 80000, 12, 3],
            ['Aksesoris Merokok', 'ACC-005', 'Tempat Tembakau (Pouch)', 25000, 40000, 18, 5],
        ];

        foreach ($products as [$cat, $sku, $name, $buy, $sell, $stock, $min]) {
            $p = new Product([
                'category_id' => $cats[$cat]->id, 'sku' => $sku, 'name' => $name,
                'purchase_price' => $buy, 'selling_price' => $sell, 'minimum_stock' => $min,
            ]);
            $p->stock = $stock; // stock tidak mass-assignable, diisi langsung hanya pada seeder
            $p->save();
        }
    }
}
