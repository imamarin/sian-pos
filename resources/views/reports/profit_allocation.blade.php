@extends('layouts.app')

@section('title', 'Laporan Alokasi Laba Kotor')

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
        form#allocationCreditsForm {
            display: block !important;
        }
        .credit-print-text {
            display: inline !important;
        }
        .table-allocation th,
        .table-allocation tfoot tr.allocation-footer-row td {
            background-color: #3b7ddd !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

<div class="container-fluid px-0" style="max-width: 1000px;">
    <!-- Header Action (Web Only) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 d-print-none">
        <div>
            <h4 class="fw-bold mb-1">Laporan Alokasi Laba Kotor</h4>
            <p class="text-muted small mb-0">Pembagian persentase pos alokasi dari Laba Kotor Murni beserta pencatatan realisasi Kredit</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('settings.index') }}#profit-allocation-settings" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm d-flex align-items-center gap-1">
                <i class="bi bi-sliders"></i> Atur % Alokasi
            </a>
            <a href="{{ route('reports.profit-allocation.pdf', request()->all()) }}" class="btn btn-outline-danger btn-sm rounded-pill px-3 shadow-sm d-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-pdf"></i> Ekspor PDF
            </a>
            <button type="button" onclick="window.print()" class="btn btn-light border btn-sm rounded-pill px-3 shadow-sm d-flex align-items-center gap-1">
                <i class="bi bi-printer me-1"></i> Print Laporan
            </button>
        </div>
    </div>

    <!-- Kop Header Toko (Khusus Print) -->
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
            <h4 style="margin: 0; font-weight: bold; text-decoration: underline; text-transform: uppercase;">LAPORAN ALOKASI LABA KOTOR</h4>
            <div style="font-size: 9pt; color: #555; margin-top: 3px;">
                Periode: <strong>{{ $start->format('d/m/Y') }}</strong> s/d <strong>{{ $end->format('d/m/Y') }}</strong>
            </div>
        </div>
    </div>

    <!-- Filter Card (Web Only) -->
    <div class="card card-custom p-3 mb-4 d-print-none">
        <form method="GET" action="{{ route('reports.profit-allocation') }}" class="row g-2 align-items-end">
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
                <a href="{{ route('reports.profit-allocation') }}" class="btn btn-light border btn-sm" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>

    <!-- Summary KPI Cards (Web Only) -->
    <div class="row g-3 mb-4 d-print-none">
        <div class="col-12 col-md-4">
            <div class="card card-custom p-3 border-start border-4 border-primary">
                <small class="text-muted d-block">Total Laba Kotor (100%)</small>
                <h5 class="fw-bold text-primary mb-0">Rp {{ number_format($summary['gross_profit'], 0, ',', '.') }}</h5>
                <small class="text-muted" style="font-size: 0.72rem;">Omset Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }} - HPP Rp {{ number_format($summary['total_cogs'], 0, ',', '.') }} - Beban & Penyusutan Rp {{ number_format($summary['total_expenses_and_shrinkage'] ?? 0, 0, ',', '.') }}</small>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card card-custom p-3 border-start border-4 border-danger">
                <small class="text-muted d-block">Total Realisasi Kredit</small>
                <h5 class="fw-bold text-danger mb-0" id="cardTotalCredit">Rp {{ number_format($summary['total_credit'], 0, ',', '.') }}</h5>
                <small class="text-muted" style="font-size: 0.72rem;">Akumulasi pemakaian dana pada pos alokasi</small>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card card-custom p-3 border-start border-4 border-success">
                <small class="text-muted d-block">Sisa Saldo Alokasi (Debit - Kredit)</small>
                <h5 class="fw-bold text-success mb-0" id="cardRemainingBalance">Rp {{ number_format($summary['remaining_balance'], 0, ',', '.') }}</h5>
                <small class="text-muted" style="font-size: 0.72rem;">Sisa dana alokasi setelah dikurangi Kredit</small>
            </div>
        </div>
    </div>

    <!-- Main Profit Allocation Form & Table -->
    <div class="card card-custom p-4 mb-4">
        <form action="{{ route('reports.profit-allocation.save-credits') }}" method="POST" id="allocationCreditsForm" class="printable-form">
            @csrf
            <input type="hidden" name="period" value="{{ $period }}">
            <input type="hidden" name="start_date" value="{{ $start->format('Y-m-d') }}">
            <input type="hidden" name="end_date" value="{{ $end->format('Y-m-d') }}">

            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3 d-print-none">
                <div>
                    <h6 class="fw-bold mb-0"><i class="bi bi-pie-chart-fill text-primary me-2"></i>Tabel Alokasi Laba Kotor ({{ $start->format('d/m/Y') }} - {{ $end->format('d/m/Y') }})</h6>
                    <small class="text-muted">Ketik nominal pengeluaran pada kolom <strong>KREDIT</strong> lalu klik <strong>Simpan Nilai Kredit</strong>.</small>
                </div>
                <button type="submit" class="btn btn-primary-custom btn-sm rounded-pill px-4 shadow-sm d-flex align-items-center gap-1">
                    <i class="bi bi-check2-circle"></i> Simpan Nilai Kredit
                </button>
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
                            <td class="text-end fw-bold">Rp{{ number_format($summary['gross_profit'], 0, ',', ',') }}</td>
                            <td class="text-end"></td>
                            <td class="text-end"></td>
                        </tr>

                        <!-- 9 Baris Pos Alokasi -->
                        @foreach($allocations as $row)
                            <tr>
                                <td class="fw-semibold">{{ $row->name }}</td>
                                <td class="text-end">{{ rtrim(rtrim(number_format($row->percentage, 2, '.', ''), '0'), '.') }}%</td>
                                <td class="text-end"></td>
                                <td class="text-end fw-semibold">Rp{{ number_format($row->debit, 0, ',', ',') }}</td>
                                <td class="text-end">
                                    <!-- Input di Layar Web (Opsi 2) -->
                                    <div class="input-group input-group-sm d-print-none">
                                        <span class="input-group-text bg-light text-muted">Rp</span>
                                        <input type="text"
                                               inputmode="numeric"
                                               name="credits[{{ $row->key }}]"
                                               class="form-control form-control-sm text-end fw-semibold input-rupiah credit-input"
                                               value="{{ $row->credit > 0 ? number_format($row->credit, 0, ',', '.') : '' }}"
                                               placeholder="0"
                                               oninput="recalculateAllocationTotals()">
                                    </div>
                                    <!-- Teks saat di-Print -->
                                    <span class="credit-print-text fw-semibold" data-key="{{ $row->key }}">
                                        {{ $row->credit > 0 ? 'Rp' . number_format($row->credit, 0, ',', ',') : '' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach

                        <!-- Baris Pemisah Kosong seperti pada Gambar -->
                        <tr>
                            <td style="height: 28px;"></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <!-- Baris Footer 1: Total Laba Kotor | Total Debit | Total Kredit -->
                        <tr class="allocation-footer-row">
                            <td></td>
                            <td></td>
                            <td class="text-end">Rp{{ number_format($summary['gross_profit'], 0, ',', ',') }}</td>
                            <td class="text-end" id="footerTotalDebit" data-debit="{{ $summary['total_debit'] }}">Rp{{ number_format($summary['total_debit'], 0, ',', ',') }}</td>
                            <td class="text-end" id="footerTotalCredit">Rp{{ number_format($summary['total_credit'], 0, ',', ',') }}</td>
                        </tr>
                        <!-- Baris Footer 2: Sisa Saldo (Total Debit - Total Kredit) -->
                        <tr class="allocation-footer-row">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-end" id="footerRemainingBalance">Rp{{ number_format($summary['remaining_balance'], 0, ',', ',') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function formatCommaCurrency(num) {
        return new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(Math.round(num));
    }

    function formatDotCurrency(num) {
        return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Math.round(num));
    }

    function recalculateAllocationTotals() {
        const totalDebitEl = document.getElementById('footerTotalDebit');
        const totalDebit = parseFloat(totalDebitEl?.getAttribute('data-debit') || '0') || 0;

        let totalCredit = 0;
        document.querySelectorAll('.credit-input').forEach(inp => {
            const raw = (inp.value || '').toString().replace(/\./g, '').replace(/,/g, '');
            const val = parseFloat(raw) || 0;
            totalCredit += val;
        });

        const remaining = totalDebit - totalCredit;

        const footerCreditEl = document.getElementById('footerTotalCredit');
        const footerRemEl = document.getElementById('footerRemainingBalance');
        const cardCreditEl = document.getElementById('cardTotalCredit');
        const cardRemEl = document.getElementById('cardRemainingBalance');

        if (footerCreditEl) footerCreditEl.innerText = 'Rp' + formatCommaCurrency(totalCredit);
        if (footerRemEl) footerRemEl.innerText = (remaining < 0 ? '-Rp' : 'Rp') + formatCommaCurrency(Math.abs(remaining));
        if (cardCreditEl) cardCreditEl.innerText = 'Rp ' + formatDotCurrency(totalCredit);
        if (cardRemEl) cardRemEl.innerText = (remaining < 0 ? '-Rp ' : 'Rp ') + formatDotCurrency(Math.abs(remaining));
    }
</script>
@endpush
@endsection

