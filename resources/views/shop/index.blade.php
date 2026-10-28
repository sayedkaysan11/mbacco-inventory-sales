@extends('layouts.app')
@section('title', 'Katalog')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Katalog Produk</h1>
    <a href="{{ route('shop.order.create') }}" class="btn btn-primary">Pesan Sekarang</a>
</div>
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari produk..."></div>
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
        <a href="{{ route('shop.index') }}" class="btn btn-link">Reset</a>
    </div>
</form>
<div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-3">
@forelse ($products as $p)
    <div class="col">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <span class="badge text-bg-secondary mb-2">{{ $p->category->name }}</span>
                <h2 class="h6">{{ $p->name }}</h2>
                <div class="fw-bold">Rp {{ number_format($p->selling_price, 0, ',', '.') }}</div>
            </div>
            <div class="card-footer">
                @if ($p->stock > 0)
                    <span class="badge text-bg-success">Tersedia</span> <small class="text-muted">stok {{ $p->stock }}</small>
                @else
                    <span class="badge text-bg-secondary">Stok habis</span>
                @endif
            </div>
        </div>
    </div>
@empty
    <div class="col-12 text-center text-muted py-4">Produk tidak ditemukan.</div>
@endforelse
</div>
<div class="mt-3">{{ $products->links() }}</div>
@endsection
