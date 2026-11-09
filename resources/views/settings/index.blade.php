@extends('layouts.app')

@section('title', 'Pengaturan Toko & Sistem')

@section('content')
<div class="container-fluid px-0" style="max-width: 900px;">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Pengaturan Toko & Preferensi Sistem</h4>
        <p class="text-muted small mb-0">Sesuaikan profil toko, format nota struk kasir, persentase pajak, dan konfigurasi POS</p>
    </div>

    <div class="card card-custom p-4">
        <form action="{{ route('settings.update') }}" method="POST">
            @csrf

            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-shop me-2"></i> Identitas & Profil Toko</h6>
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold">Nama Toko / Bisnis <span class="text-danger">*</span></label>
                    <input type="text" name="store_name" class="form-control" value="{{ old('store_name', $settings['store_name']) }}" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold">Tagline / Slogan Toko</label>
                    <input type="text" name="store_tagline" class="form-control" value="{{ old('store_tagline', $settings['store_tagline']) }}">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Alamat Lengkap Toko</label>
                    <textarea name="store_address" class="form-control" rows="2">{{ old('store_address', $settings['store_address']) }}</textarea>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="store_phone" class="form-control" value="{{ old('store_phone', $settings['store_phone']) }}">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold">Alamat Email Toko</label>
                    <input type="email" name="store_email" class="form-control" value="{{ old('store_email', $settings['store_email']) }}">
                </div>
            </div>

            <hr class="my-4">

            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-receipt-cutoff me-2"></i> Pengaturan Faktur & Struk Thermal</h6>
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold">Awalan No. Invoice Penjualan <span class="text-danger">*</span></label>
                    <input type="text" name="invoice_prefix" class="form-control font-monospace" value="{{ old('invoice_prefix', $settings['invoice_prefix']) }}" required>
                    <small class="text-muted" style="font-size: 0.75rem;">Contoh hasil: INV-20261013-0001</small>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold">Awalan No. Faktur Pembelian (PO) <span class="text-danger">*</span></label>
                    <input type="text" name="purchase_prefix" class="form-control font-monospace" value="{{ old('purchase_prefix', $settings['purchase_prefix']) }}" required>
                    <small class="text-muted" style="font-size: 0.75rem;">Contoh hasil: PO-20261013-0001</small>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold">Default Pajak PPN (%) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.1" min="0" max="100" name="tax_percentage" class="form-control text-end" value="{{ old('tax_percentage', $settings['tax_percentage']) }}" required>
                        <span class="input-group-text bg-light">%</span>
                    </div>
                    <small class="text-muted" style="font-size: 0.75rem;">Set 0 jika toko tidak mengenakan PPN</small>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Pesan Footer Struk Kasir</label>
                    <textarea name="receipt_footer" class="form-control font-monospace" rows="3">{{ old('receipt_footer', $settings['receipt_footer']) }}</textarea>
                    <small class="text-muted" style="font-size: 0.75rem;">Akan dicetak di bagian paling bawah struk thermal kasir</small>
                </div>
            </div>

            <hr class="my-4" id="profit-allocation-settings">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                <div>
                    <h6 class="fw-bold text-primary mb-1"><i class="bi bi-pie-chart-fill me-2"></i> Pengaturan Persentase Alokasi Laba Kotor (100%)</h6>
                    <small class="text-muted">Atur nama pos dan persentase pembagian Laba Kotor pada Laporan Laba Rugi & Laporan Alokasi Laba</small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="resetDefaultAllocationItems()">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Default
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="addAllocationRow()">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Pos
                    </button>
                </div>
            </div>

            @error('allocation_items')
                <div class="alert alert-danger py-2 small mb-3">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $message }}
                </div>
            @enderror

            <div class="table-responsive mb-3">
                <table class="table table-bordered align-middle mb-0" id="allocationConfigTable">
                    <thead class="table-light small">
                        <tr>
                            <th style="width: 55%;">Nama Pos Alokasi</th>
                            <th class="text-end" style="width: 30%;">Persentase (%)</th>
                            <th class="text-center" style="width: 15%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="allocationConfigBody">
                        @php
                            $itemsToRender = old('allocation_items', $allocationItems ?? \App\Services\ReportService::defaultProfitAllocationItems());
                        @endphp
                        @foreach($itemsToRender as $idx => $item)
                            <tr class="allocation-config-row">
                                <td>
                                    <input type="hidden" name="allocation_items[{{ $idx }}][key]" value="{{ $item['key'] ?? '' }}" class="alloc-key">
                                    <input type="text"
                                           name="allocation_items[{{ $idx }}][label]"
                                           class="form-control form-control-sm fw-semibold text-uppercase alloc-label"
                                           value="{{ $item['label'] ?? $item['name'] ?? '' }}"
                                           placeholder="Contoh: SDM"
                                           required>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               max="100"
                                               name="allocation_items[{{ $idx }}][percentage]"
                                               class="form-control form-control-sm text-end fw-bold alloc-pct"
                                               value="{{ rtrim(rtrim(number_format((float) ($item['percentage'] ?? 0), 2, '.', ''), '0'), '.') }}"
                                               oninput="recalculateAllocationSettingTotal()"
                                               required>
                                        <span class="input-group-text bg-light">%</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeAllocationRow(this)" title="Hapus Pos">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td class="fw-bold text-end">Total Persentase Alokasi (Wajib 100%):</td>
                            <td class="text-end">
                                <span id="allocationTotalPctBadge" class="badge bg-success fs-6">100%</span>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <button type="submit" class="btn btn-primary-custom px-4 shadow-sm" id="btnSaveSettings">
                    <i class="bi bi-check2-circle me-1"></i> Simpan Seluruh Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const defaultAllocationPreset = [
        { key: 'sdm', label: 'SDM', percentage: 30 },
        { key: 'laba_bersih', label: 'LABA BERSIH', percentage: 30 },
        { key: 'manajemen_resiko', label: 'MANAJEMEN RESIKO', percentage: 10 },
        { key: 'operasional_gudang', label: 'OPERASIONAL GUDANG', percentage: 5 },
        { key: 'transportasi', label: 'TRANSPORTASI', percentage: 5 },
        { key: 'marketing', label: 'MARKETING', percentage: 8 },
        { key: 'adm', label: 'ADM', percentage: 2 },
        { key: 'peralatan', label: 'PERALATAN', percentage: 7 },
        { key: 'pajak', label: 'PAJAK', percentage: 3 },
    ];

    function reindexAllocationRows() {
        const rows = document.querySelectorAll('#allocationConfigBody tr.allocation-config-row');
        rows.forEach((row, idx) => {
            const keyInp = row.querySelector('.alloc-key');
            const labelInp = row.querySelector('.alloc-label');
            const pctInp = row.querySelector('.alloc-pct');
            if (keyInp) keyInp.name = `allocation_items[${idx}][key]`;
            if (labelInp) labelInp.name = `allocation_items[${idx}][label]`;
            if (pctInp) pctInp.name = `allocation_items[${idx}][percentage]`;
        });
    }

    function recalculateAllocationSettingTotal() {
        let total = 0;
        document.querySelectorAll('#allocationConfigBody .alloc-pct').forEach(inp => {
            total += parseFloat(inp.value || '0') || 0;
        });

        const rounded = Math.round(total * 100) / 100;
        const badge = document.getElementById('allocationTotalPctBadge');
        const btnSave = document.getElementById('btnSaveSettings');

        if (badge) {
            badge.innerText = rounded + '%';
            if (rounded === 100) {
                badge.className = 'badge bg-success fs-6';
            } else {
                badge.className = 'badge bg-danger fs-6';
            }
        }

        if (btnSave) {
            btnSave.disabled = (rounded !== 100);
        }
    }

    function addAllocationRow(key = '', label = '', percentage = 0) {
        const tbody = document.getElementById('allocationConfigBody');
        const idx = tbody.querySelectorAll('tr.allocation-config-row').length;
        const tr = document.createElement('tr');
        tr.className = 'allocation-config-row';
        tr.innerHTML = `
            <td>
                <input type="hidden" name="allocation_items[${idx}][key]" value="${key}" class="alloc-key">
                <input type="text"
                       name="allocation_items[${idx}][label]"
                       class="form-control form-control-sm fw-semibold text-uppercase alloc-label"
                       value="${label}"
                       placeholder="Nama Pos Alokasi"
                       required>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number"
                           step="0.01"
                           min="0"
                           max="100"
                           name="allocation_items[${idx}][percentage]"
                           class="form-control form-control-sm text-end fw-bold alloc-pct"
                           value="${percentage}"
                           oninput="recalculateAllocationSettingTotal()"
                           required>
                    <span class="input-group-text bg-light">%</span>
                </div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeAllocationRow(this)" title="Hapus Pos">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        recalculateAllocationSettingTotal();
    }

    function removeAllocationRow(btn) {
        const tbody = document.getElementById('allocationConfigBody');
        if (tbody.querySelectorAll('tr.allocation-config-row').length <= 1) {
            alert('Minimal harus terdapat 1 pos alokasi laba.');
            return;
        }
        btn.closest('tr').remove();
        reindexAllocationRows();
        recalculateAllocationSettingTotal();
    }

    function resetDefaultAllocationItems() {
        const tbody = document.getElementById('allocationConfigBody');
        tbody.innerHTML = '';
        defaultAllocationPreset.forEach(item => {
            addAllocationRow(item.key, item.label, item.percentage);
        });
        reindexAllocationRows();
        recalculateAllocationSettingTotal();
    }

    document.addEventListener('DOMContentLoaded', () => {
        recalculateAllocationSettingTotal();
    });
</script>
@endpush
@endsection
