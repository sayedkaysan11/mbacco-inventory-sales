<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            // Harga modal dicatat saat transaksi terjadi (snapshot), supaya laporan laba lama
            // tidak berubah ketika harga beli produk diubah di kemudian hari.
            $table->decimal('unit_cost', 12, 2)->default(0)->after('unit_price');
        });

        // Data lama: isi dengan harga beli produk saat ini (perkiraan terbaik yang tersedia).
        DB::table('sale_items')->update([
            'unit_cost' => DB::raw('(SELECT purchase_price FROM products WHERE products.id = sale_items.product_id)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('unit_cost');
        });
    }
};
