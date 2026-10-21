@extends('layouts.app')
@section('title', 'Produk')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Produk</h1>
    @can('admin') <a href="{{ route('products.create') }}" class="btn btn-primary">+ Tambah</a> @endcan
</div>
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari SKU / nama produk..."></div>
    <div class="col-md-3">
        <select name="category_id" class="form-select">
            <option value="">Semua kategori</option>
            @foreach ($categories as $c)
                <option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary">Filter</button>
        <a href="{{ route('products.index') }}" class="btn btn-link">Reset</a>
    </div>
</form>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0 align-middle">
    <thead><tr><th>SKU</th><th>Nama</th><th>Kategori</th><th class="text-end">Harga Jual</th><th class="text-end">Stok</th><th class="text-end">Aksi</th></tr></thead>
    <tbody>
    @forelse ($products as $p)
        <tr>
            <td><code>{{ $p->sku }}</code></td>
            <td><a href="{{ route('products.show', $p) }}">{{ $p->name }}</a></td>
            <td>{{ $p->category->name }}</td>
            <td class="text-end">Rp {{ number_format($p->selling_price, 0, ',', '.') }}</td>
            <td class="text-end">
                <span class="{{ $p->isLowStock() ? 'text-danger fw-bold' : '' }}">{{ $p->stock }}</span>
                @if ($p->isLowStock()) <span class="badge text-bg-danger">rendah</span> @endif
            </td>
            <td class="text-end">
                @can('admin')
                    <a href="{{ route('products.edit', $p) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                    <form method="POST" action="{{ route('products.destroy', $p) }}" class="d-inline" onsubmit="return confirm('Hapus produk ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                @else
                    <a href="{{ route('products.show', $p) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                @endcan
            </td>
        </tr>
    @empty
        <tr><td colspan="6" class="text-center text-muted py-4">Produk tidak ditemukan.</td></tr>
    @endforelse
    </tbody></table></div></div>
<div class="mt-3">{{ $products->links() }}</div>
@endsection
