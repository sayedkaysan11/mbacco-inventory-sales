@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<h1 class="h3 mb-4">Dashboard</h1>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="card"><div class="card-body">
        <div class="text-muted small">Total Produk</div><div class="fs-3 fw-bold">{{ $totalProducts }}</div></div></div></div>
    <div class="col-6 col-lg-3"><div class="card"><div class="card-body">
        <div class="text-muted small">Penjualan Hari Ini</div><div class="fs-4 fw-bold">Rp {{ number_format($salesToday, 0, ',', '.') }}</div></div></div></div>
    <div class="col-6 col-lg-3"><div class="card"><div class="card-body">
        <div class="text-muted small">Penjualan Bulan Ini</div><div class="fs-4 fw-bold">Rp {{ number_format($salesMonth, 0, ',', '.') }}</div></div></div></div>
    <div class="col-6 col-lg-3"><div class="card {{ $lowStockCount ? 'border-danger' : '' }}"><div class="card-body">
        <div class="text-muted small">Produk Stok Rendah</div><div class="fs-3 fw-bold {{ $lowStockCount ? 'text-danger' : '' }}">{{ $lowStockCount }}</div></div></div></div>
    <div class="col-6 col-lg-3"><div class="card"><div class="card-body">
        <div class="text-muted small">Transaksi Penjualan</div><div class="fs-3 fw-bold">{{ $saleCount }}</div></div></div></div>
    <div class="col-6 col-lg-3"><div class="card"><div class="card-body">
        <div class="text-muted small">Transaksi Pembelian</div><div class="fs-3 fw-bold">{{ $purchaseCount }}</div></div></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card"><div class="card-header">Penjualan Terbaru</div>
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
                    <tr><td colspan="3" class="text-center text-muted py-3">Belum ada penjualan.</td></tr>
                @endforelse
                </tbody></table></div></div>
    </div>
    <div class="col-lg-6">
        <div class="card"><div class="card-header d-flex justify-content-between">Stok Rendah
            <a href="{{ route('products.low-stock') }}" class="small">Lihat semua</a></div>
            <div class="table-responsive"><table class="table table-sm mb-0">
                <thead><tr><th>Produk</th><th>Stok</th><th>Min.</th></tr></thead>
                <tbody>
                @forelse ($lowStockProducts as $p)
                    <tr><td>{{ $p->name }}</td><td class="text-danger fw-bold">{{ $p->stock }}</td><td>{{ $p->minimum_stock }}</td></tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">Semua stok aman.</td></tr>
                @endforelse
                </tbody></table></div></div>
    </div>
</div>
@endsection
