<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * Estimasi stok habis. Ini hitungan sederhana, BUKAN prediksi AI:
 *   rata-rata terjual per hari = total terjual 30 hari terakhir / 30
 *   perkiraan habis (hari)     = stok sekarang / rata-rata per hari
 *   saran beli                 = rata-rata per hari x 30 - stok  (agar cukup 30 hari)
 * Transaksi yang dibatalkan tidak dihitung.
 */
class StockForecastController extends Controller
{
    private const WINDOW_DAYS = 30;

    public function index()
    {
        $sold = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->where('sales.sold_at', '>=', now()->subDays(self::WINDOW_DAYS)->startOfDay())
            ->groupBy('sale_items.product_id')
            ->selectRaw('sale_items.product_id, SUM(sale_items.quantity) as qty')
            ->pluck('qty', 'product_id');

        $rows = Product::orderBy('name')->get()->map(function ($p) use ($sold) {
            $qty = (int) ($sold[$p->id] ?? 0);
            $avg = $qty / self::WINDOW_DAYS;
            $daysLeft = $avg > 0 ? (int) floor($p->stock / $avg) : null; // null = belum ada penjualan

            return (object) [
                'id' => $p->id,
                'name' => $p->name,
                'stock' => $p->stock,
                'sold' => $qty,
                'avg' => $avg,
                'days_left' => $daysLeft,
                'date' => $daysLeft === null ? null : now()->addDays($daysLeft)->locale('id'),
                'suggest' => $avg > 0 ? max(0, (int) ceil($avg * 30 - $p->stock)) : 0,
                'status' => match (true) {
                    $daysLeft === null => 'belum laku',
                    $daysLeft <= 7 => 'segera habis',
                    $daysLeft <= 14 => 'waspada',
                    default => 'aman',
                },
            ];
        })->sortBy(fn ($r) => $r->days_left ?? PHP_INT_MAX)->values();

        return view('stock.forecast', ['rows' => $rows, 'window' => self::WINDOW_DAYS]);
    }
}
