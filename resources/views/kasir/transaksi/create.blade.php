@extends('layouts.app')

@section('title', 'Input Pesanan Baru - Kasir Express Laundry')

@section('content')
<div class="row">
    <div class="col-md-7 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-cart-plus me-2"></i> Input Data Pesanan Baru</h5>
            </div>
            <div class="card-body p-4">
                <form id="form-transaksi" action="{{ route('kasir.transaksi.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="id_pelanggan" class="form-label font-semibold">Pilih Pelanggan</label>
                        <select name="id_pelanggan" id="id_pelanggan" class="form-select @error('id_pelanggan') is-invalid @enderror" required>
                            <option value="">-- Pilih Pelanggan --</option>
                            @foreach($pelangganList as $p)
                                <option value="{{ $p->id_pelanggan }}" {{ old('id_pelanggan') == $p->id_pelanggan ? 'selected' : '' }}>
                                    {{ $p->user->nama }} ({{ $p->no_telepon }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_pelanggan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

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
                        <label for="jumlah_antrean" class="form-label font-semibold">Jumlah Antrean Cucian Saat Ini</label>
                        <input type="number" class="form-control" id="jumlah_antrean" name="jumlah_antrean" value="{{ $antreanSaatIni }}" readonly>
                        <small class="text-muted">Diambil otomatis dari jumlah transaksi aktif di sistem.</small>
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

    <!-- Output Analysis Card -->
    <div class="col-md-5">
        <div class="card shadow-sm border-0 bg-dark text-white">
            <div class="card-header bg-secondary text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-cpu me-2"></i> Output Komputasi Random Forest</h5>
            </div>
            <div class="card-body p-4" id="rf-output-container">
                <p class="text-light text-center py-4 my-2">
                    <i class="bi bi-arrow-left-circle fs-1 d-block mb-2 text-info"></i>
                    Klik tombol <strong>"Hitung Estimasi (Random Forest)"</strong> untuk menjalankan alur komputasi N-Pohon Keputusan Regresi.
                </p>
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
            <div class="text-center py-4">
                <div class="spinner-border text-info mb-3" role="status"></div>
                <p class="text-info fw-bold mb-0">Menjalankan Data Preprocessing & Categorical Encoding...</p>
                <small class="text-muted">Mengevaluasi N-Pohon Keputusan Regresi...</small>
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
                const sampleTrees = trees.slice(0, 10).join(', ') + (trees.length > 10 ? '...' : '');

                container.innerHTML = `
                    <div class="alert alert-info py-2 mb-3 small">
                        <i class="bi bi-server me-1"></i> Sumber Engine: <strong>${data.source}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-uppercase text-secondary fw-bold">1. Preprocessing & Encoding</small>
                        <div class="p-2 bg-secondary rounded mt-1 extra-small">
                            Jenis: <strong>${data.input_features.jenis_layanan}</strong> (Val: ${data.input_features.encoded_layanan})<br>
                            Kategori: <strong>${data.input_features.kategori_pakaian}</strong> (Val: ${data.input_features.encoded_kategori})
                        </div>
                    </div>
                    <div class="mb-3">
                        <small class="text-uppercase text-secondary fw-bold">2. N-Pohon Keputusan (${data.jumlah_pohon} Trees)</small>
                        <div class="p-2 bg-secondary rounded mt-1 extra-small text-truncate">
                            y1..yN: [${sampleTrees}]
                        </div>
                    </div>
                    <div class="mb-3">
                        <small class="text-uppercase text-secondary fw-bold">3. Agregasi Rata-Rata (Averaging)</small>
                        <div class="display-6 fw-bold text-success my-1">${data.predicted_duration_hours} Jam</div>
                        <div class="small text-muted">Confidence Score: <strong class="text-warning">${(data.confidence_score * 100).toFixed(1)}%</strong></div>
                    </div>
                    <div class="border-top pt-3">
                        <small class="text-muted d-block mb-1">Estimasi Tanggal Selesai:</small>
                        <div class="fs-6 fw-bold text-info">${data.estimasi_selesai_formatted} WIB</div>
                        <div class="mt-2 text-end">
                            <small class="text-muted">Total Bayar:</small>
                            <span class="fs-5 fw-bold text-warning ms-1">Rp ${Number(data.subtotal).toLocaleString('id-ID')}</span>
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
