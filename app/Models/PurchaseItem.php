<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    protected $fillable = ['purchase_id', 'product_id', 'quantity', 'unit_cost', 'subtotal'];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product(): BelongsTo
    {
        // withTrashed: riwayat transaksi tetap menampilkan produk yang sudah di-soft-delete
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
