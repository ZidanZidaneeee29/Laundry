<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id('id_transaksi');
            $table->unsignedBigInteger('id_pelanggan');
            $table->unsignedBigInteger('id_kasir');
            $table->string('no_nota', 20)->unique();
            $table->dateTime('tgl_masuk');
            $table->double('total_bayar', 10, 2);
            $table->string('status_pengerjaan', 20)->default('Antre'); // Antre, Cuci, Kering, Setrika, Selesai
            $table->unsignedTinyInteger('no_mesin_cuci')->nullable();
            $table->dateTime('estimasi_selesai')->nullable();
            $table->timestamps();

            $table->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan')->onDelete('cascade');
            $table->foreign('id_kasir')->references('id_user')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
