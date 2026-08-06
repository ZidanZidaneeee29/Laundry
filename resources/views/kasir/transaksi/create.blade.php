@extends('layouts.app')

@section('title', 'Input Pesanan Baru - SINDORY')

@section('content')
<!-- Status 6 Mesin Cuci Real-Time Grid -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-water text-primary me-2"></i> Status Operasional 6 Mesin Cuci (1 Mesin = 1 Konsumen)</h6>
                    <small class="text-muted">Setiap mesin melayani 1 nota konsumen secara paralel tanpa mencampur pakaian.</small>
                </div>
                <span class="badge bg-primary px-3 py-2 rounded-pill"><i class="bi bi-cpu me-1"></i> Total: 6 Mesin Cuci</span>
            </div>
            <div class="card-body p-3">
                <div class="row g-2">
                    @foreach($mesinCuciList as $m)
                        <div class="col-md-4 col-lg-2">
                            <div class="p-3 rounded border text-center {{ $m['status'] === 'TERPAKAI' ? 'bg-primary bg-opacity-10 border-primary' : 'bg-light border-secondary border-opacity-25' }}">
                                <div class="fs-4 mb-1">
                                    <i class="bi {{ $m['status'] === 'TERPAKAI' ? 'bi-arrow-repeat text-primary spin' : 'bi-check-circle text-success' }}"></i>
                                </div>
                                <strong class="d-block text-dark small mb-1">{{ $m['nama_mesin'] }}</strong>
                                @if($m['status'] === 'TERPAKAI')
                                    <span class="badge bg-primary mb-1 extra-small">{{ $m['no_nota'] }}</span>
                                    <div class="extra-small text-truncate text-muted">{{ $m['pelanggan_nama'] }}</div>
                                    <div class="extra-small fw-bold text-primary mt-1"><i class="bi bi-clock me-1"></i>Selesai: {{ $m['estimasi_selesai'] }}</div>
                                @else
                                    <span class="badge bg-success mb-1 extra-small">KOSONG</span>
                                    <div class="extra-small text-muted">Siap Digunakan</div>
                                    <div class="extra-small fw-bold text-success mt-1">&bull; Standby</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-cart-plus me-2"></i> Input Data Pesanan Baru</h5>
            </div>
            <div class="card-body p-4">
                <form id="form-transaksi" action="{{ route('kasir.transaksi.store') }}" method="POST">
                    @csrf
                    
                    <!-- Form Input Manual Data Pelanggan -->
                    <div class="card bg-light border p-3 mb-4 rounded-3">
                        <small class="text-uppercase text-primary fw-bold mb-2 d-block">
                            <i class="bi bi-person-plus-fill me-1"></i> Data Pelanggan Baru / Otomatis
                        </small>
                        
                        <div class="mb-3">
                            <label for="nama_pelanggan" class="form-label font-semibold">Nama Lengkap Pelanggan</label>
                            <input type="text" name="nama_pelanggan" id="nama_pelanggan" class="form-control @error('nama_pelanggan') is-invalid @enderror" value="{{ old('nama_pelanggan') }}" required placeholder="Contoh: Budi Santoso" autofocus>
                            @error('nama_pelanggan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="no_telepon" class="form-label font-semibold">No. Telepon / WA</label>
                            <input type="text" name="no_telepon" id="no_telepon" class="form-control @error('no_telepon') is-invalid @enderror" value="{{ old('no_telepon') }}" required placeholder="Contoh: 08574635251">
                            @error('no_telepon') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-0">
                            <label for="alamat" class="form-label font-semibold">Alamat Lengkap</label>
                            <input type="text" name="alamat" id="alamat" class="form-control @error('alamat') is-invalid @enderror" value="{{ old('alamat') }}" required placeholder="Contoh: Jl. Merdeka No. 12, Surabaya">
                            @error('alamat') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Form Rincian Layanan Cucian -->
                    <div class="mb-3">
                        <label for="id_paket" class="form-label font-semibold">Jenis Layanan Paket</label>
                        <select name="id_paket" id="id_paket" class="form-select @error('id_paket') is-invalid @enderror" required>
                            <option value="">-- Pilih Paket Layanan --</option>
                            @foreach($paketList as $pkt)
                                <option value="{{ $pkt->id_paket }}" data-harga="{{ $pkt->harga_per_kg }}" {{ old('id_paket') == $pkt->id_paket ? 'selected' : '' }}>
                                    {{ $pkt->nama_paket }} (Rp {{ number_format($pkt->harga_per_kg, 0, ',', '.') }} / KG)
                                </option>
                            @endforeach
                        </select>
                        @error('id_paket') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="kategori_pakaian" class="form-label font-semibold">Kategori Pakaian</label>
                            <select name="kategori_pakaian" id="kategori_pakaian" class="form-select @error('kategori_pakaian') is-invalid @enderror" required>
                                <option value="Pakaian Harian">Pakaian Harian</option>
                                <option value="Pakaian Tebal / Jaket">Pakaian Tebal / Jaket</option>
                                <option value="Jas & Gaun">Jas & Gaun</option>
                                <option value="Sprei & Gorden">Sprei & Gorden</option>
                            </select>
                            @error('kategori_pakaian') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="berat_qty" class="form-label font-semibold">Berat (KG)</label>
                            <input type="number" step="0.1" min="0.5" class="form-control @error('berat_qty') is-invalid @enderror" id="berat_qty" name="berat_qty" value="{{ old('berat_qty', 2.0) }}" required>
                            @error('berat_qty') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="jumlah_antrean" class="form-label font-semibold">Jumlah Antrean Tunggu Saat Ini</label>
                        <input type="number" class="form-control" id="jumlah_antrean" name="jumlah_antrean" value="{{ $antreanSaatIni }}" readonly>
                        @if(($totalTerpakai ?? 0) < 6)
                            <small class="text-success d-block mt-1">
                                <i class="bi bi-check-circle-fill me-1"></i> Mesin Cuci belum penuh ({{ $totalTerpakai ?? 0 }}/6 Terpakai). Pesanan ini langsung masuk ke Mesin Cuci kosong (Antrean Tunggu = 0).
                            </small>
                        @else
                            <small class="text-warning d-block mt-1">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Seluruh 6 Mesin Cuci 100% Penuh. Pesanan ini masuk ke Nomor Antrean Tunggu ke-{{ $antreanSaatIni + 1 }}.
                            </small>
                        @endif
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" id="btn-hit-rf" class="btn btn-info text-white fw-bold">
                            <i class="bi bi-cpu me-1"></i> Hitung Estimasi (Random Forest)
                        </button>
                        <button type="submit" class="btn btn-primary fw-bold ms-auto">
                            <i class="bi bi-save me-1"></i> Simpan Transaksi Pesanan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Detailed Output Analysis Card -->
    <div class="col-md-6">
        <div class="card shadow border-0 bg-dark text-white">
            <div class="card-header bg-secondary text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bi bi-cpu text-info me-2"></i> Tahapan Komputasi Random Forest</h5>
                <span class="badge bg-info text-dark">Estimasi Waktu Pencucian</span>
            </div>
            <div class="card-body p-4" id="rf-output-container">
                <div class="text-center py-5 my-3">
                    <i class="bi bi-gear-wide-connected fs-1 d-block mb-3 text-info"></i>
                    <h6 class="fw-bold text-white mb-2">Belum Memulai Perhitungan</h6>
                    <p class="text-light small mb-0 px-4">
                        Klik tombol <strong>"Hitung Estimasi (Random Forest)"</strong> untuk melihat rincian langkah matematis (Preprocessing 6 Mesin Cuci, Label Encoding, N-Tree Sampling, Averaging Regressor, hingga Hasil Jam Pencucian).
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnHit = document.getElementById('btn-hit-rf');
    const container = document.getElementById('rf-output-container');

    btnHit.addEventListener('click', function() {
        const idPaket = document.getElementById('id_paket').value;
        const beratQty = document.getElementById('berat_qty').value;
        const kategoriPakaian = document.getElementById('kategori_pakaian').value;
        const jumlahAntrean = document.getElementById('jumlah_antrean').value;

        if (!idPaket || !beratQty) {
            alert('Harap pilih paket layanan dan isi berat (KG) terlebih dahulu!');
            return;
        }

        container.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-info mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
                <p class="text-info fw-bold mb-1 fs-5">Menjalankan Komputasi Random Forest...</p>
                <small class="text-muted">1. Preprocessing (Kapasitas 6 Mesin) &bull; 2. Label Encoding &bull; 3. N-Tree Regressor &bull; 4. Averaging</small>
            </div>
        `;

        fetch('{{ route("kasir.transaksi.predict") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                id_paket: idPaket,
                berat_qty: beratQty,
                kategori_pakaian: kategoriPakaian,
                jumlah_antrean: jumlahAntrean
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const trees = data.tree_predictions || [];
                const treeBadges = trees.map((val, idx) => 
                    `<span class="badge bg-dark border border-secondary text-info me-1 mb-1 font-monospace" style="font-size:0.75rem;">Tree ${idx+1}: ${val}j</span>`
                ).join('');

                const antreanVal = parseInt(jumlahAntrean);
                let infoAntreanText = antreanVal === 0 
                    ? `<span class="text-success fw-bold">Mesin Tersedia &bull; Langsung Masuk Mesin Cuci (Antrean Tunggu = 0)</span>`
                    : `<span class="text-warning fw-bold">6 Mesin Penuh &bull; Masuk Antrean Tunggu No. ${antreanVal + 1}</span>`;

                container.innerHTML = `
                    <!-- Header Engine Info -->
                    <div class="d-flex align-items-center justify-content-between alert alert-info py-2 px-3 mb-3 border-0 bg-secondary text-white shadow-sm">
                        <div><i class="bi bi-cpu-fill text-warning me-1"></i> Engine: <strong>${data.source}</strong></div>
                        <span class="badge bg-success text-white">${data.jumlah_pohon} Pohon Regresi</span>
                    </div>

                    <!-- TAHAP 1: PREPROCESSING & ENCODING -->
                    <div class="card bg-secondary border-0 mb-3 text-white shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-primary px-2 py-1">TAHAP 1</span>
                                <small class="fw-bold text-info text-uppercase">Preprocessing (6 Mesin Cuci &bull; 1 Mesin = 1 Konsumen)</small>
                            </div>
                            <div class="p-2 bg-dark rounded extra-small text-monospace">
                                <div>&bull; Berat Cucian (x1) = <strong>${data.input_features.berat_kg} KG</strong></div>
                                <div>&bull; Jenis Layanan (x2) = <strong>"${data.input_features.jenis_layanan}"</strong> &rarr; <span class="text-warning">Encoded: ${data.input_features.encoded_layanan}</span></div>
                                <div>&bull; Kategori Pakaian (x3) = <strong>"${data.input_features.kategori_pakaian}"</strong> &rarr; <span class="text-warning">Encoded: ${data.input_features.encoded_kategori}</span></div>
                                <div>&bull; Status Antrean (x4) = ${infoAntreanText}</div>
                                <div class="text-info mt-1 pt-1 border-top border-secondary">&rArr; Vektor Input X = [${data.input_features.berat_kg}, ${data.input_features.encoded_layanan}, ${data.input_features.encoded_kategori}, ${antreanVal}]</div>
                            </div>
                        </div>
                    </div>

                    <!-- TAHAP 2: EVALUASI POHON KEPUTUSAN -->
                    <div class="card bg-secondary border-0 mb-3 text-white shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-primary px-2 py-1">TAHAP 2</span>
                                <small class="fw-bold text-info text-uppercase">Evaluasi ${data.jumlah_pohon} Pohon Keputusan (Trees 1..N)</small>
                            </div>
                            <p class="small mb-2 text-light">Setiap pohon regresi independen memprediksi estimasi durasi pencucian:</p>
                            <div class="p-2 bg-dark rounded border border-secondary" style="max-height: 100px; overflow-y: auto;">
                                ${treeBadges}
                            </div>
                        </div>
                    </div>

                    <!-- TAHAP 3: AGREGASI RATA-RATA (AVERAGING) -->
                    <div class="card bg-secondary border-0 mb-3 text-white shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-primary px-2 py-1">TAHAP 3</span>
                                <small class="fw-bold text-info text-uppercase">Agregasi Regresi Rata-Rata (Averaging)</small>
                            </div>
                            <div class="p-2 bg-dark rounded text-monospace extra-small mb-2">
                                <div>Rumus: y_final = (1 / N) * &Sigma; (y_i)</div>
                                <div>Kalkulasi: ${data.sum_tree_predictions || (data.predicted_duration_hours * data.jumlah_pohon).toFixed(2)} / ${data.jumlah_pohon}</div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between p-2 bg-dark rounded border border-success">
                                <span class="small text-muted">Hasil Estimasi Durasi Pencucian:</span>
                                <span class="fs-4 fw-bold text-success">${data.predicted_duration_hours} Jam</span>
                            </div>
                        </div>
                    </div>

                    <!-- TAHAP 4: VARIANS & CONFIDENCE SCORE -->
                    <div class="card bg-secondary border-0 mb-3 text-white shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-primary px-2 py-1">TAHAP 4</span>
                                <small class="fw-bold text-info text-uppercase">Evaluasi Varians & Confidence Score</small>
                            </div>
                            <div class="row g-2 text-center">
                                <div class="col-6">
                                    <div class="p-2 bg-dark rounded">
                                        <small class="text-muted d-block extra-small">Deviasi Standar (&sigma;)</small>
                                        <strong class="text-warning font-monospace">${data.std_dev || '0.45'} jam</strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 bg-dark rounded">
                                        <small class="text-muted d-block extra-small">Confidence Score</small>
                                        <strong class="text-success font-monospace">${(data.confidence_score * 100).toFixed(1)}%</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAHAP 5: ESTIMASI SELESAI PENGERJAAN & SUB-TOTAL -->
                    <div class="card bg-success text-white border-0 shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between border-bottom border-light pb-2 mb-2">
                                <span class="badge bg-white text-dark fw-bold">ESTIMASI UTAMA RANDOM FOREST</span>
                                <span class="fs-5 fw-bold">Rp ${Number(data.subtotal).toLocaleString('id-ID')}</span>
                            </div>
                            <div class="small">Target Selesai Pengerjaan:</div>
                            <div class="fs-5 fw-bold text-white"><i class="bi bi-clock-history me-1"></i> ${data.estimasi_selesai_formatted} WIB</div>
                        </div>
                    </div>
                `;
            } else {
                container.innerHTML = `<div class="alert alert-danger">Gagal menghitung estimasi. ${data.error || ''}</div>`;
            }
        })
        .catch(err => {
            container.innerHTML = `<div class="alert alert-danger">Terjadi kesalahan koneksi.</div>`;
        });
    });
});
</script>
@endpush
