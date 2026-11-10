<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpnameDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_opname_id',
        'product_id',
        'system_stock',
        'physical_stock',
        'difference',
        'cost_price',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'system_stock' => 'integer',
            'physical_stock' => 'integer',
            'difference' => 'integer',
            'cost_price' => 'decimal:2',
        ];
    }

    public function stockOpname()
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Hitung persentase selisih stok terhadap stok sistem (Opsi A: Variance %).
     */
    public function getDifferencePercentageAttribute(): float
    {
        $sys = (int) $this->system_stock;
        $diff = (int) $this->difference;

        if ($sys > 0) {
            return round(($diff / $sys) * 100, 2);
        }

        return ((int) $this->physical_stock) > 0 ? 100.0 : 0.0;
    }

    /**
     * Format tampilan persentase selisih (contoh: "+5%", "-12,5%", "0%").
     */
    public function getFormattedDifferencePercentageAttribute(): string
    {
        $pct = $this->difference_percentage;
        if ($pct == 0) {
            return '0%';
        }

        $formatted = rtrim(rtrim(number_format(abs($pct), 2, ',', '.'), '0'), ',');
        return ($pct > 0 ? '+' : '-') . $formatted . '%';
    }
}
