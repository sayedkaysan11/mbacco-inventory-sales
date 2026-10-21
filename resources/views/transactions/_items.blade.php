{{-- Editor baris item dinamis. Variabel: $options (list produk), $mode ('sale' | 'purchase') --}}
<div class="table-responsive">
<table class="table table-sm align-middle" id="items-table">
    <thead>
        <tr>
            <th style="min-width:280px">Produk</th>
            <th style="width:110px">Qty</th>
            <th style="width:170px">{{ $mode === 'purchase' ? 'Harga beli' : 'Harga jual' }}</th>
            <th style="width:170px" class="text-end">Subtotal</th>
            <th style="width:50px"></th>
        </tr>
    </thead>
    <tbody></tbody>
    <tfoot>
        <tr><th colspan="3" class="text-end">Total (estimasi)</th><th class="text-end" id="grand-total">Rp 0</th><th></th></tr>
    </tfoot>
</table>
</div>
<button type="button" id="add-row" class="btn btn-outline-secondary btn-sm">+ Tambah item</button>
<div class="form-text">Total akhir dan harga dihitung ulang oleh server saat disimpan.</div>

<script>
(function () {
    const options = @json($options);
    const mode = @json($mode);
    const initialRows = @json(old('items', [['product_id' => '', 'quantity' => 1]]));
    const tbody = document.querySelector('#items-table tbody');
    const totalEl = document.getElementById('grand-total');
    const rupiah = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
    let idx = 0;

    function recalc() {
        let total = 0;
        tbody.querySelectorAll('tr').forEach(tr => {
            const qty = parseFloat(tr.querySelector('.qty').value) || 0;
            const price = parseFloat(tr.dataset.price) || 0;
            const sub = qty * price;
            tr.querySelector('.subtotal').textContent = rupiah(sub);
            total += sub;
        });
        totalEl.textContent = rupiah(total);
    }

    function addRow(data) {
        data = data || {};
        const i = idx++;
        const tr = document.createElement('tr');

        const select = document.createElement('select');
        select.name = `items[${i}][product_id]`;
        select.className = 'form-select form-select-sm';
        select.required = true;
        const blank = document.createElement('option');
        blank.value = ''; blank.textContent = '-- pilih produk --';
        select.appendChild(blank);
        options.forEach(o => {
            const opt = document.createElement('option');
            opt.value = o.id; opt.textContent = o.label; // textContent => aman dari XSS
            if (String(o.id) === String(data.product_id)) opt.selected = true;
            select.appendChild(opt);
        });

        const qty = document.createElement('input');
        qty.type = 'number'; qty.min = 1; qty.step = 1; qty.required = true;
        qty.name = `items[${i}][quantity]`; qty.className = 'form-control form-control-sm qty';
        qty.value = data.quantity || 1;

        const priceCell = document.createElement('td');
        let priceInput = null;
        if (mode === 'purchase') {
            priceInput = document.createElement('input');
            priceInput.type = 'number'; priceInput.min = 0; priceInput.step = '0.01'; priceInput.required = true;
            priceInput.name = `items[${i}][unit_cost]`; priceInput.className = 'form-control form-control-sm';
            if (data.unit_cost !== undefined) priceInput.value = data.unit_cost;
            priceCell.appendChild(priceInput);
        } else {
            priceCell.className = 'price-label';
        }

        const setPrice = () => {
            const o = options.find(x => String(x.id) === String(select.value));
            if (mode === 'purchase') {
                if (o && priceInput.value === '') priceInput.value = o.price;
                tr.dataset.price = priceInput.value || 0;
            } else {
                tr.dataset.price = o ? o.price : 0;
                priceCell.textContent = o ? rupiah(o.price) : '-';
            }
            recalc();
        };

        select.addEventListener('change', setPrice);
        qty.addEventListener('input', recalc);
        if (priceInput) priceInput.addEventListener('input', setPrice);

        const td = (child, cls) => { const c = document.createElement('td'); if (cls) c.className = cls; if (child) c.appendChild(child); return c; };
        const sub = td(null, 'text-end subtotal'); sub.textContent = 'Rp 0';
        const del = document.createElement('button');
        del.type = 'button'; del.className = 'btn btn-sm btn-outline-danger'; del.textContent = '×';
        del.addEventListener('click', () => { if (tbody.children.length > 1) { tr.remove(); recalc(); } });

        tr.append(td(select), td(qty), priceCell, sub, td(del));
        tbody.appendChild(tr);
        setPrice();
    }

    document.getElementById('add-row').addEventListener('click', () => addRow());
    (Array.isArray(initialRows) ? initialRows : Object.values(initialRows)).forEach(addRow);
})();
</script>
