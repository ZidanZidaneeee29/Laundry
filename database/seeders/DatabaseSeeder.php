<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Pelanggan;
use App\Models\PaketLayanan;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\PrediksiAnalisis;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Kasir
        $kasir = User::create([
            'nama' => 'Kasir Express',
            'username' => 'kasir',
            'email' => 'kasir@laundry.com',
            'password' => Hash::make('password123'),
            'role' => 'kasir',
        ]);

        // 2. Akun Pemilik
        $pemilik = User::create([
            'nama' => 'Pemilik Outlet',
            'username' => 'pemilik',
            'email' => 'pemilik@laundry.com',
            'password' => Hash::make('password123'),
            'role' => 'pemilik',
        ]);

        // 3. Akun Pelanggan Demo
        $userPelanggan1 = User::create([
            'nama' => 'Budi Santoso',
            'username' => 'pelanggan',
            'email' => 'pelanggan@laundry.com',
            'password' => Hash::make('password123'),
            'role' => 'pelanggan',
        ]);

        $pelanggan1 = Pelanggan::create([
            'id_user' => $userPelanggan1->id_user,
            'no_telepon' => '081234567890',
            'alamat' => 'Jl. Merdeka No. 12, Bandung',
        ]);

        $userPelanggan2 = User::create([
            'nama' => 'Siti Rahma',
            'username' => 'siti',
            'email' => 'siti@laundry.com',
            'password' => Hash::make('password123'),
            'role' => 'pelanggan',
        ]);

        $pelanggan2 = Pelanggan::create([
            'id_user' => $userPelanggan2->id_user,
            'no_telepon' => '089876543210',
            'alamat' => 'Jl. Mawar No. 45, Bandung',
        ]);

        // 4. Paket Layanan
        $paket1 = PaketLayanan::create([
            'nama_paket' => 'Cuci Komplit',
            'harga_per_kg' => 7000,
            'estimasi' => 24,
        ]);

        $paket2 = PaketLayanan::create([
            'nama_paket' => 'Cuci Kering',
            'harga_per_kg' => 5000,
            'estimasi' => 12,
        ]);

        $paket3 = PaketLayanan::create([
            'nama_paket' => 'Setrika Saja',
            'harga_per_kg' => 4000,
            'estimasi' => 12,
        ]);

        $paket4 = PaketLayanan::create([
            'nama_paket' => 'Express 6 Jam',
            'harga_per_kg' => 15000,
            'estimasi' => 6,
        ]);

        $paket5 = PaketLayanan::create([
            'nama_paket' => 'Bedcover & Selimut',
            'harga_per_kg' => 25000,
            'estimasi' => 24,
        ]);

        // 5. Transaksi Sampel (Aktif dalam proses)
        $tglMasuk = Carbon::now();
        $estimasiSelesai = Carbon::now()->addHours(6);

        $trans1 = Transaksi::create([
            'id_pelanggan' => $pelanggan1->id_pelanggan,
            'id_kasir' => $kasir->id_user,
            'no_nota' => 'EXP-' . date('Ymd') . '-001',
            'tgl_masuk' => $tglMasuk,
            'total_bayar' => 35000,
            'status_pengerjaan' => 'Cuci',
            'estimasi_selesai' => $estimasiSelesai,
        ]);

        DetailTransaksi::create([
            'id_transaksi' => $trans1->id_transaksi,
            'id_paket' => $paket1->id_paket,
            'berat_qty' => 5.0,
            'kategori_pakaian' => 'Pakaian Harian',
            'subtotal' => 35000,
        ]);

        // Detail Pohon Keputusan RF
        $treePredictions = [];
        for ($i = 0; $i < 50; $i++) {
            $treePredictions[] = round(5.5 + ($i % 3) * 0.4 - 0.2, 2);
        }

        PrediksiAnalisis::create([
            'id_transaksi' => $trans1->id_transaksi,
            'model_version' => 'RF-Reg-v1.0',
            'confidence_score' => 0.9650,
            'jumlah_antrean' => 2,
            'durasi_estimasi_jam' => 6.0,
            'detail_pohon_json' => $treePredictions,
        ]);

        // Transaksi Sampel 2 (Selesai)
        $trans2 = Transaksi::create([
            'id_pelanggan' => $pelanggan2->id_pelanggan,
            'id_kasir' => $kasir->id_user,
            'no_nota' => 'EXP-' . date('Ymd') . '-002',
            'tgl_masuk' => Carbon::now()->subDays(1),
            'total_bayar' => 21000,
            'status_pengerjaan' => 'Selesai',
            'estimasi_selesai' => Carbon::now()->subHours(12),
        ]);

        DetailTransaksi::create([
            'id_transaksi' => $trans2->id_transaksi,
            'id_paket' => $paket1->id_paket,
            'berat_qty' => 3.0,
            'kategori_pakaian' => 'Pakaian Harian',
            'subtotal' => 21000,
        ]);

        PrediksiAnalisis::create([
            'id_transaksi' => $trans2->id_transaksi,
            'model_version' => 'RF-Reg-v1.0',
            'confidence_score' => 0.9780,
            'jumlah_antrean' => 1,
            'durasi_estimasi_jam' => 5.2,
            'detail_pohon_json' => $treePredictions,
        ]);
    }
}
