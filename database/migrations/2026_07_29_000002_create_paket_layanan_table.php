<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paket_layanan', function (Blueprint $table) {
            $table->id('id_paket');
            $table->string('nama_paket', 50);
            $table->double('harga_per_kg', 10, 2);
            $table->integer('estimasi'); // dalam jam
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paket_layanan');
    }
};
