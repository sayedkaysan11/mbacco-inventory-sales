<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct(private SaleService $service) {}

    public function index(Request $request)
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'in:completed,cancelled'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $user = $request->user();

        $sales = Sale::with('user')
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->when($request->q, fn ($q, $s) => $q->where('sale_number', 'like', "%{$s}%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('sold_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('sold_at', '<=', $d))
            ->latest('sold_at')
            ->paginate(10)
            ->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $options = Product::where('stock', '>', 0)->orderBy('name')->get()->map(fn ($p) => [
            'id' => $p->id,
            'label' => "{$p->sku} - {$p->name} (stok: {$p->stock})",
            'price' => (float) $p->selling_price, // hanya untuk tampilan; harga asli dibaca ulang di server
        ]);

        return view('sales.create', compact('options'));
    }

    public function store(SaleRequest $request)
    {
        $sale = $this->service->create($request->user(), $request->validated('items'));

        return redirect()->route('sales.show', $sale)->with('success', 'Penjualan berhasil disimpan dan stok berkurang.');
    }

    public function show(Request $request, Sale $sale)
    {
        abort_unless($request->user()->isAdmin() || $sale->user_id === $request->user()->id, 403);

        $sale->load(['user', 'items.product']);

        return view('sales.show', compact('sale'));
    }

    public function cancel(Sale $sale)
    {
        $this->service->cancel($sale);

        return back()->with('success', 'Penjualan dibatalkan dan stok dikembalikan.');
    }
}
