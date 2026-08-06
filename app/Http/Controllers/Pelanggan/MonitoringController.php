<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    /**
     * Mendapatkan Status Real-Time 6 Slot Mesin Cuci (1 Mesin = 1 Konsumen)
     * Sinkron 100% berdasarkan kolom permanen no_mesin_cuci di basis data.
     */
    private function getMesinCuciStatus(): array
    {
        if (!Schema::hasTable('transaksi')) {
            $slots = [];
            for ($i = 1; $i <= 6; $i++) {
                $slots[] = [
                    'id_mesin' => $i,
                    'nama_mesin' => 'Mesin ' . $i,
                    'status' => 'KOSONG',
                    'no_nota' => '-',
                    'id_transaksi' => null,
                    'pelanggan_nama' => '-',
                    'status_pengerjaan' => 'Siap Pakai',
                    'estimasi_selesai' => '-',
                ];
            }
            return $slots;
        }

        $now = Carbon::now();

        $cuciTxList = Transaksi::with('pelanggan.user')
            ->where('status_pengerjaan', 'Cuci')
            ->whereNotNull('no_mesin_cuci')
            ->where(function($q) use ($now) {
                $q->whereNull('estimasi_selesai')
                  ->orWhere('estimasi_selesai', '>', $now);
            })
            ->get();

        $slots = [];
        for ($i = 1; $i <= 6; $i++) {
            $tx = $cuciTxList->firstWhere('no_mesin_cuci', $i);
            if ($tx) {
                $slots[] = [
                    'id_mesin' => $i,
                    'nama_mesin' => 'Mesin ' . $i,
                    'status' => 'TERPAKAI',
                    'no_nota' => $tx->no_nota,
                    'id_transaksi' => $tx->id_transaksi,
                    'pelanggan_nama' => $tx->pelanggan->user->nama ?? 'Pelanggan',
                    'status_pengerjaan' => $tx->status_pengerjaan,
                    'estimasi_selesai' => $tx->estimasi_selesai ? $tx->estimasi_selesai->format('H:i') . ' WIB' : '-',
                ];
            } else {
                $slots[] = [
                    'id_mesin' => $i,
                    'nama_mesin' => 'Mesin ' . $i,
                    'status' => 'KOSONG',
                    'no_nota' => '-',
                    'id_transaksi' => null,
                    'pelanggan_nama' => '-',
                    'status_pengerjaan' => 'Siap Pakai',
                    'estimasi_selesai' => '-',
                ];
            }
        }
        return $slots;
    }

    public function index(Request $request)
    {
        $searchQuery = trim($request->query('nota', ''));
        $activeTransaction = null;
        $matchingTransactions = collect();
        $mesinCuciList = $this->getMesinCuciStatus();

        if ($searchQuery !== '' && Schema::hasTable('transaksi')) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $searchQuery);

            // Cari berdasarkan Nomor Nota ATAU Nomor Telepon / WA Pelanggan
            $query = Transaksi::with(['pelanggan.user', 'detailTransaksi.paketLayanan', 'prediksiAnalisis'])
                ->where(function($q) use ($searchQuery, $cleanPhone) {
                    $q->where('no_nota', 'like', "%{$searchQuery}%")
                      ->orWhereHas('pelanggan', function($pq) use ($searchQuery, $cleanPhone) {
                          $pq->where('no_telepon', 'like', "%{$searchQuery}%");
                          if (!empty($cleanPhone) && strlen($cleanPhone) >= 3) {
                              $pq->orWhere('no_telepon', 'like', "%{$cleanPhone}%");
                          }
                      });
                });

            $matchingTransactions = $query->orderBy('created_at', 'desc')->get();

            if ($matchingTransactions->isNotEmpty()) {
                if ($request->has('id')) {
                    $selectedId = $request->query('id');
                    $activeTransaction = $matchingTransactions->firstWhere('id_transaksi', $selectedId) ?? $matchingTransactions->first();
                } else {
                    $activeTransaction = $matchingTransactions->first();
                }
            }
        }

        $riwayatTransaksi = collect();
        if (Auth::check() && Auth::user()->role === 'pelanggan' && Schema::hasTable('transaksi')) {
            $pelanggan = Auth::user()->pelanggan;
            if ($pelanggan) {
                $riwayatTransaksi = Transaksi::with(['detailTransaksi.paketLayanan', 'prediksiAnalisis'])
                    ->where('id_pelanggan', $pelanggan->id_pelanggan)
                    ->orderBy('created_at', 'desc')
                    ->get();

                if (!$activeTransaction && $riwayatTransaksi->isNotEmpty()) {
                    $activeTransaction = $riwayatTransaksi->first();
                }
            }
        }

        return view('pelanggan.monitoring', compact('activeTransaction', 'matchingTransactions', 'riwayatTransaksi', 'searchQuery', 'mesinCuciList'));
    }

    public function riwayat()
    {
        if (!Auth::check() || Auth::user()->role !== 'pelanggan') {
            return redirect()->route('monitoring');
        }

        $pelanggan = Auth::user()->pelanggan;
        $riwayatTransaksi = collect();

        if ($pelanggan && Schema::hasTable('transaksi')) {
            $riwayatTransaksi = Transaksi::with(['detailTransaksi.paketLayanan', 'prediksiAnalisis'])
                ->where('id_pelanggan', $pelanggan->id_pelanggan)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        return view('pelanggan.riwayat', compact('riwayatTransaksi'));
    }
}
