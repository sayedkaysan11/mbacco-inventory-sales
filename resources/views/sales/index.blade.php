@extends('layouts.app')
@section('title', 'Penjualan')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Penjualan</h1>
    <a href="{{ route('sales.create') }}" class="btn btn-primary">+ Penjualan Baru</a>
</div>
@include('transactions._filter', ['route' => 'sales.index', 'placeholder' => 'No. penjualan'])
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0 align-middle">
    <thead><tr><th>No.</th><th>Tanggal</th><th>Kasir</th><th class="text-end">Total</th><th>Status</th></tr></thead>
    <tbody>
    @forelse ($sales as $s)
        <tr>
            <td><a href="{{ route('sales.show', $s) }}">{{ $s->sale_number }}</a></td>
            <td>{{ $s->sold_at->format('d/m/Y H:i') }}</td><td>{{ $s->user->name }}</td>
            <td class="text-end">Rp {{ number_format($s->total, 0, ',', '.') }}</td>
            <td><span class="badge text-bg-{{ $s->status === 'completed' ? 'success' : 'secondary' }}">{{ $s->status }}</span></td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada transaksi.</td></tr>
    @endforelse
    </tbody></table></div></div>
<div class="mt-3">{{ $sales->links() }}</div>
@endsection
