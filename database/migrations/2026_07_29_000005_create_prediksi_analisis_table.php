<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prediksi_analisis', function (Blueprint $table) {
            $table->id('id_prediksi');
            $table->unsignedBigInteger('id_transaksi');
            $table->string('model_version', 20)->default('RF-Reg-v1.0');
            $table->double('confidence_score', 10, 4)->default(0.95);
            $table->integer('jumlah_antrean')->default(0);
            $table->double('durasi_estimasi_jam', 10, 2)->default(0);
            $table->text('detail_pohon_json')->nullable();
            $table->timestamps();

            $table->foreign('id_transaksi')->references('id_transaksi')->on('transaksi')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prediksi_analisis');
    }
};
