@extends('layouts.app')
@section('title', 'Estimasi Stok')
@section('content')
<div class="hero mb-4">
    <div>
        <p class="hero-date">Berdasarkan penjualan {{ $window }} hari terakhir</p>
        <h1 class="hero-title">Estimasi stok habis</h1>
    </div>
</div>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Produk</th><th class="text-end">Stok</th><th class="text-end">Terjual {{ $window }} hari</th><th class="text-end">Rata-rata</th><th>Perkiraan habis</th><th class="text-end">Saran beli</th><th>Status</th></tr></thead>
    <tbody>
    @forelse ($rows as $r)
        <tr>
            <td>{{ $r->name }}</td>
            <td class="text-end">{{ $r->stock }}</td>
            <td class="text-end">{{ $r->sold }}</td>
            @if ($r->days_left === null)
                <td class="text-end text-muted">-</td><td class="text-muted">-</td><td class="text-end">-</td>
                <td><span class="badge text-bg-secondary">belum laku</span></td>
            @else
                <td class="text-end">{{ number_format($r->avg, 1, ',', '.') }}/hari</td>
                <td class="{{ $r->days_left <= 7 ? 'text-danger fw-bold' : '' }}">&asymp; {{ $r->days_left }} hari ({{ $r->date->translatedFormat('j M') }})</td>
                <td class="text-end">{{ $r->suggest ?: '-' }}</td>
                <td>
                    @if ($r->status === 'segera habis') <span class="badge text-bg-danger">segera habis</span>
                    @elseif ($r->status === 'waspada') <span class="badge text-bg-primary">waspada</span>
                    @else <span class="badge text-bg-success">aman</span> @endif
                </td>
            @endif
        </tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada produk.</td></tr>
    @endforelse
    </tbody></table></div></div>
<div class="row g-3 mt-1">
    <div class="col-lg-8"><p class="text-muted small mb-0">Perkiraan habis = stok saat ini dibagi rata-rata terjual per hari. Saran beli = jumlah untuk mencukupi 30 hari ke depan. Ini estimasi sederhana dari data penjualan, bukan prediksi pasti: lonjakan atau penurunan penjualan tidak ikut diperhitungkan.</p></div>
    <div class="col-lg-4 text-lg-end"><span class="badge text-bg-danger">&le; 7 hari</span> <span class="badge text-bg-primary">8 sampai 14 hari</span> <span class="badge text-bg-success">&gt; 14 hari</span></div>
</div>
@endsection
