<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Business logic pembelian / stock-in. */
class PurchaseService
{
    /**
     * @param  array<int, array{product_id:int|string, quantity:int|string, unit_cost:numeric}>  $items
     */
    public function create(User $user, int $supplierId, array $items): Purchase
    {
        // Gabungkan produk yang sama; harga beli = rata-rata tertimbang bila diinput berbeda.
        $lines = collect($items)->groupBy('product_id')->map(function ($group) {
            $qty = (int) $group->sum('quantity');
            $cost = $group->sum(fn ($i) => $i['quantity'] * $i['unit_cost']);

            return ['quantity' => $qty, 'subtotal' => round($cost, 2), 'unit_cost' => round($cost / max($qty, 1), 2)];
        })->sortKeys();

        return DB::transaction(function () use ($user, $supplierId, $lines) {
            $purchase = Purchase::create([
                'supplier_id' => $supplierId,
                'user_id' => $user->id,
                'purchase_number' => $this->generateNumber(),
                'total' => 0,
                'status' => 'completed',
                'purchased_at' => now(),
            ]);

            $total = 0;

            foreach ($lines as $productId => $line) {
                $product = Product::lockForUpdate()->findOrFail($productId);

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $line['quantity'],
                    'unit_cost' => $line['unit_cost'],
                    'subtotal' => $line['subtotal'],
                ]);

                $product->increment('stock', $line['quantity']);
                $total += $line['subtotal'];
            }

            $purchase->update(['total' => $total]);

            return $purchase;
        });
    }

    /** Pembatalan pembelian: stok dikurangi lagi, tetapi tidak boleh sampai negatif. */
    public function cancel(Purchase $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            $purchase = Purchase::lockForUpdate()->findOrFail($purchase->id);

            if ($purchase->status !== 'completed') {
                throw ValidationException::withMessages(['status' => 'Transaksi ini sudah dibatalkan.']);
            }

            foreach ($purchase->items()->orderBy('product_id')->get() as $item) {
                $product = Product::withTrashed()->lockForUpdate()->findOrFail($item->product_id);

                if ($product->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'status' => "Pembelian tidak dapat dibatalkan: stok \"{$product->name}\" ({$product->stock}) sudah lebih kecil dari kuantitas pembelian ({$item->quantity}).",
                    ]);
                }

                $product->decrement('stock', $item->quantity);
            }

            $purchase->update(['status' => 'cancelled']);
        });
    }

    private function generateNumber(): string
    {
        do {
            $number = 'PO-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));
        } while (Purchase::where('purchase_number', $number)->exists());

        return $number;
    }
}
