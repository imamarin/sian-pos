@extends('layouts.app')

@section('title', 'Laporan Laba Rugi')

@section('content')
<style>
    .table-allocation {
        border-collapse: collapse;
        width: 100%;
    }
    .table-allocation th {
        background-color: #3b7ddd !important;
        color: #ffffff !important;
        font-weight: 700;
        text-transform: uppercase;
        border: 1px solid #1e293b !important;
        padding: 10px 12px !important;
    }
    .table-allocation td {
        border: 1px solid #334155 !important;
        padding: 8px 12px !important;
        color: #0f172a;
        font-size: 0.9rem;
    }
    .table-allocation tfoot tr.allocation-footer-row td {
        background-color: #3b7ddd !important;
        color: #0f172a !important;
        font-weight: 800;
        font-size: 0.95rem;
        border: 1px solid #1e293b !important;
    }
    .credit-print-text {
        display: none;
    }
    @media print {
        form,
        form.printable-form,
        form#plAllocationCreditsForm {
            display: block !important;
        }
        .credit-print-text {
            display: inline !important;
        }
        @page {
            size: A4 portrait;
            margin: 12mm 10mm;
        }
        body {
            font-size: 9pt !important;
            color: #111 !important;
            background: #fff !important;
        }
        .container-fluid {
            max-width: 100% !important;
            padding: 0 !important;
        }
        .card, .card-custom {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin-bottom: 12px !important;
            background: transparent !important;
        }
        table {
            page-break-inside: auto;
        }
        tr {
            page-break-inside: avoid !important;
        }
        table.table-allocation {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-top: 6px !important;
            margin-bottom: 15px !important;
            page-break-inside: avoid !important;
        }
        table.table-allocation th {
            background-color: #3b7ddd !important;
            color: #ffffff !important;
            font-weight: bold !important;
            text-transform: uppercase !important;
            border: 1px solid #1e293b !important;
            padding: 6px 8px !important;
            font-size: 9pt !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        table.table-allocation td {
            border: 1px solid #334155 !important;
            padding: 5px 8px !important;
            font-size: 8.5pt !important;
            color: #0f172a !important;
        }
        table.table-allocation tfoot tr.allocation-footer-row td {
            background-color: #3b7ddd !important;
            color: #000000 !important;
            font-weight: 800 !important;
            font-size: 9pt !important;
            border: 1px solid #1e293b !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .signature-table, table.table-hover {
            page-break-inside: avoid !important;
        }
    }
</style>

