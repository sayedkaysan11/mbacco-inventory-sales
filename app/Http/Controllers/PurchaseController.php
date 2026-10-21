<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(private PurchaseService $service) {}

    public function index(Request $request)
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'in:completed,cancelled'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $user = $request->user();

        $purchases = Purchase::with(['supplier', 'user'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->when($request->q, fn ($q, $s) => $q->where('purchase_number', 'like', "%{$s}%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('purchased_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('purchased_at', '<=', $d))
            ->latest('purchased_at')
            ->paginate(10)
            ->withQueryString();

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $options = Product::orderBy('name')->get()->map(fn ($p) => [
            'id' => $p->id,
            'label' => "{$p->sku} - {$p->name} (stok: {$p->stock})",
            'price' => (float) $p->purchase_price,
        ]);

        return view('purchases.create', [
            'suppliers' => Supplier::orderBy('name')->get(),
            'options' => $options,
        ]);
    }

    public function store(PurchaseRequest $request)
    {
        $purchase = $this->service->create(
            $request->user(),
            (int) $request->supplier_id,
            $request->validated('items')
        );

        return redirect()->route('purchases.show', $purchase)->with('success', 'Pembelian berhasil disimpan dan stok bertambah.');
    }

    public function show(Request $request, Purchase $purchase)
    {
        abort_unless($request->user()->isAdmin() || $purchase->user_id === $request->user()->id, 403);

        $purchase->load(['supplier', 'user', 'items.product']);

        return view('purchases.show', compact('purchase'));
    }

    public function cancel(Purchase $purchase)
    {
        $this->service->cancel($purchase);

        return back()->with('success', 'Pembelian dibatalkan dan stok disesuaikan kembali.');
    }
}
