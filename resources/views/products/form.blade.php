@extends('layouts.app')
@section('title', $product->exists ? 'Ubah Produk' : 'Tambah Produk')
@section('content')
<h1 class="h3 mb-3">{{ $product->exists ? 'Ubah' : 'Tambah' }} Produk</h1>
<div class="card"><div class="card-body">
<form method="POST" action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}">
    @csrf
    @if ($product->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">SKU</label>
            <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="form-control @error('sku') is-invalid @enderror" required>
            @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-8">
            <label class="form-label">Nama produk</label>
            <input type="text" name="name" value="{{ old('name', $product->name) }}" class="form-control @error('name') is-invalid @enderror" required>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Kategori</label>
            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                <option value="">-- pilih --</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Stok minimum</label>
            <input type="number" min="0" step="1" name="minimum_stock" value="{{ old('minimum_stock', $product->minimum_stock ?? 0) }}" class="form-control @error('minimum_stock') is-invalid @enderror" required>
            @error('minimum_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Harga beli</label>
            <input type="number" min="0" step="0.01" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price) }}" class="form-control @error('purchase_price') is-invalid @enderror" required>
            @error('purchase_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Harga jual</label>
            <input type="number" min="0" step="0.01" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" class="form-control @error('selling_price') is-invalid @enderror" required>
            @error('selling_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
    @if ($product->exists)
        <p class="text-muted small mt-3 mb-0">Stok saat ini: <strong>{{ $product->stock }}</strong>. Stok hanya berubah melalui transaksi pembelian/penjualan.</p>
    @endif
    <div class="mt-4">
        <button class="btn btn-primary">Simpan</button>
        <a href="{{ route('products.index') }}" class="btn btn-link">Batal</a>
    </div>
</form>
</div></div>
@endsection
