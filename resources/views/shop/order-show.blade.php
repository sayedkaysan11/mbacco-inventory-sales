@extends('layouts.app')
@section('title', $sale->sale_number)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ $sale->sale_number }}
        <span class="badge text-bg-{{ $sale->status === 'completed' ? 'success' : 'secondary' }} fs-6">{{ $sale->status }}</span></h1>
    <a href="{{ route('shop.orders.index') }}" class="btn btn-outline-secondary">&larr; Kembali</a>
</div>
<div class="card mb-3"><div class="card-body">
    <dl class="row mb-0">
        <dt class="col-sm-3">Waktu</dt><dd class="col-sm-9">{{ $sale->sold_at->format('d/m/Y H:i') }}</dd>
    </dl>
</div></div>
<div class="card"><div class="table-responsive"><table class="table mb-0">
    <thead><tr><th>Produk</th><th class="text-end">Qty</th><th class="text-end">Harga</th><th class="text-end">Subtotal</th></tr></thead>
    <tbody>
    @foreach ($sale->items as $i)
        <tr><td>{{ $i->product->name }}</td><td class="text-end">{{ $i->quantity }}</td>
            <td class="text-end">Rp {{ number_format($i->unit_price, 0, ',', '.') }}</td>
            <td class="text-end">Rp {{ number_format($i->subtotal, 0, ',', '.') }}</td></tr>
    @endforeach
    </tbody>
    <tfoot><tr><th colspan="3" class="text-end">Total</th><th class="text-end">Rp {{ number_format($sale->total, 0, ',', '.') }}</th></tr></tfoot>
</table></div></div>
@endsection
