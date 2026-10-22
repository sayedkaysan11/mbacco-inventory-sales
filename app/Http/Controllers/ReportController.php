<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Laporan penjualan dan laba (khusus admin, dijaga middleware can:admin di routes).
 * Laba = subtotal penjualan - (qty x unit_cost). unit_cost adalah harga modal saat transaksi terjadi.
 * Hanya transaksi berstatus "completed" yang dihitung.
 */
class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->period($request);

        return view('reports.index', $this->build($from, $to) + compact('from', 'to'));
    }

    public function export(Request $request)
    {
        [$from, $to] = $this->period($request);
        $days = $this->build($from, $to)['days'];
        $filename = "laporan-laba-{$from->toDateString()}-{$to->toDateString()}.csv";

        return response()->streamDownload(function () use ($days) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8 dengan benar
            fputcsv($out, ['Tanggal', 'Transaksi', 'Omzet', 'Modal', 'Laba']);
            foreach ($days as $d) {
                fputcsv($out, [$d->day, $d->trx, $d->omzet, $d->modal, $d->laba]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function period(Request $request): array
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $from = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : now()->startOfMonth();
        $to = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : now()->endOfDay();

        return [$from, $to];
    }

    private function build(Carbon $from, Carbon $to): array
    {
        // Query dasar: item penjualan dari transaksi selesai pada periode terpilih
        $base = fn () => DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sold_at', [$from, $to]);

        $days = $base()
            ->selectRaw('DATE(sales.sold_at) as day, COUNT(DISTINCT sales.id) as trx,
                SUM(sale_items.subtotal) as omzet, SUM(sale_items.quantity * sale_items.unit_cost) as modal')
            ->groupBy('day')->orderBy('day')->get()
            ->map(function ($r) {
                $r->trx = (int) $r->trx;
                $r->omzet = (float) $r->omzet;
                $r->modal = (float) $r->modal;
                $r->laba = $r->omzet - $r->modal;

                return $r;
            });

        $top = $base()
            ->join('products', 'products.id', '=', 'sale_items.product_id') // termasuk produk yang sudah di-soft-delete
            ->selectRaw('products.name, SUM(sale_items.quantity) as qty, SUM(sale_items.subtotal) as omzet,
                SUM(sale_items.quantity * sale_items.unit_cost) as modal')
            ->groupBy('products.id', 'products.name')
            ->orderByRaw('SUM(sale_items.subtotal) - SUM(sale_items.quantity * sale_items.unit_cost) DESC')
            ->limit(10)->get()
            ->map(function ($r) {
                $r->qty = (int) $r->qty;
                $r->omzet = (float) $r->omzet;
                $r->laba = $r->omzet - (float) $r->modal;
                $r->margin = $r->omzet > 0 ? $r->laba / $r->omzet * 100 : 0;

                return $r;
            });

        $omzet = $days->sum('omzet');
        $modal = $days->sum('modal');
        $trx = $days->sum('trx');

        return [
            'days' => $days,
            'top' => $top,
            'maxLaba' => max(1, (float) $days->max('laba')),
            'summary' => [
                'omzet' => $omzet,
                'modal' => $modal,
                'laba' => $omzet - $modal,
                'margin' => $omzet > 0 ? ($omzet - $modal) / $omzet * 100 : 0,
                'transactions' => $trx,
                'average' => $trx > 0 ? $omzet / $trx : 0,
            ],
        ];
    }
}
