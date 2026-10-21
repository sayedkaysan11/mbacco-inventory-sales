@extends('layouts.app')
@section('title', 'Pembelian Baru')
@section('content')
<h1 class="h3 mb-3">Pembelian / Stock-in Baru</h1>
<form method="POST" action="{{ route('purchases.store') }}">
    @csrf
    <div class="card mb-3"><div class="card-body">
        <label class="form-label">Pemasok</label>
        <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
            <option value="">-- pilih pemasok --</option>
            @foreach ($suppliers as $s)
                <option value="{{ $s->id }}" @selected(old('supplier_id') == $s->id)>{{ $s->name }}</option>
            @endforeach
        </select>
    </div></div>
    <div class="card mb-3"><div class="card-body">
        @include('transactions._items', ['options' => $options, 'mode' => 'purchase'])
    </div></div>
    <button class="btn btn-primary">Simpan Pembelian</button>
    <a href="{{ route('purchases.index') }}" class="btn btn-link">Batal</a>
</form>
@endsection
