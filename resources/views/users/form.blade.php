@extends('layouts.app')
@section('title', $user->exists ? 'Ubah Pengguna' : 'Tambah Pengguna')
@section('content')
<h1 class="h3 mb-3">{{ $user->exists ? 'Ubah' : 'Tambah' }} Pengguna</h1>
<div class="card"><div class="card-body">
<form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}">
    @csrf
    @if ($user->exists) @method('PUT') @endif
    <div class="mb-3">
        <label class="form-label">Nama</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Role</label>
        <select name="role" class="form-select">
            @foreach (['admin' => 'Admin / Owner', 'staff' => 'Staff'] as $val => $label)
                <option value="{{ $val }}" @selected(old('role', $user->role ?? 'staff') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        @if ($user->exists && $user->id === auth()->id())
            <div class="form-text">Perubahan role pada akun Anda sendiri diabaikan oleh sistem.</div>
        @endif
    </div>
    <div class="mb-3">
        <label class="form-label">Password {{ $user->exists ? '(kosongkan jika tidak diubah)' : '' }}</label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" {{ $user->exists ? '' : 'required' }}>
    </div>
    <div class="mb-3">
        <label class="form-label">Konfirmasi password</label>
        <input type="password" name="password_confirmation" class="form-control">
    </div>
    <button class="btn btn-primary">Simpan</button>
    <a href="{{ route('users.index') }}" class="btn btn-link">Batal</a>
</form>
</div></div>
@endsection
