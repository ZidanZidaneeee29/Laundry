<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonitoringController extends Controller
{
    public function index(Request $request)
    {
        $searchNota = $request->query('nota');
        $activeTransaction = null;

        if ($searchNota) {
            $activeTransaction = Transaksi::with(['pelanggan.user', 'detailTransaksi.paketLayanan', 'prediksiAnalisis'])
                ->where('no_nota', trim($searchNota))
                ->first();
        }

        $riwayatTransaksi = collect();
        if (Auth::check() && Auth::user()->role === 'pelanggan') {
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

        return view('pelanggan.monitoring', compact('activeTransaction', 'riwayatTransaksi', 'searchNota'));
    }

    public function riwayat()
    {
        if (!Auth::check() || Auth::user()->role !== 'pelanggan') {
            return redirect()->route('monitoring');
        }

        $pelanggan = Auth::user()->pelanggan;
        $riwayatTransaksi = collect();

        if ($pelanggan) {
            $riwayatTransaksi = Transaksi::with(['detailTransaksi.paketLayanan', 'prediksiAnalisis'])
                ->where('id_pelanggan', $pelanggan->id_pelanggan)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        return view('pelanggan.riwayat', compact('riwayatTransaksi'));
    }
}
