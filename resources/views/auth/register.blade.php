@extends('layouts.app')
@section('title', 'Daftar Pembeli')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3">MBACCO – Daftar Pembeli</h1>
                <form method="POST" action="{{ route('register.attempt') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="name">Nama</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Password (min. 8 karakter)</label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password_confirmation">Konfirmasi password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                    </div>
                    <button class="btn btn-primary w-100">Daftar</button>
                </form>
                <hr>
                <div>Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></div>
            </div>
        </div>
    </div>
</div>
@endsection
