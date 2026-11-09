<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('customers')
            ->where('member_level', 'silver')
            ->update(['member_level' => 'reseller']);

        DB::table('customers')
            ->whereIn('member_level', ['gold', 'platinum'])
            ->update(['member_level' => 'dapur']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('customers')
            ->where('member_level', 'reseller')
            ->update(['member_level' => 'silver']);

        DB::table('customers')
            ->where('member_level', 'dapur')
            ->update(['member_level' => 'gold']);
    }
};

