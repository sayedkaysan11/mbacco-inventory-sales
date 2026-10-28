@extends('layouts.app')
@section('title', 'Masuk')
@section('content')
<div class="row login-wrap g-4">
    <div class="col-lg-7 brand-panel">
        <img class="login-logo" src="{{ asset('images/mbacco-logo.png') }}" alt="MBACCO, Matang Tobacco">
        <h1 class="visually-hidden">MBACCO</h1>
        <p class="tagline">Stok tembakau, pembelian, dan penjualan toko Anda dalam satu catatan yang rapi.</p>
    </div>
    <div class="col-lg-5">
        <div class="card login-card">
            <div class="card-body p-4 p-lg-5">
                <h2 class="h4 mb-4">Masuk ke akun Anda</h2>
                <form method="POST" action="{{ route('login.attempt') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control form-control-lg @error('email') is-invalid @enderror" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control form-control-lg" required>
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">Ingat saya</label>
                    </div>
                    <button class="btn btn-primary btn-lg w-100">Masuk</button>
                </form><p class="mt-3 mb-0 text-center">Belum punya akun? <a href="{{ route('register') }}">Daftar sebagai pembeli</a></p>
                <small class="text-muted">Akun demo: admin@example.com atau staff@example.com, password <code>password123</code></small>
            </div>
        </div>
    </div>
</div>
@endsection
