<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transaksi') && !Schema::hasColumn('transaksi', 'no_mesin_cuci')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->unsignedTinyInteger('no_mesin_cuci')->nullable()->after('status_pengerjaan');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('transaksi') && Schema::hasColumn('transaksi', 'no_mesin_cuci')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->dropColumn('no_mesin_cuci');
            });
        }
    }
};
