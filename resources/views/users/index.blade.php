@extends('layouts.app')
@section('title', 'Pengguna')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Pengguna &amp; Role</h1>
    <a href="{{ route('users.create') }}" class="btn btn-primary">+ Tambah</a>
</div>
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama/email..."></div>
    <div class="col-auto"><button class="btn btn-outline-secondary">Cari</button></div>
</form>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0 align-middle">
    <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th class="text-end">Aksi</th></tr></thead>
    <tbody>
    @foreach ($users as $u)
        <tr>
            <td>{{ $u->name }}</td><td>{{ $u->email }}</td>
            <td><span class="badge text-bg-{{ $u->isAdmin() ? 'primary' : 'secondary' }}">{{ $u->role }}</span></td>
            <td class="text-end">
                <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                @if ($u->id !== auth()->id())
                <form method="POST" action="{{ route('users.destroy', $u) }}" class="d-inline" onsubmit="return confirm('Hapus pengguna ini?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody></table></div></div>
<div class="mt-3">{{ $users->links() }}</div>
@endsection
