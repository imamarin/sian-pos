@extends('layouts.app')

@section('title', 'Detail Retur Penjualan #' . $saleReturn->return_number)

@section('content')
<div class="container-fluid px-0" style="max-width: 950px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Bukti Retur Penjualan: {{ $saleReturn->return_number }}</h4>
            <p class="text-muted small mb-0">Rincian produk yang dikembalikan pelanggan, status kondisi stok, serta pertukaran / pengembalian dana</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-printer me-1"></i> Cetak Bukti
            </button>
            <a href="{{ route('returns.sales.index') }}" class="btn btn-primary-custom btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card card-custom p-4 mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <small class="text-muted d-block">Tanggal Retur</small>
                <div class="fw-bold">{{ $saleReturn->return_date?->format('d/m/Y H:i') ?? $saleReturn->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Nota Penjualan Asal</small>
                @if($saleReturn->sale_id)
                    <a href="{{ route('sales.show', $saleReturn->sale_id) }}" class="fw-bold font-monospace text-decoration-none">
                        {{ $saleReturn->sale?->invoice_number ?? '#' . $saleReturn->sale_id }}
                    </a>
                @else
                    <span class="text-muted">-</span>
                @endif
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Pelanggan</small>
                <div class="fw-bold">{{ $saleReturn->sale?->customer?->name ?? 'Umum' }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Metode Penyelesaian</small>
                @if($saleReturn->is_exchange)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">TUKAR BARANG</span>
                @elseif($saleReturn->refund_method === 'credit')
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">POTONG PIUTANG / KREDIT</span>
                @else
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">REFUND TUNAI</span>
                @endif
            </div>
        </div>
        @if($saleReturn->notes)
            <div class="mt-3 pt-3 border-top small text-muted">
                <strong>Catatan:</strong> {{ $saleReturn->notes }}
            </div>
        @endif
    </div>

    <!-- Tabel 1: Barang yang Dikembalikan Pelanggan -->
    <div class="card card-custom p-4 mb-4">
        <h6 class="fw-bold mb-3"><i class="bi bi-box-arrow-in-down-left text-danger me-2"></i> Barang yang Dikembalikan Pelanggan</h6>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th>#</th>
                        <th>Produk</th>
                        <th class="text-center">Kuantitas</th>
                        <th class="text-center">Kondisi & Status Stok</th>
                        <th class="text-end">Harga Satuan</th>
                        <th class="text-end">Subtotal</th>
                        <th>Alasan Retur</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($saleReturn->details as $idx => $d)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td class="fw-bold text-dark">{{ $d->product?->name }}</td>
                            <td class="text-center fw-bold">{{ $d->qty }} {{ $d->product?->unit?->short_name }}</td>
                            <td class="text-center">
                                @if(($d->item_condition ?? 'good') === 'damaged')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                        <i class="bi bi-x-circle me-1"></i>Barang Rusak (Non-Stok)
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-check-circle me-1"></i>Layak Jual (Masuk Stok)
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">Rp {{ number_format($d->refund_price, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                            <td class="small text-muted">{{ $d->reason ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="5" class="text-end">Total Nilai Barang yang Diretur:</td>
                        <td class="text-end text-danger fs-6">Rp {{ number_format($saleReturn->total_refund, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Tabel 2: Barang Pengganti (Jika Metode = Tukar Barang) -->
    @if($saleReturn->is_exchange && $saleReturn->exchanges->isNotEmpty())
        <div class="card card-custom p-4 mb-4 border-primary">
            <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-arrow-left-right me-2"></i> Barang Pengganti yang Diberikan ke Pelanggan</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>#</th>
                            <th>Produk Pengganti</th>
                            <th class="text-center">Kuantitas Keluar</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($saleReturn->exchanges as $idx => $ex)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td class="fw-bold text-dark">{{ $ex->product?->name }}</td>
                                <td class="text-center fw-bold text-primary">-{{ $ex->qty }} {{ $ex->product?->unit?->short_name }}</td>
                                <td class="text-end">Rp {{ number_format($ex->unit_price, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold">Rp {{ number_format($ex->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="4" class="text-end">Total Nilai Barang Pengganti:</td>
                            <td class="text-end text-primary fs-6">Rp {{ number_format($saleReturn->exchange_total, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end">
                                @if($saleReturn->exchange_balance > 0)
                                    Selisih Pertukaran (Pelanggan Tambah Bayar):
                                @elseif($saleReturn->exchange_balance < 0)
                                    Selisih Pertukaran (Dikembalikan ke Pelanggan / Potong Piutang):
                                @else
                                    Selisih Pertukaran:
                                @endif
                            </td>
                            <td class="text-end fs-5 {{ $saleReturn->exchange_balance > 0 ? 'text-success' : ($saleReturn->exchange_balance < 0 ? 'text-danger' : 'text-secondary') }}">
                                @if($saleReturn->exchange_balance > 0)
                                    +Rp {{ number_format($saleReturn->exchange_balance, 0, ',', '.') }}
                                @elseif($saleReturn->exchange_balance < 0)
                                    -Rp {{ number_format(abs($saleReturn->exchange_balance), 0, ',', '.') }}
                                @else
                                    Tukar Pas (Rp 0)
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
