@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="hero mb-4">
    <div>
        <p class="hero-date">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
        <h1 class="hero-title">Selamat datang, {{ auth()->user()->name }}</h1>
    </div>
    <a href="{{ route('sales.create') }}" class="btn btn-primary btn-lg">Catat penjualan</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="panel panel-feature h-100">
            <div class="panel-label">Penjualan bulan ini</div>
            <div class="feature-num">Rp <span data-count="{{ (int) $salesMonth }}">{{ number_format($salesMonth, 0, ',', '.') }}</span></div>
            <div class="feature-sub">Hari ini: Rp <span data-count="{{ (int) $salesToday }}">{{ number_format($salesToday, 0, ',', '.') }}</span></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="row g-3 h-100">
            <div class="col-6"><div class="panel h-100">
                <div class="panel-label">Total produk</div>
                <div class="stat-num" data-count="{{ $totalProducts }}">{{ $totalProducts }}</div></div></div>
            <div class="col-6"><div class="panel h-100 {{ $lowStockCount ? 'panel-danger' : '' }}">
                <div class="panel-label">Produk stok rendah</div>
                <div class="stat-num" data-count="{{ $lowStockCount }}">{{ $lowStockCount }}</div></div></div>
            <div class="col-6"><div class="panel h-100">
                <div class="panel-label">Transaksi penjualan</div>
                <div class="stat-num" data-count="{{ $saleCount }}">{{ $saleCount }}</div></div></div>
            <div class="col-6"><div class="panel h-100">
                <div class="panel-label">Transaksi pembelian</div>
                <div class="stat-num" data-count="{{ $purchaseCount }}">{{ $purchaseCount }}</div></div></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card"><div class="card-header">Penjualan terbaru</div>
            <div class="table-responsive"><table class="table table-sm mb-0">
                <thead><tr><th>No.</th><th>Waktu</th><th class="text-end">Total</th></tr></thead>
                <tbody>
                @forelse ($latestSales as $sale)
                    <tr>
                        <td><a href="{{ route('sales.show', $sale) }}">{{ $sale->sale_number }}</a></td>
                        <td>{{ $sale->sold_at->format('d/m/Y H:i') }}</td>
                        <td class="text-end">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">Belum ada penjualan. <a href="{{ route('sales.create') }}">Catat yang pertama</a>.</td></tr>
                @endforelse
                </tbody></table></div></div>
    </div>
    <div class="col-lg-6">
        <div class="card"><div class="card-header d-flex justify-content-between align-items-center">Stok perlu diisi ulang
            <a href="{{ route('products.low-stock') }}" class="small">Lihat semua</a></div>
            <div class="table-responsive"><table class="table table-sm mb-0">
                <thead><tr><th>Produk</th><th class="text-end">Stok</th><th class="text-end">Minimum</th></tr></thead>
                <tbody>
                @forelse ($lowStockProducts as $p)
                    <tr><td>{{ $p->name }}</td><td class="text-end text-danger fw-bold">{{ $p->stock }}</td><td class="text-end">{{ $p->minimum_stock }}</td></tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">Semua stok aman.</td></tr>
                @endforelse
                </tbody></table></div></div>
    </div>
</div>
@endsection