<div class="container-fluid px-0" style="max-width: 980px;">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 d-print-none">
        <div>
            <h4 class="fw-bold mb-1">Laporan Laba Rugi (Profit & Loss)</h4>
            <p class="text-muted small mb-0">Analisis pendapatan penjualan, HPP, beban operasional & penyusutan, Laba Kotor, serta Alokasi Laba</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('settings.index') }}#profit-allocation-settings" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm d-flex align-items-center gap-1">
                <i class="bi bi-sliders"></i> Atur % Alokasi
            </a>
            <a href="{{ route('reports.profit-loss.pdf', request()->all()) }}" class="btn btn-outline-danger btn-sm rounded-pill px-3 shadow-sm d-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-pdf"></i> Ekspor PDF
            </a>
            <button onclick="window.print()" class="btn btn-light border btn-sm rounded-pill px-3 shadow-sm d-flex align-items-center gap-1">
                <i class="bi bi-printer me-1"></i> Print Laporan
            </button>
        </div>
    </div>

    <!-- Kop Header Toko (Khusus Tampilan Cetak / Print) -->
    <div class="d-none d-print-block mb-3 border-bottom pb-2">
        <table style="width: 100%; border: none; margin-bottom: 5px;">
            <tr style="border: none;">
                <td style="border: none; padding: 0;">
                    <h3 style="margin: 0; font-weight: bold; text-transform: uppercase;">{{ \App\Models\Setting::get('store_name', 'Kasir POS') }}</h3>
                    <div style="font-size: 9pt; color: #555;">{{ \App\Models\Setting::get('store_address', 'Jl. Toko') }} &bull; Telp: {{ \App\Models\Setting::get('store_phone', '-') }}</div>
                </td>
                <td style="border: none; padding: 0; text-align: right; font-size: 8.5pt; color: #555;">
                    Dicetak: {{ now()->format('d/m/Y H:i') }}<br>
                    Petugas: {{ auth()->user()->name }}
                </td>
            </tr>
        </table>
        <div style="text-align: center; margin-top: 8px;">
            <h4 style="margin: 0; font-weight: bold; text-decoration: underline; text-transform: uppercase;">LAPORAN LABA RUGI (PROFIT & LOSS)</h4>
            <div style="font-size: 9pt; color: #555; margin-top: 3px;">
                Periode: <strong>{{ $start->format('d/m/Y') }}</strong> s/d <strong>{{ $end->format('d/m/Y') }}</strong>
            </div>
        </div>
    </div>

    <!-- Filter Card (Hanya di Layar Web) -->
    <div class="card card-custom p-3 mb-4 d-print-none">
        <form method="GET" action="{{ route('reports.profit-loss') }}" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted">Preset Periode</label>
                <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="yesterday" {{ $period === 'yesterday' ? 'selected' : '' }}>Kemarin</option>
                    <option value="this_week" {{ $period === 'this_week' ? 'selected' : '' }}>Minggu Ini</option>
                    <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="this_year" {{ $period === 'this_year' ? 'selected' : '' }}>Tahun Ini</option>
                    <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Kustom</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted">Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $start->format('Y-m-d') }}">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted">Tanggal Selesai</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $end->format('Y-m-d') }}">
            </div>
            <div class="col-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary-custom btn-sm w-100">Filter</button>
                <a href="{{ route('reports.profit-loss') }}" class="btn btn-light border btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>

    <!-- Income Statement Table -->
    <div class="card card-custom p-4 mb-4">
        <h5 class="fw-bold mb-4 text-dark d-print-none"><i class="bi bi-cash-stack text-primary me-2"></i> Laporan Laba Rugi Komprehensif</h5>

        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Komponen Keuangan</th>
                        <th class="text-end" style="width: 250px;">Nominal (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- 1. Pendapatan -->
                    <tr class="table-light fw-bold">
                        <td colspan="2">1. PENDAPATAN OPERASIONAL</td>
                    </tr>
                    <tr>
                        <td class="ps-4">Subtotal Penjualan Kotor (Gross Sales)</td>
                        <td class="text-end fw-semibold">Rp {{ number_format($profitLoss['total_subtotal'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="ps-4 text-muted">Potongan Diskon Penjualan (-)</td>
                        <td class="text-end text-danger">- Rp {{ number_format($profitLoss['total_discounts'], 0, ',', '.') }}</td>
                    </tr>
                    @if(($profitLoss['total_returns'] ?? 0) > 0)
                    <tr>
                        <td class="ps-4 text-muted">Potongan Retur Penjualan (-)</td>
                        <td class="text-end text-danger">- Rp {{ number_format($profitLoss['total_returns'], 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr class="fw-bold">
                        <td class="ps-4">Total Pendapatan Bersih Penjualan Produk</td>
                        <td class="text-end text-dark">Rp {{ number_format($profitLoss['total_revenue'], 0, ',', '.') }}</td>
                    </tr>
                    @if(($profitLoss['total_shipping'] ?? 0) > 0)
                    <tr class="text-muted small">
                        <td class="ps-4 fst-italic"><i class="bi bi-truck me-1"></i> Total Ongkos Kirim Terdata (Terpisah dari Penjualan Produk)</td>
                        <td class="text-end fst-italic">Rp {{ number_format($profitLoss['total_shipping'], 0, ',', '.') }}</td>
                    </tr>
                    @endif

                    <!-- 2. HPP -->
                    <tr class="table-light fw-bold">
                        <td colspan="2">2. HARGA POKOK PENJUALAN (HPP / COGS)</td>
                    </tr>
                    <tr>
                        <td class="ps-4 text-muted">Total Modal Barang Terjual (Cost of Goods Sold) (-)</td>
                        <td class="text-end text-danger">- Rp {{ number_format($profitLoss['total_cogs'], 0, ',', '.') }}</td>
                    </tr>

                    <!-- 3. Beban Operasional & Penyusutan (Sebelum Laba Kotor) -->
                    <tr class="table-light fw-bold">
                        <td colspan="2">3. BEBAN OPERASIONAL & PENYUSUTAN</td>
                    </tr>
                    <tr>
                        <td class="ps-4 text-muted">Beban Operasional & Pengeluaran Kasir (-)</td>
                        <td class="text-end text-danger">- Rp {{ number_format($profitLoss['expenses'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="ps-4 text-muted">
                            Total Penyusutan & Kerugian Stok (Opname / Rusak / Hilang) (-)
                            @if(($profitLoss['stock_shrinkage_events'] ?? 0) > 0)
                                <a href="{{ route('reports.stock-shrinkage', request()->all()) }}" class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle text-decoration-none ms-1 d-print-none" title="Lihat rincian barang yang menyusut">
                                    {{ $profitLoss['stock_shrinkage_events'] }} Catatan ({{ rtrim(rtrim(number_format((float) $profitLoss['stock_shrinkage_items'], 3, ',', '.'), '0'), ',') }} Item) <i class="bi bi-box-arrow-up-right ms-1"></i>
                                </a>
                            @endif
                        </td>
                        <td class="text-end text-danger">- Rp {{ number_format($profitLoss['stock_shrinkage'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="fw-bold">
                        <td class="ps-4">Total Beban Operasional & Penyusutan</td>
                        <td class="text-end text-danger">- Rp {{ number_format($profitLoss['total_expenses_and_shrinkage'] ?? $profitLoss['expenses'], 0, ',', '.') }}</td>
                    </tr>

                    <!-- Laba Kotor (Gross Profit) -->
                    <tr class="table-primary fw-bold fs-5">
                        <td>LABA KOTOR (GROSS PROFIT)</td>
                        <td class="text-end {{ $profitLoss['gross_profit'] >= 0 ? 'text-primary' : 'text-danger' }}">
                            {{ $profitLoss['gross_profit'] < 0 ? '- ' : '' }}Rp {{ number_format(abs($profitLoss['gross_profit']), 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 4. ALOKASI PEMBAGIAN LABA KOTOR (Tepat di Bawah Laba Kotor) -->
        <div class="pt-3 border-top">
            <form action="{{ route('reports.profit-allocation.save-credits') }}" method="POST" id="plAllocationCreditsForm" class="printable-form">
                @csrf
                <input type="hidden" name="redirect_to" value="profit-loss">
                <input type="hidden" name="period" value="{{ $period }}">
                <input type="hidden" name="start_date" value="{{ $start->format('Y-m-d') }}">
                <input type="hidden" name="end_date" value="{{ $end->format('Y-m-d') }}">

                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-pie-chart-fill text-primary me-2"></i>4. ALOKASI PEMBAGIAN LABA KOTOR (100%)
                        </h6>
                        <small class="text-muted d-print-none">
                            Pembagian otomatis dari <strong>LABA KOTOR</strong> sesuai persentase pos. Ketik nominal pada kolom <strong>KREDIT</strong> lalu klik <strong>Simpan Nilai Kredit</strong>.
                        </small>
                    </div>
                    <div class="d-flex gap-2 d-print-none">
                        <a href="{{ route('settings.index') }}#profit-allocation-settings" class="btn btn-light border btn-sm rounded-pill px-3">
                            <i class="bi bi-gear me-1"></i> Atur %
                        </a>
                        <button type="submit" class="btn btn-primary-custom btn-sm rounded-pill px-4 shadow-sm d-flex align-items-center gap-1">
                            <i class="bi bi-check2-circle"></i> Simpan Nilai Kredit
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table-allocation">
                        <thead>
                            <tr>
                                <th style="width: 30%;">NAMA</th>
                                <th class="text-end" style="width: 10%;">%</th>
                                <th class="text-end" style="width: 20%;">LABA KOTOR</th>
                                <th class="text-end" style="width: 20%;">DEBIT</th>
                                <th class="text-end" style="width: 20%;">KREDIT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Baris Pertama: LABA KOTOR -->
                            <tr>
                                <td class="fw-bold">LABA KOTOR</td>
                                <td class="text-end"></td>
                                <td class="text-end fw-bold">Rp{{ number_format($allocationSummary['gross_profit'], 0, ',', ',') }}</td>
                                <td class="text-end"></td>
                                <td class="text-end"></td>
                            </tr>

                            <!-- Baris Pos Alokasi -->
                            @foreach($allocations as $row)
                                <tr>
                                    <td class="fw-semibold">{{ $row->name }}</td>
                                    <td class="text-end">{{ rtrim(rtrim(number_format($row->percentage, 2, '.', ''), '0'), '.') }}%</td>
                                    <td class="text-end"></td>
                                    <td class="text-end fw-semibold">Rp{{ number_format($row->debit, 0, ',', ',') }}</td>
                                    <td class="text-end">
                                        <div class="input-group input-group-sm d-print-none">
                                            <span class="input-group-text bg-light text-muted">Rp</span>
                                            <input type="text"
                                                   inputmode="numeric"
                                                   name="credits[{{ $row->key }}]"
                                                   class="form-control form-control-sm text-end fw-semibold input-rupiah credit-input"
                                                   value="{{ $row->credit > 0 ? number_format($row->credit, 0, ',', '.') : '' }}"
                                                   placeholder="0"
                                                   oninput="recalculatePlAllocationTotals()">
                                        </div>
                                        <span class="credit-print-text fw-semibold" data-key="{{ $row->key }}">
                                            {{ $row->credit > 0 ? 'Rp' . number_format($row->credit, 0, ',', ',') : '' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach

                            <!-- Baris Pemisah Kosong -->
                            <tr>
                                <td style="height: 24px;"></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="allocation-footer-row">
                                <td></td>
                                <td></td>
                                <td class="text-end">Rp{{ number_format($allocationSummary['gross_profit'], 0, ',', ',') }}</td>
                                <td class="text-end" id="plFooterTotalDebit" data-debit="{{ $allocationSummary['total_debit'] }}">Rp{{ number_format($allocationSummary['total_debit'], 0, ',', ',') }}</td>
                                <td class="text-end" id="plFooterTotalCredit">Rp{{ number_format($allocationSummary['total_credit'], 0, ',', ',') }}</td>
                            </tr>
                            <tr class="allocation-footer-row">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-end" id="plFooterRemainingBalance">
                                    {{ $allocationSummary['remaining_balance'] < 0 ? '-Rp' : 'Rp' }}{{ number_format(abs($allocationSummary['remaining_balance']), 0, ',', ',') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </form>
        </div>
    </div>

    <!-- Top 10 Profit Contributors -->
    <div class="card card-custom p-4">
        <h6 class="fw-bold mb-3"><i class="bi bi-trophy text-warning me-2"></i> 10 Produk Kontributor Margin Keuntungan Terbesar</h6>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th>#</th>
                        <th>Nama Produk</th>
                        <th class="text-center">Qty Terjual</th>
                        <th class="text-end">Total Omset</th>
                        <th class="text-end">Total HPP</th>
                        <th class="text-end text-success fw-bold">Keuntungan Bersih</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bestSellers as $idx => $item)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td class="fw-semibold text-dark">{{ $item->product_name }}</td>
                            <td class="text-center"><span class="badge bg-primary rounded-pill">{{ $item->total_qty }}</span></td>
                            <td class="text-end">Rp {{ number_format($item->total_sales, 0, ',', '.') }}</td>
                            <td class="text-end text-muted">Rp {{ number_format($item->total_cogs, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold text-success">+ Rp {{ number_format($item->total_profit, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada data kontributor laba untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Signature Block (Khusus Print) -->
    <div class="d-none d-print-block mt-4">
        <table style="width: 100%; border: none;">
            <tr style="border: none;">
                <td style="width: 50%; border: none; text-align: center; padding-top: 20px;">
                    <p style="margin-bottom: 50px;">Dibuat Oleh,</p>
                    <p class="fw-bold mb-0" style="text-decoration: underline;">{{ auth()->user()->name }}</p>
                    <small style="color: #666;">Bagian Keuangan / Kasir</small>
                </td>
                <td style="width: 50%; border: none; text-align: center; padding-top: 20px;">
                    <p style="margin-bottom: 50px;">Menyetujui & Mengesahkan,</p>
                    <p class="fw-bold mb-0" style="text-decoration: underline;">( .................................................. )</p>
                    <small style="color: #666;">Owner / Pemilik Toko</small>
                </td>
            </tr>
        </table>
    </div>
</div>

@push('scripts')
<script>
    function formatPlCommaCurrency(num) {
        return new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(Math.round(num));
    }

    function recalculatePlAllocationTotals() {
        const totalDebitEl = document.getElementById('plFooterTotalDebit');
        const totalDebit = parseFloat(totalDebitEl?.getAttribute('data-debit') || '0') || 0;

        let totalCredit = 0;
        document.querySelectorAll('#plAllocationCreditsForm .credit-input').forEach(inp => {
            const raw = (inp.value || '').toString().replace(/\./g, '').replace(/,/g, '');
            const val = parseFloat(raw) || 0;
            totalCredit += val;

            const parentTd = inp.closest('td');
            const printSpan = parentTd?.querySelector('.credit-print-text');
            if (printSpan) {
                printSpan.innerText = val > 0 ? 'Rp' + formatPlCommaCurrency(val) : '';
            }
        });

        const remaining = totalDebit - totalCredit;

        const footerCreditEl = document.getElementById('plFooterTotalCredit');
        const footerRemEl = document.getElementById('plFooterRemainingBalance');

        if (footerCreditEl) footerCreditEl.innerText = 'Rp' + formatPlCommaCurrency(totalCredit);
        if (footerRemEl) footerRemEl.innerText = (remaining < 0 ? '-Rp' : 'Rp') + formatPlCommaCurrency(Math.abs(remaining));
    }
</script>
@endpush
@endsection
