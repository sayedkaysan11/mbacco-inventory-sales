@extends('layouts.app')
@section('title', $purchase->purchase_number)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ $purchase->purchase_number }}
        <span class="badge text-bg-{{ $purchase->status === 'completed' ? 'success' : 'secondary' }} fs-6">{{ $purchase->status }}</span></h1>
    <div>
        @can('admin')
            @if ($purchase->status === 'completed')
            <form method="POST" action="{{ route('purchases.cancel', $purchase) }}" class="d-inline" onsubmit="return confirm('Batalkan pembelian ini? Stok akan dikurangi kembali.')">
                @csrf <button class="btn btn-outline-danger">Batalkan</button>
            </form>
            @endif
        @endcan
        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">&larr; Kembali</a>
    </div>
</div>
<div class="card mb-3"><div class="card-body">
    <dl class="row mb-0">
        <dt class="col-sm-3">Pemasok</dt><dd class="col-sm-9">{{ $purchase->supplier->name }}</dd>
        <dt class="col-sm-3">Petugas</dt><dd class="col-sm-9">{{ $purchase->user->name }}</dd>
        <dt class="col-sm-3">Waktu</dt><dd class="col-sm-9">{{ $purchase->purchased_at->format('d/m/Y H:i') }}</dd>
    </dl>
</div></div>
<div class="card"><div class="table-responsive"><table class="table mb-0">
    <thead><tr><th>Produk</th><th class="text-end">Qty</th><th class="text-end">Harga beli</th><th class="text-end">Subtotal</th></tr></thead>
    <tbody>
    @foreach ($purchase->items as $i)
        <tr><td>{{ $i->product->name }}</td><td class="text-end">{{ $i->quantity }}</td>
            <td class="text-end">Rp {{ number_format($i->unit_cost, 0, ',', '.') }}</td>
            <td class="text-end">Rp {{ number_format($i->subtotal, 0, ',', '.') }}</td></tr>
    @endforeach
    </tbody>
    <tfoot><tr><th colspan="3" class="text-end">Total</th><th class="text-end">Rp {{ number_format($purchase->total, 0, ',', '.') }}</th></tr></tfoot>
</table></div></div>
@endsection
