<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Laba Rugi</title>
    <style>
        @page {
            margin: 15mm 12mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #222;
            line-height: 1.35;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #222;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .store-title {
            font-size: 16px;
            font-weight: bold;
            color: #111;
            text-transform: uppercase;
        }
        .store-sub {
            font-size: 9px;
            color: #555;
            margin-top: 2px;
        }
        .report-title {
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            margin-top: 6px;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
        }
        .report-meta {
            text-align: center;
            font-size: 9.5px;
            color: #555;
            margin-bottom: 15px;
        }
        table.pl-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        table.pl-table th {
            background-color: #f1f5f9;
            color: #111;
            padding: 7px 8px;
            text-align: left;
            border: 1px solid #94a3b8;
            font-size: 10px;
        }
        table.pl-table td {
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            font-size: 9.5px;
        }
        .section-header {
            background-color: #f8fafc;
            font-weight: bold;
            color: #1e293b;
            font-size: 10px;
        }
        .pl-total {
            font-weight: bold;
            background-color: #f1f5f9;
            font-size: 10.5px;
        }
        .net-profit-row {
            font-weight: bold;
            font-size: 11px;
            background-color: #dbeafe;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }

        table.allocation-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 18px;
            page-break-inside: avoid;
        }
        table.allocation-table th {
            background-color: #3b7ddd;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 8px;
            border: 1px solid #1e293b;
            font-size: 9.5px;
        }
        table.allocation-table td {
            padding: 5px 8px;
            border: 1px solid #334155;
            font-size: 9px;
            color: #0f172a;
        }
        table.allocation-table tr.alloc-footer td {
            background-color: #3b7ddd;
            color: #000000;
            font-weight: bold;
            font-size: 9.5px;
            border: 1px solid #1e293b;
        }

        table.bestseller-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        table.bestseller-table th {
            background-color: #f1f5f9;
            padding: 6px;
            font-size: 9.5px;
            border: 1px solid #94a3b8;
        }
        table.bestseller-table td {
            padding: 5px;
            font-size: 9px;
            border: 1px solid #cbd5e1;
        }

        .signature-table {
            width: 100%;
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .signature-table td {
            text-align: center;
            vertical-align: top;
            font-size: 9.5px;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <div class="store-title">{{ $settings['store_name'] }}</div>
                <div class="store-sub">{{ $settings['store_address'] }} &bull; Telp: {{ $settings['store_phone'] }}</div>
            </td>
            <td style="text-align: right; vertical-align: middle; font-size: 9px; color: #666;">
                Dicetak: {{ now()->format('d/m/Y H:i') }}<br>
                Petugas: {{ auth()->user()->name ?? 'Admin' }}
            </td>
        </tr>
    </table>

    <div class="report-title">Laporan Laba Rugi (Profit & Loss)</div>
    <div class="report-meta">
        Periode: <strong>{{ $startDate->format('d/m/Y') }}</strong> s/d <strong>{{ $endDate->format('d/m/Y') }}</strong>
    </div>

    <!-- Income Statement Table -->
    <table class="pl-table">
        <thead>
            <tr>
                <th style="width: 70%;">Komponen Keuangan & Arus Laba Rugi</th>
                <th style="width: 30%;" class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <!-- 1. PENDAPATAN -->
            <tr class="section-header">
                <td colspan="2">1. PENDAPATAN PENJUALAN (REVENUE)</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Total Penjualan Kotor (Subtotal)</td>
                <td class="text-right">Rp {{ number_format($profitLoss['total_subtotal'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Potongan Diskon Transaksi (-)</td>
                <td class="text-right" style="color: #b91c1c;">- Rp {{ number_format($profitLoss['total_discounts'], 0, ',', '.') }}</td>
            </tr>
            @if(($profitLoss['total_returns'] ?? 0) > 0)
            <tr>
                <td style="padding-left: 20px;">Potongan Retur Penjualan (-)</td>
                <td class="text-right" style="color: #b91c1c;">- Rp {{ number_format($profitLoss['total_returns'], 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="pl-total">
                <td>Total Penjualan Bersih (Omset Bersih)</td>
                <td class="text-right">Rp {{ number_format($profitLoss['total_revenue'], 0, ',', '.') }}</td>
            </tr>

            <!-- 2. HPP -->
            <tr class="section-header">
                <td colspan="2">2. HARGA POKOK PENJUALAN (HPP / COGS)</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Total Harga Modal Pokok Barang Terjual (-)</td>
                <td class="text-right" style="color: #b91c1c;">- Rp {{ number_format($profitLoss['total_cogs'], 0, ',', '.') }}</td>
            </tr>

            <!-- 3. BEBAN OPERASIONAL & PENYUSUTAN (Sebelum Laba Kotor) -->
            <tr class="section-header">
                <td colspan="2">3. BEBAN OPERASIONAL & PENYUSUTAN</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Biaya Listrik, Air, Gaji, Makan, Operasional Kasir (-)</td>
                <td class="text-right" style="color: #b91c1c;">- Rp {{ number_format($profitLoss['expenses'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">
                    Total Penyusutan & Kerugian Stok (Opname / Rusak / Hilang) (-)
                    @if(($profitLoss['stock_shrinkage_events'] ?? 0) > 0)
                        <span style="color: #666; font-size: 8.5px;">({{ $profitLoss['stock_shrinkage_events'] }} catatan, {{ rtrim(rtrim(number_format((float) $profitLoss['stock_shrinkage_items'], 3, ',', '.'), '0'), ',') }} item)</span>
                    @endif
                </td>
                <td class="text-right" style="color: #b91c1c;">- Rp {{ number_format($profitLoss['stock_shrinkage'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr class="pl-total">
                <td>Total Beban Operasional & Penyusutan</td>
                <td class="text-right" style="color: #b91c1c;">- Rp {{ number_format($profitLoss['total_expenses_and_shrinkage'] ?? $profitLoss['expenses'], 0, ',', '.') }}</td>
            </tr>

            <!-- LABA KOTOR (GROSS PROFIT) -->
            <tr class="net-profit-row">
                <td style="font-size: 11px;">LABA KOTOR (GROSS PROFIT)</td>
                <td class="text-right" style="font-size: 11px; {{ $profitLoss['gross_profit'] >= 0 ? 'color: #1d4ed8;' : 'color: #b91c1c;' }}">
                    {{ $profitLoss['gross_profit'] < 0 ? '-' : '' }}Rp {{ number_format(abs($profitLoss['gross_profit']), 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- 4. ALOKASI PEMBAGIAN LABA KOTOR -->
    @if(isset($allocations) && isset($allocationSummary))
        <div style="font-weight: bold; font-size: 10.5px; margin-top: 10px; margin-bottom: 4px;">
            4. ALOKASI PEMBAGIAN LABA KOTOR (100%)
        </div>
        <table class="allocation-table">
            <thead>
                <tr>
                    <th style="width: 32%; text-align: left;">NAMA</th>
                    <th style="width: 10%;" class="text-right">%</th>
                    <th style="width: 19%;" class="text-right">LABA KOTOR</th>
                    <th style="width: 19.5%;" class="text-right">DEBIT</th>
                    <th style="width: 19.5%;" class="text-right">KREDIT</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="fw-bold">LABA KOTOR</td>
                    <td></td>
                    <td class="text-right fw-bold">Rp{{ number_format($allocationSummary['gross_profit'], 0, ',', ',') }}</td>
                    <td></td>
                    <td></td>
                </tr>
                @foreach($allocations as $row)
                    <tr>
                        <td class="fw-bold">{{ $row->name }}</td>
                        <td class="text-right">{{ rtrim(rtrim(number_format($row->percentage, 2, '.', ''), '0'), '.') }}%</td>
                        <td></td>
                        <td class="text-right">Rp{{ number_format($row->debit, 0, ',', ',') }}</td>
                        <td class="text-right">{{ $row->credit > 0 ? 'Rp' . number_format($row->credit, 0, ',', ',') : '' }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td style="height: 12px;"></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr class="alloc-footer">
                    <td></td>
                    <td></td>
                    <td class="text-right">Rp{{ number_format($allocationSummary['gross_profit'], 0, ',', ',') }}</td>
                    <td class="text-right">Rp{{ number_format($allocationSummary['total_debit'], 0, ',', ',') }}</td>
                    <td class="text-right">Rp{{ number_format($allocationSummary['total_credit'], 0, ',', ',') }}</td>
                </tr>
                <tr class="alloc-footer">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="text-right">
                        {{ $allocationSummary['remaining_balance'] < 0 ? '-Rp' : 'Rp' }}{{ number_format(abs($allocationSummary['remaining_balance']), 0, ',', ',') }}
                    </td>
                </tr>
            </tbody>
        </table>
    @endif

    @if($bestSellers->count() > 0)
        <div style="font-weight: bold; font-size: 11px; margin-top: 20px; margin-bottom: 5px;">
            10 Produk Terlaris & Margin Keuntungan (Periode Berjalan)
        </div>
        <table class="bestseller-table">
            <thead>
                <tr>
                    <th style="width: 5%;" class="text-center">#</th>
                    <th style="width: 35%;">Nama Produk</th>
                    <th style="width: 12%;" class="text-center">Terjual</th>
                    <th style="width: 16%;" class="text-right">Total Penjualan</th>
                    <th style="width: 16%;" class="text-right">Total HPP</th>
                    <th style="width: 16%;" class="text-right">Laba Kotor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bestSellers as $idx => $item)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="fw-bold">{{ $item->product_name }}</td>
                        <td class="text-center">{{ $item->total_qty }} pcs</td>
                        <td class="text-right">Rp {{ number_format($item->total_sales, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($item->total_cogs, 0, ',', '.') }}</td>
                        <td class="text-right fw-bold" style="color: #047857;">Rp {{ number_format($item->total_profit, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <table class="signature-table">
        <tr>
            <td style="width: 50%;">
                <p style="margin-bottom: 50px;">Dibuat Oleh,</p>
                <p class="fw-bold" style="text-decoration: underline; margin-bottom: 2px;">{{ auth()->user()->name ?? 'Admin' }}</p>
                <span style="color: #666;">Bagian Keuangan / Kasir</span>
            </td>
            <td style="width: 50%;">
                <p style="margin-bottom: 50px;">Menyetujui & Mengesahkan,</p>
                <p class="fw-bold" style="text-decoration: underline; margin-bottom: 2px;">( .................................................. )</p>
                <span style="color: #666;">Owner / Pemilik Toko</span>
            </td>
        </tr>
    </table>
</body>
</html>
