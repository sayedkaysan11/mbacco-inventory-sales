@extends('layouts.app')
@section('title', 'Pesan Produk')
@section('content')
<h1 class="h3 mb-3">Pesan Produk</h1>
<form method="POST" action="{{ route('shop.order.store') }}">
    @csrf
    <div class="card mb-3"><div class="card-body">
        @include('transactions._items', ['options' => $options, 'mode' => 'sale'])
    </div></div>
    <button class="btn btn-primary">Kirim Pesanan</button>
    <a href="{{ route('shop.index') }}" class="btn btn-link">Batal</a>
</form>
@endsection
