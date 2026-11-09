<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Alokasi Laba Kotor</title>
    <style>
        @page {
            margin: 15mm 12mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10.5px;
            color: #111;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #111;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .store-title {
            font-size: 16px;
            font-weight: bold;
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
        }
        .report-meta {
            text-align: center;
            font-size: 9.5px;
            color: #555;
            margin-bottom: 16px;
        }
        table.allocation-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        table.allocation-table th {
            background-color: #3b7ddd;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 10px;
            border: 1px solid #000;
            font-size: 10px;
        }
        table.allocation-table td {
            padding: 7px 10px;
            border: 1px solid #000;
            font-size: 10px;
        }
        table.allocation-table tr.footer-blue td {
            background-color: #3b7ddd;
            color: #000;
            font-weight: bold;
            font-size: 10.5px;
            border: 1px solid #000;
        }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td>
                <div class="store-title">{{ $settings['store_name'] }}</div>
                <div class="store-sub">{{ $settings['store_address'] }} &bull; Telp: {{ $settings['store_phone'] }}</div>
            </td>
            <td style="text-align: right; font-size: 9px; color: #555;">
                Dicetak: {{ now()->format('d/m/Y H:i') }}<br>
                Petugas: {{ auth()->user()->name ?? 'Admin' }}
            </td>
        </tr>
    </table>

    <div class="report-title">LAPORAN ALOKASI LABA KOTOR</div>
    <div class="report-meta">
        Periode: <strong>{{ $startDate->format('d/m/Y') }}</strong> s/d <strong>{{ $endDate->format('d/m/Y') }}</strong>
    </div>

    <table class="allocation-table">
        <thead>
            <tr>
                <th class="text-left" style="width: 32%;">NAMA</th>
                <th class="text-right" style="width: 10%;">%</th>
                <th class="text-right" style="width: 19%;">LABA KOTOR</th>
                <th class="text-right" style="width: 19%;">DEBIT</th>
                <th class="text-right" style="width: 20%;">KREDIT</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="fw-bold">LABA KOTOR</td>
                <td class="text-right"></td>
                <td class="text-right fw-bold">Rp{{ number_format($summary['gross_profit'], 0, ',', ',') }}</td>
                <td class="text-right"></td>
                <td class="text-right"></td>
            </tr>
            @foreach($allocations as $row)
                <tr>
                    <td>{{ $row->name }}</td>
                    <td class="text-right">{{ rtrim(rtrim(number_format($row->percentage, 2, '.', ''), '0'), '.') }}%</td>
                    <td class="text-right"></td>
                    <td class="text-right">Rp{{ number_format($row->debit, 0, ',', ',') }}</td>
                    <td class="text-right">{{ $row->credit > 0 ? 'Rp' . number_format($row->credit, 0, ',', ',') : '' }}</td>
                </tr>
            @endforeach
            <tr>
                <td style="height: 16px;"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr class="footer-blue">
                <td></td>
                <td></td>
                <td class="text-right">Rp{{ number_format($summary['gross_profit'], 0, ',', ',') }}</td>
                <td class="text-right">Rp{{ number_format($summary['total_debit'], 0, ',', ',') }}</td>
                <td class="text-right">Rp{{ number_format($summary['total_credit'], 0, ',', ',') }}</td>
            </tr>
            <tr class="footer-blue">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-right">Rp{{ number_format($summary['remaining_balance'], 0, ',', ',') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>

