<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'customer_id',
        'cash_shift_id',
        'transaction_date',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'shipping_cost',
        'grand_total',
        'paid_amount',
        'change_amount',
        'payment_method',
        'dp_payment_method',
        'payment_status',
        'due_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'datetime',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_percentage' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
        ];
    }

    /**
     * Get net product sales (excluding shipping cost and after invoice discount).
     */
    public function getProductNetSalesAttribute(): float
    {
        return max(0, (float) $this->subtotal - (float) $this->discount_amount);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashShift()
    {
        return $this->belongsTo(CashShift::class);
    }

    public function details()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function returns()
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function payments()
    {
        return $this->hasMany(SalePayment::class);
    }

    public function paymentAllocations()
    {
        return $this->hasMany(ReceivablePaymentAllocation::class);
    }

    public function getRemainingDebtAttribute(): float
    {
        return max(0, (float) $this->grand_total - (float) $this->paid_amount);
    }

    /**
     * Total nominal bersih yang diretur pada nota ini (memperhitungkan selisih tukar barang).
     */
    public function getTotalReturnedAttribute(): float
    {
        $returns = $this->relationLoaded('returns') ? $this->returns : $this->returns()->get();

        return (float) $returns->sum(fn (SaleReturn $r) => $r->net_returned_amount);
    }

    /**
     * Grand Total Bersih setelah dikurangi retur penjualan.
     */
    public function getNetGrandTotalAttribute(): float
    {
        return max(0.0, (float) $this->grand_total - $this->total_returned);
    }
}
