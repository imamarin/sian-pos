<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'store_name' => Setting::get('store_name', 'Kasir POS'),
            'store_tagline' => Setting::get('store_tagline', 'Solusi Kasir Bisnis'),
            'store_address' => Setting::get('store_address', 'Jl. Alamat Toko'),
            'store_phone' => Setting::get('store_phone', '081234567890'),
            'store_email' => Setting::get('store_email', 'toko@pos.test'),
            'tax_percentage' => Setting::get('tax_percentage', '0'),
            'receipt_footer' => Setting::get('receipt_footer', 'Terima kasih atas kunjungan Anda!'),
            'invoice_prefix' => Setting::get('invoice_prefix', 'INV'),
            'purchase_prefix' => Setting::get('purchase_prefix', 'PO'),
            'low_stock_alert' => Setting::get('low_stock_alert', '5'),
        ];

        $allocationItems = ReportService::getProfitAllocationItems();

        return view('settings.index', compact('settings', 'allocationItems'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'store_tagline' => ['nullable', 'string', 'max:255'],
            'store_address' => ['nullable', 'string'],
            'store_phone' => ['nullable', 'string', 'max:50'],
            'store_email' => ['nullable', 'email', 'max:255'],
            'tax_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'receipt_footer' => ['nullable', 'string'],
            'invoice_prefix' => ['required', 'string', 'max:10'],
            'purchase_prefix' => ['required', 'string', 'max:10'],
            'allocation_items' => ['nullable', 'array', 'min:1'],
            'allocation_items.*.key' => ['nullable', 'string', 'max:60'],
            'allocation_items.*.label' => ['required_with:allocation_items', 'string', 'max:100'],
            'allocation_items.*.percentage' => ['required_with:allocation_items', 'numeric', 'min:0', 'max:100'],
        ]);

        if (!empty($validated['allocation_items']) && is_array($validated['allocation_items'])) {
            $totalPercentage = collect($validated['allocation_items'])
                ->sum(fn ($item) => (float) ($item['percentage'] ?? 0));

            if (round($totalPercentage, 2) != 100.00) {
                return back()
                    ->withErrors([
                        'allocation_items' => "Total persentase Alokasi Laba Kotor harus tepat 100% (saat ini: {$totalPercentage}%).",
                    ])
                    ->withInput();
            }

            $cleanAllocations = [];
            foreach ($validated['allocation_items'] as $idx => $item) {
                $rawLabel = trim((string) ($item['label'] ?? ''));
                $cleanLabel = trim((string) preg_replace('/\s*\d+(?:[.,]\d+)?%\s*$/u', '', $rawLabel));
                if ($cleanLabel === '') {
                    $cleanLabel = $rawLabel ?: ('POS ' . ($idx + 1));
                }

                $pct = (float) ($item['percentage'] ?? 0);
                $pctFormatted = rtrim(rtrim(number_format($pct, 2, '.', ''), '0'), '.');
                $key = !empty($item['key'])
                    ? (string) $item['key']
                    : (Str::slug($cleanLabel, '_') ?: ('pos_' . ($idx + 1)));

                $cleanAllocations[] = [
                    'key' => $key,
                    'label' => $cleanLabel,
                    'name' => "{$cleanLabel} {$pctFormatted}%",
                    'percentage' => $pct,
                ];
            }

            Setting::set(
                'profit_allocation_config',
                json_encode($cleanAllocations),
                'reports',
                'Konfigurasi Persentase Alokasi Laba Kotor'
            );
        }

        unset($validated['allocation_items']);

        foreach ($validated as $key => $value) {
            Setting::set($key, (string) $value, 'general');
        }

        return back()->with('success', 'Pengaturan sistem, profil toko, & persentase alokasi laba berhasil disimpan.');
    }
}
