<!doctype html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - MBACCO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Font opsional: dimuat tanpa memblokir halaman. Bila offline, otomatis memakai Georgia. --}}
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" media="print" onload="this.media='all'" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&display=swap">
    <link href="{{ asset('css/mbacco.css') }}" rel="stylesheet">
</head>
<body>
<div class="smoke" aria-hidden="true"><i></i><i></i><i></i></div>
@auth
@php $on = fn (...$r) => request()->routeIs(...$r) ? ' active' : ''; @endphp
<nav class="navbar navbar-expand-lg mb-nav" data-bs-theme="dark">
    <div class="container">
        <a class="navbar-brand" href="{{ route('dashboard') }}"><img class="brand-logo" src="{{ asset('images/mbacco-logo.png') }}" alt="">MBACCO <small>Inventory</small></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-label="Menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link{{ $on('dashboard') }}" href="{{ route('dashboard') }}">Dashboard</a></li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle{{ $on('sales.*', 'purchases.*') }}" href="#" data-bs-toggle="dropdown">Transaksi</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item{{ $on('sales.*') }}" href="{{ route('sales.index') }}">Penjualan</a></li>
                        <li><a class="dropdown-item{{ $on('purchases.*') }}" href="{{ route('purchases.index') }}">Pembelian</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle{{ $on('products.*', 'stock.*') }}" href="#" data-bs-toggle="dropdown">Stok</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item{{ $on('products.index', 'products.show', 'products.create', 'products.edit') }}" href="{{ route('products.index') }}">Produk</a></li>
                        <li><a class="dropdown-item{{ $on('products.low-stock') }}" href="{{ route('products.low-stock') }}">Stok Rendah</a></li>
                        @if (Route::has('stock.forecast'))
                            <li><a class="dropdown-item{{ $on('stock.forecast') }}" href="{{ route('stock.forecast') }}">Estimasi Stok</a></li>
                        @endif
                    </ul>
                </li>

                @can('admin')
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle{{ $on('categories.*', 'suppliers.*', 'users.*') }}" href="#" data-bs-toggle="dropdown">Data Master</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item{{ $on('categories.*') }}" href="{{ route('categories.index') }}">Kategori</a></li>
                            <li><a class="dropdown-item{{ $on('suppliers.*') }}" href="{{ route('suppliers.index') }}">Pemasok</a></li>
                            <li><a class="dropdown-item{{ $on('users.*') }}" href="{{ route('users.index') }}">Pengguna</a></li>
                        </ul>
                    </li>
                    @if (Route::has('reports.index'))
                        <li class="nav-item"><a class="nav-link{{ $on('reports.*') }}" href="{{ route('reports.index') }}">Laporan</a></li>
                    @endif
                @endcan
            </ul>

            <div class="dropdown">
                <button class="user-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <span class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>{{ auth()->user()->name }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">Masuk sebagai {{ auth()->user()->role }}</h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item">Keluar</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
@endauth

<main class="container py-4">
    @include('partials.flash')
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/mbacco.js') }}" defer></script>
</body>
</html>
