@extends('layouts.app')
@section('title', $category->exists ? 'Ubah Kategori' : 'Tambah Kategori')
@section('content')
<h1 class="h3 mb-3">{{ $category->exists ? 'Ubah' : 'Tambah' }} Kategori</h1>
<div class="card"><div class="card-body">
<form method="POST" action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}">
    @csrf
    @if ($category->exists) @method('PUT') @endif
    <div class="mb-3">
        <label class="form-label">Nama kategori</label>
        <input type="text" name="name" value="{{ old('name', $category->name) }}" class="form-control @error('name') is-invalid @enderror" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <button class="btn btn-primary">Simpan</button>
    <a href="{{ route('categories.index') }}" class="btn btn-link">Batal</a>
</form>
</div></div>
@endsection
