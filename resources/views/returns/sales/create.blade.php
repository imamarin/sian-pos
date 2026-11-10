@extends('layouts.app')

@section('title', 'Buat Retur Penjualan')

@section('content')
<div class="container-fluid px-0" style="max-width: 1050px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Formulir Retur & Tukar Barang Penjualan</h4>
            <p class="text-muted small mb-0">Pilih nota penjualan, tentukan kondisi barang yang dikembalikan (layak jual / rusak), dan pilih metode refund atau tukar barang</p>
        </div>
        <a href="{{ route('returns.sales.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <!-- Step 1: Select Sale Invoice -->
    <div class="card card-custom p-4 mb-4">
        <label class="form-label small fw-semibold">Pilih Nota Transaksi Penjualan yang Akan Diretur</label>
        <div class="d-flex gap-2">
            <select class="form-select" id="saleSelect" onchange="window.location.href='{{ route('returns.sales.create') }}?sale_id=' + this.value">
                <option value="">Pilih No. Invoice Penjualan...</option>
                @foreach($completedSales as $s)
                    <option value="{{ $s->id }}" {{ ($sale && $sale->id == $s->id) ? 'selected' : '' }}>
                        {{ $s->invoice_number }} - {{ $s->transaction_date->format('d/m/Y') }} - {{ $s->customer?->name ?? 'Umum' }} (Rp {{ number_format($s->grand_total, 0, ',', '.') }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    @if($sale)
        @php
            $customerLevel = strtolower($sale->customer?->member_level ?? 'regular');
        @endphp
        <form action="{{ route('returns.sales.store') }}" method="POST" id="saleReturnForm">
            @csrf
            <input type="hidden" name="sale_id" value="{{ $sale->id }}">

            <div class="card card-custom p-4 mb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Nota Penjualan Asal</small>
                        <div class="fw-bold fs-5 text-primary">{{ $sale->invoice_number }}</div>
                        <div class="small text-muted">
                            Pelanggan: <span class="fw-semibold text-dark">{{ $sale->customer?->name ?? 'Umum (Walk-in)' }}</span>
                            <span class="badge bg-light text-secondary border ms-1 text-uppercase" style="font-size: 0.65rem;">{{ $customerLevel }}</span>
                        </div>
                        @if($sale->remaining_debt > 0)
                            <div class="mt-2">
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    Sisa Hutang Nota: Rp {{ number_format($sale->remaining_debt, 0, ',', '.') }}
                                </span>
                                <div class="small text-danger mt-1">
                                    <i class="bi bi-info-circle me-1"></i> Nilai retur/selisih lebih akan otomatis memotong sisa hutang pelanggan terlebih dahulu.
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Metode Penyelesaian Retur <span class="text-danger">*</span></label>
                        <select name="refund_method" id="refundMethodSelect" class="form-select" required onchange="toggleExchangeSection()">
                            @if($sale->remaining_debt > 0)
                                <option value="credit" {{ old('refund_method') === 'credit' ? 'selected' : '' }}>Potong Piutang Pelanggan / Kredit Toko</option>
                                <option value="cash" {{ old('refund_method') === 'cash' ? 'selected' : '' }}>Potong Piutang + Lebihnya Tunai (Kas Laci)</option>
                                <option value="exchange" {{ in_array(old('refund_method'), ['exchange', 'none']) ? 'selected' : '' }}>Tukar Barang (Produk Sama / Beda Produk)</option>
                            @else
                                <option value="cash" {{ old('refund_method', 'cash') === 'cash' ? 'selected' : '' }}>Refund Tunai Langsung (Potong Kas Laci Kasir)</option>
                                <option value="exchange" {{ in_array(old('refund_method'), ['exchange', 'none']) ? 'selected' : '' }}>Tukar Barang (Produk Sama / Beda Produk)</option>
                                <option value="credit" {{ old('refund_method') === 'credit' ? 'selected' : '' }}>Kredit Toko / Saldo Member</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Catatan Dokumen Retur</label>
                        <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="Contoh: Retur barang rusak / tukar produk...">
                    </div>
                </div>
            </div>

            <!-- Items Table from Sale (Barang yang Dikembalikan Customer) -->
            <div class="card card-custom p-4 mb-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                    <div>
                        <h6 class="fw-bold mb-0"><i class="bi bi-box-arrow-in-down-left text-danger me-2"></i> 1. Pilih Barang yang Dikembalikan Pelanggan</h6>
                        <small class="text-muted">Tentukan jumlah retur dan kondisi fisik barang (Layak Jual masuk kembali ke stok, Barang Rusak tidak masuk ke stok layak jual)</small>
                    </div>
                    <div class="text-md-end">
                        <small class="text-muted d-block">Total Nilai Barang Diretur</small>
                        <span class="fw-bold fs-5 text-danger" id="totalReturnDisplay">Rp 0</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Produk</th>
                                <th class="text-center" style="width: 95px;">Qty Beli</th>
                                <th class="text-end" style="width: 135px;">Harga Satuan</th>
                                <th class="text-center" style="width: 120px;">Qty Retur</th>
                                <th style="width: 220px;">Kondisi Barang Kembali</th>
                                <th style="width: 210px;">Alasan Retur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->details as $index => $item)
                                @php
                                    $alreadyReturned = (int) \App\Models\SaleReturnDetail::whereHas('saleReturn', function ($q) use ($sale) {
                                        $q->where('sale_id', $sale->id);
                                    })->where('product_id', $item->product_id)->sum('qty');
                                    $maxReturnable = max(0, (int) $item->qty - $alreadyReturned);
                                @endphp
                                <tr class="return-item-row"
                                    data-product-id="{{ $item->product_id }}"
                                    data-product-name="{{ $item->product_name }}"
                                    data-price="{{ (float) $item->selling_price }}">
                                    <td>
                                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                        <div class="fw-bold text-dark">{{ $item->product_name }}</div>
                                        <small class="text-muted">SKU: {{ $item->product?->sku }}</small>
                                        @if($alreadyReturned > 0)
                                            <div class="small text-warning fw-semibold">Sudah diretur: {{ $alreadyReturned }} (Sisa: {{ $maxReturnable }})</div>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $item->qty }}</td>
                                    <td class="text-end">Rp {{ number_format($item->selling_price, 0, ',', '.') }}</td>
                                    <td>
                                        <input type="number"
                                               name="items[{{ $index }}][qty]"
                                               class="form-control form-control-sm text-center return-qty-input"
                                               min="0"
                                               max="{{ $maxReturnable }}"
                                               value="{{ old("items.{$index}.qty", 0) }}"
                                               {{ $maxReturnable <= 0 ? 'readonly' : '' }}
                                               oninput="recalculateExchangeSummary()">
                                    </td>
                                    <td>
                                        <select name="items[{{ $index }}][item_condition]" class="form-select form-select-sm condition-select" {{ $maxReturnable <= 0 ? 'disabled' : '' }}>
                                            <option value="good" {{ old("items.{$index}.item_condition", 'good') === 'good' ? 'selected' : '' }}>
                                                Layak Jual (Masuk Stok)
                                            </option>
                                            <option value="damaged" {{ old("items.{$index}.item_condition") === 'damaged' ? 'selected' : '' }}>
                                                Barang Rusak (Non-Stok)
                                            </option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text"
                                               name="items[{{ $index }}][reason]"
                                               class="form-control form-control-sm"
                                               value="{{ old("items.{$index}.reason") }}"
                                               placeholder="Kemasan rusak, salah beli, dll."
                                               {{ $maxReturnable <= 0 ? 'readonly' : '' }}>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Section 2: Replacement / Exchange Items (Tampil jika Metode = Tukar Barang) -->
            <div class="card card-custom p-4 mb-4 d-none border-primary" id="exchangeSectionCard">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-arrow-left-right me-2"></i> 2. Pilih Barang Pengganti yang Diambil Pelanggan (Tukar Barang)</h6>
                        <small class="text-muted">Stok barang pengganti di bawah ini akan otomatis dikurangi dari gudang/toko</small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" onclick="autoFillSameProducts()">
                            <i class="bi bi-magic me-1"></i> Samakan dgn Barang Diretur
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="addExchangeRow()">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Barang Pengganti
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-3">
                        <thead class="table-light small">
                            <tr>
                                <th>Produk Pengganti (Keluar dari Stok Toko)</th>
                                <th class="text-center" style="width: 110px;">Stok Tersedia</th>
                                <th class="text-center" style="width: 120px;">Qty Pengganti</th>
                                <th class="text-end" style="width: 160px;">Harga Satuan (Rp)</th>
                                <th class="text-end" style="width: 160px;">Subtotal</th>
                                <th class="text-center" style="width: 60px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="exchangeItemsBody">
                            <!-- Diisi otomatis via JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Ringkasan Selisih Tukar Barang -->
                <div class="p-3 rounded-3 bg-light border">
                    <div class="row g-3 align-items-center text-center text-md-start">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Total Nilai Barang Diretur</small>
                            <div class="fw-bold fs-6 text-danger" id="summaryReturnTotal">Rp 0</div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Total Nilai Barang Pengganti</small>
                            <div class="fw-bold fs-6 text-primary" id="summaryExchangeTotal">Rp 0</div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <small class="text-muted d-block" id="summaryBalanceLabel">Selisih Pertukaran</small>
                            <div class="fw-bold fs-5 text-secondary" id="summaryBalanceValue">Tukar Pas (Rp 0)</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-custom p-3">
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('returns.sales.index') }}" class="btn btn-light rounded-pill px-4">Batal</a>
                    <button type="submit" class="btn btn-primary-custom px-4 shadow-sm" onclick="return confirm('Konfirmasi proses retur penjualan ini?')">
                        <i class="bi bi-check2-circle me-1"></i> Simpan & Proses Retur
                    </button>
                </div>
            </div>
        </form>

        <template id="exchangeProductOptionsTemplate">
            <option value="">Pilih Produk Pengganti...</option>
            @foreach($products ?? [] as $prod)
                @php
                    $tierPrice = (float) $prod->getPriceForLevel($customerLevel);
                @endphp
                <option value="{{ $prod->id }}"
                        data-stock="{{ $prod->stock }}"
                        data-price="{{ $tierPrice }}"
                        data-unit="{{ $prod->unit?->short_name ?? 'pcs' }}">
                    {{ $prod->name }} (SKU: {{ $prod->sku }}) - Stok: {{ $prod->stock }} - Rp {{ number_format($tierPrice, 0, ',', '.') }}
                </option>
            @endforeach
        </template>

        @push('scripts')
        <script>
            let exchangeRowIndex = 0;

            function formatRupiah(num) {
                return 'Rp ' + Math.round(num).toLocaleString('id-ID');
            }

            function toggleExchangeSection() {
                const method = document.getElementById('refundMethodSelect').value;
                const card = document.getElementById('exchangeSectionCard');
                const isExchange = (method === 'exchange' || method === 'none');

                if (isExchange) {
                    card.classList.remove('d-none');
                    const tbody = document.getElementById('exchangeItemsBody');
                    if (tbody.querySelectorAll('tr').length === 0) {
                        autoFillSameProducts();
                    }
                } else {
                    card.classList.add('d-none');
                }

                recalculateExchangeSummary();
            }

            function addExchangeRow(productId = '', qty = 1, customPrice = null) {
                const tbody = document.getElementById('exchangeItemsBody');
                const optionsHtml = document.getElementById('exchangeProductOptionsTemplate').innerHTML;
                const idx = exchangeRowIndex++;

                const tr = document.createElement('tr');
                tr.className = 'exchange-row';
                tr.innerHTML = `
                    <td>
                        <select name="exchange_items[${idx}][product_id]" class="form-select form-select-sm ex-product-select" onchange="handleExchangeProductChange(this)">
                            ${optionsHtml}
                        </select>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border ex-stock-badge">0</span>
                    </td>
                    <td>
                        <input type="number" name="exchange_items[${idx}][qty]" class="form-control form-control-sm text-center ex-qty-input" min="1" value="${qty}" oninput="recalculateExchangeSummary()">
                    </td>
                    <td>
                        <input type="number" name="exchange_items[${idx}][unit_price]" class="form-control form-control-sm text-end ex-price-input" min="0" step="any" value="${customPrice !== null ? customPrice : 0}" oninput="recalculateExchangeSummary()">
                    </td>
                    <td class="text-end fw-bold ex-subtotal-text">Rp 0</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-light btn-sm text-danger border" onclick="removeExchangeRow(this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;

                tbody.appendChild(tr);

                if (productId) {
                    const select = tr.querySelector('.ex-product-select');
                    select.value = String(productId);
                    handleExchangeProductChange(select, customPrice);
                } else {
                    recalculateExchangeSummary();
                }
            }

            function removeExchangeRow(btn) {
                btn.closest('tr').remove();
                recalculateExchangeSummary();
            }

            function handleExchangeProductChange(select, overridePrice = null) {
                const tr = select.closest('tr');
                const opt = select.options[select.selectedIndex];
                const stock = opt ? (opt.getAttribute('data-stock') || 0) : 0;
                const defaultPrice = opt ? parseFloat(opt.getAttribute('data-price') || 0) : 0;

                tr.querySelector('.ex-stock-badge').innerText = stock;
                const priceInput = tr.querySelector('.ex-price-input');
                priceInput.value = overridePrice !== null ? overridePrice : defaultPrice;

                recalculateExchangeSummary();
            }

            function autoFillSameProducts() {
                const tbody = document.getElementById('exchangeItemsBody');
                tbody.innerHTML = '';

                let added = 0;
                document.querySelectorAll('.return-item-row').forEach(row => {
                    const qty = parseInt(row.querySelector('.return-qty-input')?.value || 0);
                    if (qty > 0) {
                        const pid = row.getAttribute('data-product-id');
                        const price = parseFloat(row.getAttribute('data-price') || 0);
                        addExchangeRow(pid, qty, price);
                        added++;
                    }
                });

                if (added === 0) {
                    addExchangeRow('', 1, 0);
                }

                recalculateExchangeSummary();
            }

            function recalculateExchangeSummary() {
                let returnTotal = 0;
                document.querySelectorAll('.return-item-row').forEach(row => {
                    const qty = parseInt(row.querySelector('.return-qty-input')?.value || 0);
                    const price = parseFloat(row.getAttribute('data-price') || 0);
                    if (qty > 0) {
                        returnTotal += (qty * price);
                    }
                });

                document.getElementById('totalReturnDisplay').innerText = formatRupiah(returnTotal);
                document.getElementById('summaryReturnTotal').innerText = formatRupiah(returnTotal);

                let exchangeTotal = 0;
                document.querySelectorAll('.exchange-row').forEach(row => {
                    const pid = row.querySelector('.ex-product-select')?.value;
                    const qty = parseInt(row.querySelector('.ex-qty-input')?.value || 0);
                    const price = parseFloat(row.querySelector('.ex-price-input')?.value || 0);
                    const sub = (pid && qty > 0) ? (qty * price) : 0;
                    exchangeTotal += sub;
                    row.querySelector('.ex-subtotal-text').innerText = formatRupiah(sub);
                });

                document.getElementById('summaryExchangeTotal').innerText = formatRupiah(exchangeTotal);

                const balance = exchangeTotal - returnTotal;
                const labelEl = document.getElementById('summaryBalanceLabel');
                const valEl = document.getElementById('summaryBalanceValue');

                if (balance > 0) {
                    labelEl.innerText = 'Pelanggan Tambah Bayar (Kas Masuk)';
                    valEl.innerText = '+' + formatRupiah(balance);
                    valEl.className = 'fw-bold fs-5 text-success';
                } else if (balance < 0) {
                    labelEl.innerText = 'Kembalikan Selisih ke Pelanggan';
                    valEl.innerText = '-' + formatRupiah(Math.abs(balance));
                    valEl.className = 'fw-bold fs-5 text-danger';
                } else {
                    labelEl.innerText = 'Selisih Pertukaran';
                    valEl.innerText = 'Tukar Pas (Rp 0)';
                    valEl.className = 'fw-bold fs-5 text-secondary';
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                toggleExchangeSection();
                recalculateExchangeSummary();
            });
        </script>
        @endpush
    @else
        <div class="card card-custom p-5 text-center text-muted">
            <i class="bi bi-receipt fs-1 text-secondary mb-2"></i>
            Silakan pilih nota penjualan di atas untuk memuat daftar barang.
        </div>
    @endif
</div>
@endsection
