@extends('layouts.app')
@section('title', 'Pembelian')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Pembelian</h1>
    <a href="{{ route('purchases.create') }}" class="btn btn-primary">+ Pembelian Baru</a>
</div>
@include('transactions._filter', ['route' => 'purchases.index', 'placeholder' => 'No. pembelian'])
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0 align-middle">
    <thead><tr><th>No.</th><th>Tanggal</th><th>Pemasok</th><th>Petugas</th><th class="text-end">Total</th><th>Status</th></tr></thead>
    <tbody>
    @forelse ($purchases as $p)
        <tr>
            <td><a href="{{ route('purchases.show', $p) }}">{{ $p->purchase_number }}</a></td>
            <td>{{ $p->purchased_at->format('d/m/Y H:i') }}</td>
            <td>{{ $p->supplier->name }}</td><td>{{ $p->user->name }}</td>
            <td class="text-end">Rp {{ number_format($p->total, 0, ',', '.') }}</td>
            <td><span class="badge text-bg-{{ $p->status === 'completed' ? 'success' : 'secondary' }}">{{ $p->status }}</span></td>
        </tr>
    @empty
        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada transaksi.</td></tr>
    @endforelse
    </tbody></table></div></div>
<div class="mt-3">{{ $purchases->links() }}</div>
@endsection
