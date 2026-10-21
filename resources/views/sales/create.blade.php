@extends('layouts.app')
@section('title', 'Penjualan Baru')
@section('content')
<h1 class="h3 mb-3">Penjualan Baru</h1>
<form method="POST" action="{{ route('sales.store') }}">
    @csrf
    <div class="card mb-3"><div class="card-body">
        @include('transactions._items', ['options' => $options, 'mode' => 'sale'])
    </div></div>
    <button class="btn btn-primary">Simpan Penjualan</button>
    <a href="{{ route('sales.index') }}" class="btn btn-link">Batal</a>
</form>
@endsection
