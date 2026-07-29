<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Pelanggan;
use App\Models\PaketLayanan;
use App\Models\PrediksiAnalisis;
use App\Services\RandomForestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransaksiController extends Controller
{
    protected RandomForestService $rfService;

    public function __construct(RandomForestService $rfService)
    {
        $this->rfService = $rfService;
    }

    public function index(Request $request)
    {
        $query = Transaksi::with(['pelanggan.user', 'kasir', 'detailTransaksi.paketLayanan', 'prediksiAnalisis'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status_pengerjaan', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_nota', 'like', "%{$search}%")
                  ->orWhereHas('pelanggan.user', function($qu) use ($search) {
                      $qu->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        $transaksi = $query->paginate(10);
        return view('kasir.transaksi.index', compact('transaksi'));
    }

    public function create()
    {
        $pelangganList = Pelanggan::with('user')->get();
        $paketList = PaketLayanan::all();
        $antreanSaatIni = Transaksi::whereIn('status_pengerjaan', ['Antre', 'Cuci', 'Kering', 'Setrika'])->count();

        return view('kasir.transaksi.create', compact('pelangganList', 'paketList', 'antreanSaatIni'));
    }

    public function predictApi(Request $request)
    {
        $request->validate([
            'id_paket' => 'required|exists:paket_layanan,id_paket',
            'berat_qty' => 'required|numeric|min:0.5',
            'kategori_pakaian' => 'required|string',
            'jumlah_antrean' => 'required|integer|min:0',
        ]);

        $paket = PaketLayanan::findOrFail($request->id_paket);
        $result = $this->rfService->predictDuration(
            (float)$request->berat_qty,
            $paket->nama_paket,
            $request->kategori_pakaian,
            (int)$request->jumlah_antrean
        );

        $estimasiSelesai = Carbon::now()->addHours($result['predicted_duration_hours'])->format('d M Y, H:i');
        $result['estimasi_selesai_formatted'] = $estimasiSelesai;
        $result['subtotal'] = round($paket->harga_per_kg * $request->berat_qty, 2);

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',
            'id_paket' => 'required|exists:paket_layanan,id_paket',
            'berat_qty' => 'required|numeric|min:0.5',
            'kategori_pakaian' => 'required|string',
        ]);

        $paket = PaketLayanan::findOrFail($request->id_paket);
        $antreanSaatIni = Transaksi::whereIn('status_pengerjaan', ['Antre', 'Cuci', 'Kering', 'Setrika'])->count();

        // 1. Jalankan Komputasi Random Forest
        $rfResult = $this->rfService->predictDuration(
            (float)$request->berat_qty,
            $paket->nama_paket,
            $request->kategori_pakaian,
            $antreanSaatIni
        );

        $tglMasuk = Carbon::now();
        $durasiJam = (float)$rfResult['predicted_duration_hours'];
        $estimasiSelesai = (clone $tglMasuk)->addMinutes((int)round($durasiJam * 60));
        $totalBayar = $paket->harga_per_kg * $request->berat_qty;
        $noNota = 'EXP-' . date('Ymd') . '-' . str_pad(Transaksi::whereDate('created_at', date('Y-m-d'))->count() + 1, 3, '0', STR_PAD_LEFT);

        // 2. Simpan Transaksi
        $transaksi = Transaksi::create([
            'id_pelanggan' => $request->id_pelanggan,
            'id_kasir' => Auth::user()->id_user,
            'no_nota' => $noNota,
            'tgl_masuk' => $tglMasuk,
            'total_bayar' => $totalBayar,
            'status_pengerjaan' => 'Antre',
            'estimasi_selesai' => $estimasiSelesai,
        ]);

        // 3. Simpan Detail Transaksi
        DetailTransaksi::create([
            'id_transaksi' => $transaksi->id_transaksi,
            'id_paket' => $paket->id_paket,
            'berat_qty' => $request->berat_qty,
            'kategori_pakaian' => $request->kategori_pakaian,
            'subtotal' => $totalBayar,
        ]);

        // 4. Simpan Prediksi Analisis (Random Forest Meta Data)
        PrediksiAnalisis::create([
            'id_transaksi' => $transaksi->id_transaksi,
            'model_version' => $rfResult['model_version'] ?? 'RF-Reg-v1.0',
            'confidence_score' => $rfResult['confidence_score'] ?? 0.95,
            'jumlah_antrean' => $antreanSaatIni,
            'durasi_estimasi_jam' => $durasiJam,
            'detail_pohon_json' => $rfResult['tree_predictions'] ?? [],
        ]);

        return redirect()->route('kasir.transaksi.show', $transaksi->id_transaksi)
            ->with('success', 'Transaksi berhasil dibuat dengan estimasi Random Forest!');
    }

    public function show($id)
    {
        $transaksi = Transaksi::with(['pelanggan.user', 'kasir', 'detailTransaksi.paketLayanan', 'prediksiAnalisis'])
            ->findOrFail($id);

        return view('kasir.transaksi.show', compact('transaksi'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status_pengerjaan' => 'required|in:Antre,Cuci,Kering,Setrika,Selesai',
        ]);

        $transaksi = Transaksi::findOrFail($id);
        $transaksi->status_pengerjaan = $request->status_pengerjaan;
        $transaksi->save();

        return back()->with('success', "Status pengerjaan nota {$transaksi->no_nota} diubah menjadi {$transaksi->status_pengerjaan}.");
    }
}
