<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_number',
        'sale_id',
        'user_id',
        'cash_shift_id',
        'return_date',
        'total_refund',
        'exchange_total',
        'exchange_balance',
        'refund_method',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'datetime',
            'total_refund' => 'decimal:2',
            'exchange_total' => 'decimal:2',
            'exchange_balance' => 'decimal:2',
        ];
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cashShift()
    {
        return $this->belongsTo(CashShift::class);
    }

    public function details()
    {
        return $this->hasMany(SaleReturnDetail::class);
    }

    public function exchanges()
    {
        return $this->hasMany(SaleReturnExchange::class);
    }

    public function getIsExchangeAttribute(): bool
    {
        return in_array($this->refund_method, ['exchange', 'none'], true);
    }

    /**
     * Nilai pengurang omzet bersih dari transaksi retur ini.
     * - Jika refund tunai/kredit: mengurangi omzet sebesar total_refund.
     * - Jika tukar barang (exchange/none): mengurangi omzet sebesar (total_refund - exchange_total).
     */
    public function getNetReturnedAmountAttribute(): float
    {
        if ($this->is_exchange) {
            if ($this->refund_method === 'none' && (float) $this->exchange_total <= 0) {
                return 0.0;
            }
            return (float) $this->total_refund - (float) $this->exchange_total;
        }

        return (float) $this->total_refund;
    }
}
