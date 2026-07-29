@extends('layouts.app')

@section('title', 'Detail Nota & Transparansi Random Forest - Indo Express Laundry')

@section('content')
<div class="row mb-3 d-print-none">
    <div class="col-md-6">
        <a href="{{ route('kasir.transaksi.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Transaksi
        </a>
    </div>
    <div class="col-md-6 text-end">
        <button onclick="window.print()" class="btn btn-dark">
            <i class="bi bi-printer me-1"></i> Cetak Nota Transaksi
        </button>
    </div>
</div>

<div class="row">
    <!-- Invoice Card -->
    <div class="col-md-7 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ asset('images/logo.png') }}" alt="Indo Express Laundry Logo" height="55" class="rounded border p-1">
                        <div>
                            <h4 class="fw-bold text-primary mb-0">INDO EXPRESS LAUNDRY</h4>
                            <small class="text-muted">75 Menit Selesai &bull; Jl. Raya Laundry No. 88, Bandung</small>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-primary fs-6 mb-1">{{ $transaksi->no_nota }}</span>
                        <div class="small text-muted">{{ $transaksi->tgl_masuk->format('d/m/Y H:i') }} WIB</div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <small class="text-uppercase text-muted fw-bold">Pelanggan:</small>
                        <h6 class="fw-bold mb-1">{{ $transaksi->pelanggan->user->nama ?? '-' }}</h6>
                        <small class="text-muted d-block">Telp: {{ $transaksi->pelanggan->no_telepon ?? '-' }}</small>
                        <small class="text-muted d-block">Alamat: {{ $transaksi->pelanggan->alamat ?? '-' }}</small>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-uppercase text-muted fw-bold">Kasir Bertugas:</small>
                        <h6 class="fw-bold mb-1">{{ $transaksi->kasir->nama ?? '-' }}</h6>
                        <small class="text-uppercase text-muted fw-bold d-block mt-2">Status Pengerjaan:</small>
                        <span class="badge bg-success fs-6">{{ $transaksi->status_pengerjaan }}</span>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Paket Layanan</th>
                                <th>Kategori Pakaian</th>
                                <th>Berat (KG)</th>
                                <th>Harga / KG</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transaksi->detailTransaksi as $dt)
                                <tr>
                                    <td><strong>{{ $dt->paketLayanan->nama_paket ?? '-' }}</strong></td>
                                    <td>{{ $dt->kategori_pakaian }}</td>
                                    <td>{{ $dt->berat_qty }} KG</td>
                                    <td>Rp {{ number_format($dt->paketLayanan->harga_per_kg ?? 0, 0, ',', '.') }}</td>
                                    <td><strong class="text-success">Rp {{ number_format($dt->subtotal, 0, ',', '.') }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end fw-bold fs-6">TOTAL BAYAR:</td>
                                <td><span class="fs-5 fw-bold text-success">Rp {{ number_format($transaksi->total_bayar, 0, ',', '.') }}</span></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="p-3 bg-light rounded border text-center">
                    <small class="text-uppercase text-muted fw-bold d-block mb-1">ESTIMASI SELESAI PENGERJAAN (POWERED BY RANDOM FOREST):</small>
                    <div class="fs-4 fw-bold text-primary">
                        <i class="bi bi-clock-history me-2"></i>
                        {{ $transaksi->estimasi_selesai ? $transaksi->estimasi_selesai->format('d M Y, H:i') : '-' }} WIB
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Random Forest Transparent Workflow Card -->
    <div class="col-md-5 d-print-none">
        <div class="card shadow-sm border-0 bg-dark text-white">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-cpu me-2"></i> Transparansi Komputasi Random Forest</h5>
            </div>
            <div class="card-body p-4">
                @php
                    $pred = $transaksi->prediksiAnalisis;
                    $trees = $pred && is_array($pred->detail_pohon_json) ? $pred->detail_pohon_json : [];
                @endphp

                <div class="mb-3 border-bottom pb-2">
                    <small class="text-uppercase text-info fw-bold">Tahap 1: Preprocessing & Encoding</small>
                    <p class="small text-light mb-1 mt-1">
                        Variabel kategorikal (Layanan & Kategori Pakaian) diubah menjadi nilai numerik matematis.
                    </p>
                    <div class="p-2 bg-secondary rounded extra-small">
                        Antrean Saat Itu: <strong>{{ $pred->jumlah_antrean ?? 0 }} Pesanan</strong><br>
                        Versi Model: <strong>{{ $pred->model_version ?? 'RF-Reg-v1.0' }}</strong>
                    </div>
                </div>

                <div class="mb-3 border-bottom pb-2">
                    <small class="text-uppercase text-info fw-bold">Tahap 2: Evaluasi {{ count($trees) }} Pohon Keputusan (Tree 1..N)</small>
                    <p class="small text-light mb-1 mt-1">Setiap pohon menghitung estimasi durasi individu ($y_1, y_2, \dots, y_n$ dalam jam):</p>
                    <div class="p-2 bg-secondary rounded extra-small text-monospace" style="max-height: 120px; overflow-y: auto;">
                        @foreach($trees as $idx => $y_val)
                            <span class="badge bg-dark text-info mb-1">Tree {{ $idx+1 }}: {{ $y_val }} jam</span>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3">
                    <small class="text-uppercase text-info fw-bold">Tahap 3: Agregasi Hasil (Averaging)</small>
                    <p class="small text-light mb-1 mt-1">Rata-rata dari seluruh prediksi pohon regresi:</p>
                    <div class="d-flex align-items-center justify-content-between p-3 bg-secondary rounded">
                        <div>
                            <small class="text-muted d-block">Estimasi Final Rata-Rata:</small>
                            <strong class="fs-4 text-warning">{{ $pred->durasi_estimasi_jam ?? 0 }} Jam</strong>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">Confidence Score:</small>
                            <span class="badge bg-success fs-6">{{ round(($pred->confidence_score ?? 0.95) * 100, 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
