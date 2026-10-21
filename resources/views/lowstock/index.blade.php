@extends('layouts.app')
@section('title', 'Stok Rendah')
@section('content')
<h1 class="h3 mb-3">Produk Stok Rendah</h1>
<p class="text-muted">Produk dengan stok &le; stok minimum.</p>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead><tr><th>SKU</th><th>Nama</th><th>Kategori</th><th class="text-end">Stok</th><th class="text-end">Minimum</th></tr></thead>
    <tbody>
    @forelse ($products as $p)
        <tr>
            <td><code>{{ $p->sku }}</code></td><td><a href="{{ route('products.show', $p) }}">{{ $p->name }}</a></td>
            <td>{{ $p->category->name }}</td>
            <td class="text-end text-danger fw-bold">{{ $p->stock }}</td><td class="text-end">{{ $p->minimum_stock }}</td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-center text-muted py-4">Semua stok aman.</td></tr>
    @endforelse
    </tbody></table></div></div>
<div class="mt-3">{{ $products->links() }}</div>
@endsection
