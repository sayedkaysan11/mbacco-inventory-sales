@extends('layouts.app')
@section('title', 'Laporan Laba')
@section('content')
@php $rp = fn ($n) => 'Rp '.number_format($n, 0, ',', '.'); @endphp
<div class="hero mb-4">
    <div>
        <p class="hero-date">{{ $from->locale('id')->translatedFormat('d M Y') }} &ndash; {{ $to->locale('id')->translatedFormat('d M Y') }}</p>
        <h1 class="hero-title">Laporan penjualan dan laba</h1>
    </div>
    <a href="{{ route('reports.export', request()->only('date_from', 'date_to')) }}" class="btn btn-outline-secondary">Ekspor CSV</a>
</div>

<form method="GET" action="{{ route('reports.index') }}" class="row g-2 mb-4">
    <div class="col-md-3"><label class="form-label">Dari tanggal</label>
        <input type="date" name="date_from" value="{{ $from->toDateString() }}" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Sampai tanggal</label>
        <input type="date" name="date_to" value="{{ $to->toDateString() }}" class="form-control"></div>
    <div class="col-auto d-flex align-items-end gap-2">
        <button class="btn btn-primary">Tampilkan</button>
        <a href="{{ route('reports.index', ['date_from' => now()->startOfMonth()->toDateString(), 'date_to' => now()->toDateString()]) }}" class="btn btn-outline-secondary">Bulan ini</a>
    </div>
</form>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="panel panel-feature h-100">
            <div class="panel-label">Laba kotor periode ini</div>
            <div class="feature-num">{{ $rp($summary['laba']) }}</div>
            <div class="feature-sub">Margin {{ number_format($summary['margin'], 1, ',', '.') }}% dari omzet {{ $rp($summary['omzet']) }}</div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="row g-3 h-100">
            <div class="col-6"><div class="panel h-100"><div class="panel-label">Omzet</div><div class="stat-num" style="font-size:1.9rem">{{ $rp($summary['omzet']) }}</div></div></div>
            <div class="col-6"><div class="panel h-100"><div class="panel-label">Modal barang terjual</div><div class="stat-num" style="font-size:1.9rem">{{ $rp($summary['modal']) }}</div></div></div>
            <div class="col-6"><div class="panel h-100"><div class="panel-label">Transaksi</div><div class="stat-num">{{ $summary['transactions'] }}</div></div></div>
            <div class="col-6"><div class="panel h-100"><div class="panel-label">Rata-rata per transaksi</div><div class="stat-num" style="font-size:1.9rem">{{ $rp($summary['average']) }}</div></div></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">Laba per hari</div>
    <div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>Tanggal</th><th class="text-end">Transaksi</th><th class="text-end">Omzet</th><th class="text-end">Modal</th><th class="text-end">Laba</th><th style="width:26%"></th></tr></thead>
        <tbody>
        @forelse ($days as $d)
            <tr>
                <td>{{ \Carbon\Carbon::parse($d->day)->locale('id')->translatedFormat('D d M') }}</td>
                <td class="text-end">{{ $d->trx }}</td>
                <td class="text-end">{{ $rp($d->omzet) }}</td>
                <td class="text-end">{{ $rp($d->modal) }}</td>
                <td class="text-end">{{ $rp($d->laba) }}</td>
                <td><div class="bar-track"><div class="bar-fill" style="width:{{ max(0, $d->laba) / $maxLaba * 100 }}%"></div></div></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada penjualan pada periode ini.</td></tr>
        @endforelse
        </tbody>
        @if ($days->isNotEmpty())
        <tfoot><tr><th>Total</th><th class="text-end">{{ $summary['transactions'] }}</th><th class="text-end">{{ $rp($summary['omzet']) }}</th>
            <th class="text-end">{{ $rp($summary['modal']) }}</th><th class="text-end">{{ $rp($summary['laba']) }}</th><th></th></tr></tfoot>
        @endif
    </table></div>
</div>

<div class="card">
    <div class="card-header">Produk paling menguntungkan</div>
    <div class="table-responsive"><table class="table mb-0">
        <thead><tr><th>Produk</th><th class="text-end">Terjual</th><th class="text-end">Omzet</th><th class="text-end">Laba</th><th class="text-end">Margin</th></tr></thead>
        <tbody>
        @forelse ($top as $t)
            <tr><td>{{ $t->name }}</td><td class="text-end">{{ $t->qty }}</td><td class="text-end">{{ $rp($t->omzet) }}</td>
                <td class="text-end">{{ $rp($t->laba) }}</td><td class="text-end">{{ number_format($t->margin, 0) }}%</td></tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
<p class="text-muted small mt-3">Laba = harga jual dikurangi harga beli saat transaksi terjadi. Transaksi yang dibatalkan tidak dihitung.</p>
@endsection
