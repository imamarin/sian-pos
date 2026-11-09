<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'payment_method',
        'amount',
        'tendered_amount',
        'reference_note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'tendered_amount' => 'decimal:2',
        ];
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public static function methodLabel(string $method): string
    {
        return match (strtolower($method)) {
            'cash' => 'Tunai (Cash)',
            'transfer' => 'Transfer Bank',
            'qris' => 'QRIS',
            'debit' => 'Kartu Debit',
            'credit' => 'Kartu Kredit',
            'debt' => 'Hutang / Tempo',
            'split' => 'Split / Campuran',
            default => strtoupper($method),
        };
    }
}

