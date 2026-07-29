<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\PrediksiAnalisis;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPendapatan = Transaksi::sum('total_bayar');
        $totalTransaksi = Transaksi::count();
        $transaksiAktif = Transaksi::whereIn('status_pengerjaan', ['Antre', 'Cuci', 'Kering', 'Setrika'])->count();
        $transaksiSelesai = Transaksi::where('status_pengerjaan', 'Selesai')->count();

        $avgConfidence = PrediksiAnalisis::avg('confidence_score');
        $avgConfidencePercent = round(($avgConfidence ?? 0.95) * 100, 1);

        $latestTransactions = Transaksi::with(['pelanggan.user', 'detailTransaksi.paketLayanan', 'prediksiAnalisis'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('pemilik.dashboard', compact(
            'totalPendapatan',
            'totalTransaksi',
            'transaksiAktif',
            'transaksiSelesai',
            'avgConfidencePercent',
            'latestTransactions'
        ));
    }

    public function monitoring(Request $request)
    {
        $query = Transaksi::with(['pelanggan.user', 'kasir', 'detailTransaksi.paketLayanan', 'prediksiAnalisis'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status_pengerjaan', $request->status);
        }

        $transaksiList = $query->paginate(10);
        return view('pemilik.monitoring', compact('transaksiList'));
    }

    public function laporan(Request $request)
    {
        $tglMulai = $request->input('tgl_mulai', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $tglSelesai = $request->input('tgl_selesai', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $transaksiList = Transaksi::with(['pelanggan.user', 'kasir', 'detailTransaksi.paketLayanan', 'prediksiAnalisis'])
            ->whereDate('tgl_masuk', '>=', $tglMulai)
            ->whereDate('tgl_masuk', '<=', $tglSelesai)
            ->orderBy('tgl_masuk', 'desc')
            ->get();

        $totalOmset = $transaksiList->sum('total_bayar');
        $totalKg = $transaksiList->sum(function($t) {
            return $t->detailTransaksi->sum('berat_qty');
        });

        return view('pemilik.laporan', compact('transaksiList', 'tglMulai', 'tglSelesai', 'totalOmset', 'totalKg'));
    }
}
