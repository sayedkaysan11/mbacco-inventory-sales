{{-- Filter bersama untuk daftar pembelian & penjualan --}}
<form method="GET" action="{{ route($route) }}" class="row g-2 mb-3">
    <div class="col-md-3"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ $placeholder }}"></div>
    <div class="col-md-2">
        <select name="status" class="form-select">
            <option value="">Semua status</option>
            <option value="completed" @selected(request('status') === 'completed')>completed</option>
            <option value="cancelled" @selected(request('status') === 'cancelled')>cancelled</option>
        </select>
    </div>
    <div class="col-md-2"><input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" title="Dari tanggal"></div>
    <div class="col-md-2"><input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" title="Sampai tanggal"></div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary">Filter</button>
        <a href="{{ route($route) }}" class="btn btn-link">Reset</a>
    </div>
</form>
