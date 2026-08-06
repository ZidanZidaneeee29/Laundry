<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Pelanggan;
use App\Models\User;
use App\Models\PaketLayanan;
use App\Models\PrediksiAnalisis;
use App\Services\RandomForestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TransaksiController extends Controller
{
    protected RandomForestService $rfService;

    public function __construct(RandomForestService $rfService)
    {
        $this->rfService = $rfService;
    }

    /**
     * Mencari slot mesin cuci nomor terkecil (1..6) yang sedang KOSONG.
     */
    private function findAvailableMachineSlot(): ?int
    {
        if (!Schema::hasTable('transaksi')) {
            return 1;
        }

        $now = Carbon::now();
        $occupiedSlots = Transaksi::where('status_pengerjaan', 'Cuci')
            ->whereNotNull('no_mesin_cuci')
            ->where(function($q) use ($now) {
                $q->whereNull('estimasi_selesai')
                  ->orWhere('estimasi_selesai', '>', $now);
            })
            ->pluck('no_mesin_cuci')
            ->toArray();

        for ($slot = 1; $slot <= 6; $slot++) {
            if (!in_array($slot, $occupiedSlots)) {
                return $slot;
            }
        }

        return null;
    }

    /**
     * Sinkronisasi Otomatis Status Pengerjaan di Basis Data (6 Mesin Cuci Paralel FIFO)
     */
    private function syncRealTimeStatusToDatabase(): void
    {
        if (!Schema::hasTable('transaksi')) {
            return;
        }

        $now = Carbon::now();

        // 1. Update transaksi yang estimasi selesainya <= waktu sekarang menjadi 'Selesai'
        Transaksi::where('status_pengerjaan', '!=', 'Selesai')
            ->whereNotNull('estimasi_selesai')
            ->where('estimasi_selesai', '<=', $now)
            ->update(['status_pengerjaan' => 'Selesai']);

        // 2. Ambil seluruh transaksi belum selesai
        $allActiveTxList = Transaksi::where('status_pengerjaan', '!=', 'Selesai')
            ->whereNotNull('tgl_masuk')
            ->whereNotNull('estimasi_selesai')
            ->orderBy('created_at', 'asc')
            ->get();

        $levels = [
            'Antre' => 1,
            'Cuci' => 2,
            'Kering' => 3,
            'Setrika' => 4,
            'Selesai' => 5,
        ];

        foreach ($allActiveTxList as $tx) {
            $rawStatus = $tx->getRawOriginal('status_pengerjaan');
            $rawLevel = $levels[$rawStatus] ?? 1;

            $totalSec = max(1, $tx->tgl_masuk->diffInSeconds($tx->estimasi_selesai));
            $elapsedSec = max(0, $tx->tgl_masuk->diffInSeconds($now, false));
            $pct = ($elapsedSec / $totalSec) * 100;

            if ($pct >= 75) {
                $targetStatus = 'Setrika';
            } elseif ($pct >= 45) {
                $targetStatus = 'Kering';
            } else {
                // Untuk persentase cuci (< 45%):
                // Jika pesanan belum pegang slot mesin cuci atau statusnya Antre, alokasikan slot bebas
                if (!$tx->no_mesin_cuci || $rawStatus === 'Antre') {
                    $availableSlot = $this->findAvailableMachineSlot();
                    if ($availableSlot !== null) {
                        $tx->no_mesin_cuci = $availableSlot;
                        $targetStatus = 'Cuci';
                    } else {
                        $targetStatus = 'Antre';
                    }
                } else {
                    $targetStatus = 'Cuci';
                }
            }

            $targetLevel = $levels[$targetStatus] ?? 1;

            if ($targetLevel > $rawLevel && $rawStatus !== 'Selesai') {
                $tx->status_pengerjaan = $targetStatus;
            }
            $tx->save();
        }
    }

    /**
     * Menghitung total transaksi yang sedang AKTIF menggunakan 6 Mesin Cuci.
     */
    private function getTotalMesinTerpakaiCount(): int
    {
        if (!Schema::hasTable('transaksi')) {
            return 0;
        }

        $now = Carbon::now();
        return Transaksi::where('status_pengerjaan', 'Cuci')
            ->whereNotNull('no_mesin_cuci')
            ->where(function($q) use ($now) {
                $q->whereNull('estimasi_selesai')
                  ->orWhere('estimasi_selesai', '>', $now);
            })
            ->count();
    }

    /**
     * Menghitung jumlah antrean tunggu (hanya bertambah jika 6 mesin sudah 100% full).
     */
    private function getJumlahAntreanTunggu(): int
    {
        if (!Schema::hasTable('transaksi')) {
            return 0;
        }

        $now = Carbon::now();
        $totalAntre = Transaksi::where('status_pengerjaan', 'Antre')
            ->where(function($q) use ($now) {
                $q->whereNull('estimasi_selesai')
                  ->orWhere('estimasi_selesai', '>', $now);
            })
            ->count();

        return $totalAntre;
    }

    /**
     * Mendapatkan Status Real-Time 6 Slot Mesin Cuci (1 Mesin = 1 Konsumen)
     * Tersinkronisasi persis berdasarkan nomor slot mesin permanen pada basis data.
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
        $this->syncRealTimeStatusToDatabase();

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
                  })
                  ->orWhereHas('pelanggan', function($qp) use ($search) {
                      $qp->where('no_telepon', 'like', "%{$search}%");
                  });
            });
        }

        $transaksi = $query->paginate(10);
        return view('kasir.transaksi.index', compact('transaksi'));
    }

    public function create()
    {
        $this->syncRealTimeStatusToDatabase();

        $paketList = PaketLayanan::all();
        $totalTerpakai = $this->getTotalMesinTerpakaiCount();
        $antreanSaatIni = $this->getJumlahAntreanTunggu();
        $mesinCuciList = $this->getMesinCuciStatus();

        return view('kasir.transaksi.create', compact('paketList', 'antreanSaatIni', 'totalTerpakai', 'mesinCuciList'));
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
            'nama_pelanggan' => 'required|string|max:100',
            'no_telepon' => 'required|string|max:20',
            'alamat' => 'required|string|max:255',
            'id_paket' => 'required|exists:paket_layanan,id_paket',
            'berat_qty' => 'required|numeric|min:0.5',
            'kategori_pakaian' => 'required|string',
        ]);

        // 1. Cari atau buat Data Pelanggan secara otomatis berdasarkan No. Telepon
        $cleanPhone = preg_replace('/[^0-9]/', '', $request->no_telepon);
        $pelanggan = Pelanggan::where('no_telepon', $request->no_telepon)
            ->orWhere('no_telepon', $cleanPhone)
            ->first();

        if (!$pelanggan) {
            $uniq = time() . '_' . rand(100, 999);
            $user = User::create([
                'nama' => $request->nama_pelanggan,
                'username' => 'pelanggan_' . $uniq,
                'email' => 'pelanggan_' . $uniq . '@sindory.local',
                'password' => Hash::make(Str::random(16)),
                'role' => 'pelanggan',
            ]);

            $pelanggan = Pelanggan::create([
                'id_user' => $user->id_user,
                'no_telepon' => $request->no_telepon,
                'alamat' => $request->alamat,
            ]);
        } else {
            if ($pelanggan->user) {
                $pelanggan->user->nama = $request->nama_pelanggan;
                $pelanggan->user->save();
            }
            $pelanggan->alamat = $request->alamat;
            $pelanggan->save();
        }

        $paket = PaketLayanan::findOrFail($request->id_paket);
        $antreanSaatIni = $this->getJumlahAntreanTunggu();

        // 2. Jalankan Komputasi Random Forest
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

        // 3. Simpan Transaksi (Cari slot mesin cuci bebas nomor terkecil 1..6)
        $slotToAssign = $this->findAvailableMachineSlot();
        $initialStatus = ($slotToAssign !== null) ? 'Cuci' : 'Antre';

        $transaksi = Transaksi::create([
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'id_kasir' => Auth::user()->id_user,
            'no_nota' => $noNota,
            'tgl_masuk' => $tglMasuk,
            'total_bayar' => $totalBayar,
            'status_pengerjaan' => $initialStatus,
            'no_mesin_cuci' => $slotToAssign,
            'estimasi_selesai' => $estimasiSelesai,
        ]);

        // 4. Simpan Detail Transaksi
        DetailTransaksi::create([
            'id_transaksi' => $transaksi->id_transaksi,
            'id_paket' => $paket->id_paket,
            'berat_qty' => $request->berat_qty,
            'kategori_pakaian' => $request->kategori_pakaian,
            'subtotal' => $totalBayar,
        ]);

        // 5. Simpan Prediksi Analisis (Random Forest Meta Data)
        PrediksiAnalisis::create([
            'id_transaksi' => $transaksi->id_transaksi,
            'model_version' => $rfResult['model_version'] ?? 'RF-Reg-v1.0',
            'confidence_score' => $rfResult['confidence_score'] ?? 0.95,
            'jumlah_antrean' => $antreanSaatIni,
            'durasi_estimasi_jam' => $durasiJam,
            'detail_pohon_json' => $rfResult['tree_predictions'] ?? [],
        ]);

        return redirect()->route('kasir.transaksi.show', $transaksi->id_transaksi)
            ->with('success', 'Transaksi berhasil disimpan!');
    }

    public function show($id)
    {
        $this->syncRealTimeStatusToDatabase();

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

        return back()->with('success', "Status pengerjaan nota {$transaksi->no_nota} berhasil diubah menjadi {$transaksi->status_pengerjaan}.");
    }
}
