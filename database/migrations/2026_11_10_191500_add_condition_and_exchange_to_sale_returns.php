<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_returns', 'exchange_total')) {
                $table->decimal('exchange_total', 15, 2)->default(0)->after('total_refund');
            }
            if (!Schema::hasColumn('sale_returns', 'exchange_balance')) {
                $table->decimal('exchange_balance', 15, 2)->default(0)->after('exchange_total');
            }
        });

        Schema::table('sale_return_details', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_return_details', 'item_condition')) {
                $table->string('item_condition', 20)->default('good')->after('qty'); // good, damaged
            }
            if (!Schema::hasColumn('sale_return_details', 'cost_price')) {
                $table->decimal('cost_price', 15, 2)->default(0)->after('refund_price');
            }
        });

        if (!Schema::hasTable('sale_return_exchanges')) {
            Schema::create('sale_return_exchanges', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_return_id')->constrained('sale_returns')->onDelete('cascade');
                $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
                $table->integer('qty')->default(1);
                $table->decimal('cost_price', 15, 2)->default(0);
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_return_exchanges');

        Schema::table('sale_return_details', function (Blueprint $table) {
            $cols = array_filter(['item_condition', 'cost_price'], fn ($c) => Schema::hasColumn('sale_return_details', $c));
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('sale_returns', function (Blueprint $table) {
            $cols = array_filter(['exchange_total', 'exchange_balance'], fn ($c) => Schema::hasColumn('sale_returns', $c));
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};

