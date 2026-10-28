<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Pembeli dialihkan ke katalog toko
        if ($user->can('customer')) {
            return redirect()->route('shop.index');
        }

        // Staff hanya melihat angka transaksinya sendiri; admin melihat semuanya.
        $sales = fn () => Sale::where('status', 'completed')
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id));
        $purchases = fn () => Purchase::where('status', 'completed')
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id));

        return view('dashboard', [
            // Semua angka berasal dari query agregat ke database (bukan data statis)
            'totalProducts' => Product::count(),
            'lowStockCount' => Product::whereColumn('stock', '<=', 'minimum_stock')->count(),
            'salesToday' => $sales()->whereDate('sold_at', today())->sum('total'),
            'salesMonth' => $sales()->whereBetween('sold_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('total'),
            'saleCount' => $sales()->count(),
            'purchaseCount' => $purchases()->count(),
            'latestSales' => Sale::with('user')
                ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
                ->latest('sold_at')->limit(5)->get(),
            'lowStockProducts' => Product::with('category')
                ->whereColumn('stock', '<=', 'minimum_stock')
                ->orderBy('stock')->limit(5)->get(),
        ]);
    }
}