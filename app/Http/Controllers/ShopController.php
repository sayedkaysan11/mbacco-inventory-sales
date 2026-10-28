<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\Request;

/**
 * Sisi pembeli (role customer): katalog, memesan, dan riwayat pesanan sendiri.
 * Pemesanan memakai SaleService yang sama dengan kasir, sehingga aturan stok,
 * DB::transaction, dan lockForUpdate otomatis berlaku juga untuk pembeli.
 */
class ShopController extends Controller
{
    public function __construct(private SaleService $service) {}

    public function index(Request $request)
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer'],
        ]);

        // Hanya kolom yang aman ditampilkan di view: harga beli (modal) tidak ditampilkan ke pembeli.
        $products = Product::with('category')
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%")))
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('shop.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        $options = Product::where('stock', '>', 0)->orderBy('name')->get()->map(fn ($p) => [
            'id' => $p->id,
            'label' => "{$p->name} (stok: {$p->stock})",
            'price' => (float) $p->selling_price, // hanya tampilan; harga asli dibaca ulang di server
        ]);

        return view('shop.order', compact('options'));
    }

    public function store(SaleRequest $request)
    {
        $sale = $this->service->create($request->user(), $request->validated('items'));

        return redirect()->route('shop.orders.show', $sale)->with('success', 'Pesanan berhasil dibuat. Terima kasih!');
    }

    public function orders(Request $request)
    {
        $orders = Sale::where('user_id', $request->user()->id)
            ->latest('sold_at')
            ->paginate(10);

        return view('shop.orders', compact('orders'));
    }

    public function show(Request $request, Sale $sale)
    {
        // Pembeli hanya boleh melihat pesanannya sendiri.
        abort_unless($sale->user_id === $request->user()->id, 403);

        $sale->load('items.product');

        return view('shop.order-show', compact('sale'));
    }
}
