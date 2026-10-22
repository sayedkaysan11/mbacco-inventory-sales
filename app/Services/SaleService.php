<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Business logic penjualan.
 * Semua perubahan (header, item, stok) terjadi dalam SATU database transaction.
 */
class SaleService
{
    /**
     * @param  array<int, array{product_id:int|string, quantity:int|string}>  $items
     */
    public function create(User $user, array $items): Sale
    {
        // Gabungkan baris produk yang sama, lalu urutkan by id agar urutan lock konsisten
        // (mencegah deadlock jika dua transaksi mengunci produk yang sama).
        $lines = collect($items)
            ->groupBy('product_id')
            ->map(fn ($group) => (int) $group->sum('quantity'))
            ->sortKeys();

        return DB::transaction(function () use ($user, $lines) {
            $sale = Sale::create([
                'user_id' => $user->id,
                'sale_number' => $this->generateNumber(),
                'total' => 0,
                'status' => 'completed',
                'sold_at' => now(),
            ]);

            $total = 0;

            foreach ($lines as $productId => $qty) {
                // lockForUpdate: baris produk dikunci sampai transaksi selesai,
                // sehingga dua penjualan bersamaan tidak bisa sama-sama membaca stok lama (overselling).
                $product = Product::lockForUpdate()->findOrFail($productId);

                if ($qty < 1 || $product->stock < $qty) {
                    // Exception => seluruh transaksi di-rollback (header + item sebelumnya ikut batal)
                    throw ValidationException::withMessages([
                        'items' => "Stok \"{$product->name}\" tidak cukup (tersedia {$product->stock}, diminta {$qty}).",
                    ]);
                }

                // Harga diambil dari DATABASE, bukan dari input browser.
                $subtotal = round($product->selling_price * $qty, 2);

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $product->selling_price,
                    'unit_cost' => $product->purchase_price, // snapshot modal untuk laporan laba
                    'subtotal' => $subtotal,
                ]);

                $product->decrement('stock', $qty);
                $total += $subtotal;
            }

            // Total dihitung server.
            $sale->update(['total' => $total]);

            return $sale;
        });
    }

    /** Pembatalan: stok dikembalikan secara konsisten. */
    public function cancel(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            $sale = Sale::lockForUpdate()->findOrFail($sale->id);

            if ($sale->status !== 'completed') {
                throw ValidationException::withMessages(['status' => 'Transaksi ini sudah dibatalkan.']);
            }

            foreach ($sale->items()->orderBy('product_id')->get() as $item) {
                Product::withTrashed()->lockForUpdate()->findOrFail($item->product_id)
                    ->increment('stock', $item->quantity);
            }

            $sale->update(['status' => 'cancelled']);
        });
    }

    private function generateNumber(): string
    {
        do {
            $number = 'SL-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));
        } while (Sale::where('sale_number', $number)->exists());

        return $number;
    }
}
