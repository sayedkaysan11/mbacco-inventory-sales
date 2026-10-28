@extends('layouts.app')
@section('title', 'Pesanan Saya')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Pesanan Saya</h1>
    <a href="{{ route('shop.order.create') }}" class="btn btn-primary">+ Pesan Lagi</a>
</div>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0 align-middle">
    <thead><tr><th>No.</th><th>Tanggal</th><th class="text-end">Total</th><th>Status</th></tr></thead>
    <tbody>
    @forelse ($orders as $o)
        <tr>
            <td><a href="{{ route('shop.orders.show', $o) }}">{{ $o->sale_number }}</a></td>
            <td>{{ $o->sold_at->format('d/m/Y H:i') }}</td>
            <td class="text-end">Rp {{ number_format($o->total, 0, ',', '.') }}</td>
            <td><span class="badge text-bg-{{ $o->status === 'completed' ? 'success' : 'secondary' }}">{{ $o->status }}</span></td>
        </tr>
    @empty
        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada pesanan.</td></tr>
    @endforelse
    </tbody></table></div></div>
<div class="mt-3">{{ $orders->links() }}</div>
@endsection
