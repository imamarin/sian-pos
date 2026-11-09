<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->string('payment_method'); // cash, transfer, qris, debit, credit
            $table->decimal('amount', 15, 2)->default(0); // Net amount applied to invoice
            $table->decimal('tendered_amount', 15, 2)->default(0); // Nominal diserahkan pelanggan pada metode ini
            $table->string('reference_note')->nullable();
            $table->timestamps();

            $table->index(['sale_id', 'payment_method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
    }
};

