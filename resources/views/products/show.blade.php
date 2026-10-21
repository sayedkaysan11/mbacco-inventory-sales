@extends('layouts.app')
@section('title', $product->name)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ $product->name }}</h1>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">&larr; Kembali</a>
</div>
<div class="card"><div class="card-body">
    <dl class="row mb-0">
        <dt class="col-sm-3">SKU</dt><dd class="col-sm-9"><code>{{ $product->sku }}</code></dd>
        <dt class="col-sm-3">Kategori</dt><dd class="col-sm-9">{{ $product->category->name }}</dd>
        <dt class="col-sm-3">Harga beli</dt><dd class="col-sm-9">Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</dd>
        <dt class="col-sm-3">Harga jual</dt><dd class="col-sm-9">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</dd>
        <dt class="col-sm-3">Stok</dt>
        <dd class="col-sm-9"><span class="{{ $product->isLowStock() ? 'text-danger fw-bold' : '' }}">{{ $product->stock }}</span>
            (minimum {{ $product->minimum_stock }})</dd>
    </dl>
</div></div>
@endsection
