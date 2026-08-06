@extends('layouts.app')

@section('title', 'Monitoring Pesanan - SINDORY')

@section('content')
<!-- Search Hero Banner -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow text-white" style="background: linear-gradient(135deg, #0f172a, #0284c7);">
            <div class="card-body p-4 p-md-5 text-center position-relative overflow-hidden">
                <div class="mb-3">
                    <img src="{{ asset('images/logo.png') }}" alt="SINDORY Logo" height="80" class="rounded bg-white p-2 shadow">
                </div>
                <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-10 border border-white border-opacity-25 px-4 py-2 rounded-pill mb-3">
                    <span class="fs-5">👋</span>
                    <span class="fw-bold text-warning text-uppercase tracking-wider">Selamat Datang di SINDORY Laundry</span>
                </div>
                <h2 class="fw-bold mb-2 display-6">Lacak Status Cucian Real-Time Anda</h2>
                <p class="text-white-50 mb-4 fs-6 max-w-2xl mx-auto">
                    Masukkan <strong>Nomor Nota (contoh: EXP-...)</strong> atau <strong>Nomor Telepon / WhatsApp</strong> Anda untuk memantau progress pengerjaan laundry secara transparan.
                </p>

                <form action="{{ route('monitoring') }}" method="GET" class="row justify-content-center">
                    <div class="col-md-8 col-lg-7">
                        <div class="input-group input-group-lg shadow">
                            <span class="input-group-text bg-white border-0 text-primary px-3"><i class="bi bi-search fs-4"></i></span>
                            <input type="text" name="nota" class="form-control border-0 py-3" placeholder="Masukkan Nomor Nota (EXP-...) atau No. Telepon / WA..." value="{{ request('nota', $searchQuery ?? '') }}" required>
                            <button class="btn btn-warning fw-bold px-4 text-dark fs-6" type="submit">
                                <i class="bi bi-search me-1"></i> LACAK SEKARANG
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Status 6 Mesin Cuci Real-Time Grid -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm border-0 bg-white">
            <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-water text-primary me-2"></i> Status Real-Time 6 Mesin Cuci SINDORY</h6>
                    <small class="text-muted">Prinsip Higienis: 1 Mesin Cuci melayani 1 Konsumen (Pakaian tidak dicampur)</small>
                </div>
                <span class="badge bg-primary px-3 py-2 rounded-pill"><i class="bi bi-cpu me-1"></i> 6 Mesin Cuci Active</span>
            </div>
            <div class="card-body p-3">
                <div class="row g-2">
                    @foreach($mesinCuciList as $m)
                        @php
                            $isMyOrder = $activeTransaction && $activeTransaction->no_nota === $m['no_nota'];
                        @endphp
                        <div class="col-md-4 col-lg-2">
                            <div class="p-3 rounded border text-center {{ $isMyOrder ? 'bg-warning bg-opacity-25 border-warning shadow-sm' : ($m['status'] === 'TERPAKAI' ? 'bg-primary bg-opacity-10 border-primary' : 'bg-light border-secondary border-opacity-25') }}">
                                <div class="fs-4 mb-1">
                                    <i class="bi {{ $m['status'] === 'TERPAKAI' ? 'bi-arrow-repeat text-primary spin' : 'bi-check-circle text-success' }}"></i>
                                </div>
                                <strong class="d-block text-dark small mb-1">{{ $m['nama_mesin'] }}</strong>
                                @if($m['status'] === 'TERPAKAI')
                                    <span class="badge {{ $isMyOrder ? 'bg-warning text-dark' : 'bg-primary' }} mb-1 extra-small">{{ $m['no_nota'] }}</span>
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

@if(isset($matchingTransactions) && $matchingTransactions->count() > 1)
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0 bg-white">
                <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-telephone-inbound text-primary me-2"></i> 
                        Ditemukan {{ $matchingTransactions->count() }} Pesanan untuk No. Telepon / Kata Kunci: <strong>"{{ $searchQuery }}"</strong>
                    </h6>
                    <small class="text-muted">Klik "Pilih Nota" untuk melihat progress detail</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No. Nota</th>
                                    <th>Pelanggan</th>
                                    <th>No. Telepon / WA</th>
                                    <th>Tanggal Masuk</th>
                                    <th>Status Real-Time</th>
                                    <th>Total Bayar</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($matchingTransactions as $mt)
                                    <tr class="{{ $activeTransaction && $activeTransaction->id_transaksi === $mt->id_transaksi ? 'table-primary fw-bold' : '' }}">
                                        <td><strong>{{ $mt->no_nota }}</strong></td>
                                        <td>{{ $mt->pelanggan->user->nama ?? '-' }}</td>
                                        <td>{{ $mt->pelanggan->no_telepon ?? '-' }}</td>
                                        <td>{{ $mt->tgl_masuk->format('d/m/Y H:i') }} WIB</td>
                                        <td>
                                            <span class="badge 
                                                @if($mt->status_pengerjaan === 'Selesai') bg-success
                                                @elseif($mt->status_pengerjaan === 'Antre') bg-secondary
                                                @else bg-primary @endif fs-6">
                                                {{ $mt->status_pengerjaan }}
                                            </span>
                                        </td>
                                        <td>Rp {{ number_format($mt->total_bayar, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('monitoring', ['nota' => $searchQuery, 'id' => $mt->id_transaksi]) }}" class="btn btn-sm {{ $activeTransaction && $activeTransaction->id_transaksi === $mt->id_transaksi ? 'btn-primary' : 'btn-outline-primary' }}">
                                                <i class="bi bi-eye me-1"></i> {{ $activeTransaction && $activeTransaction->id_transaksi === $mt->id_transaksi ? 'Sedang Dilihat' : 'Pilih Nota' }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@if($activeTransaction)
    @php
        $statusOrder = ['Antre', 'Cuci', 'Kering', 'Setrika', 'Selesai'];
        $currentStatus = $activeTransaction->status_pengerjaan;
        $currentIndex = array_search($currentStatus, $statusOrder);
        if ($currentIndex === false) $currentIndex = 0;

        $targetTimestamp = $activeTransaction->estimasi_selesai ? $activeTransaction->estimasi_selesai->timestamp * 1000 : 0;
    @endphp

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow border-0 card-interactive">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
                    <div>
                        <span class="text-muted small d-block">Nomor Nota Pesanan:</span>
                        <h5 class="fw-bold text-primary mb-0"><i class="bi bi-ticket-perforated me-2"></i> {{ $activeTransaction->no_nota }}</h5>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill"><i class="bi bi-person-fill me-1"></i> {{ $activeTransaction->pelanggan->user->nama ?? 'Pelanggan' }}</span>
                        <small class="text-muted d-block mt-1"><i class="bi bi-telephone me-1"></i> {{ $activeTransaction->pelanggan->no_telepon ?? '-' }}</small>
                    </div>
                </div>
                <div class="card-body p-4">
                    <!-- Progress Bar Status Pengerjaan Real-time -->
                    <h6 class="fw-bold text-uppercase text-secondary mb-3"><i class="bi bi-activity me-1 text-primary"></i> Status Pengerjaan Real-Time</h6>
                    <div class="step-progress mb-4">
                        @foreach($statusOrder as $index => $st)
                            @php
                                $class = '';
                                if ($index < $currentIndex) $class = 'completed';
                                elseif ($index === $currentIndex) $class = 'active';
                            @endphp
                            <div class="step-item {{ $class }}">
                                @if($index < $currentIndex)
                                    <i class="bi bi-check-circle-fill me-1"></i>
                                @elseif($index === $currentIndex)
                                    <i class="bi bi-arrow-repeat spin me-1"></i>
                                @else
                                    <i class="bi bi-circle me-1"></i>
                                @endif
                                {{ $st }}
                            </div>
                        @endforeach
                    </div>

                    <div class="row text-center my-4 align-items-center bg-light p-4 rounded-3 border">
                        <div class="col-md-6 mb-4 mb-md-0 border-end">
                            <small class="text-uppercase text-muted fw-bold d-block mb-2"><i class="bi bi-clock-history me-1"></i> HITUNG MUNDUR WAKTU SELESAI</small>
                            @if($currentStatus === 'Selesai')
                                <div class="alert alert-success d-inline-block py-3 px-4 mb-0 fw-bold fs-5 shadow-sm">
                                    <i class="bi bi-check-all me-2"></i> PESANAN SUDAH SELESAI & SIAP DIAMBIL!
                                </div>
                            @else
                                <div id="countdown-timer" class="timer-box mb-2">00 : 00 : 00</div>
                                <div class="small text-muted">
                                    Target Selesai: <strong class="text-dark">{{ $activeTransaction->estimasi_selesai ? $activeTransaction->estimasi_selesai->format('d M Y, H:i') : '-' }} WIB</strong>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <small class="text-uppercase text-muted fw-bold d-block mb-2"><i class="bi bi-cpu me-1"></i> HASIL PREDIKSI RANDOM FOREST REGRESSOR</small>
                            <div class="fs-2 fw-bold text-success mb-1">
                                {{ $activeTransaction->prediksiAnalisis->durasi_estimasi_jam ?? '-' }} Jam
                            </div>
                            <div class="small text-muted">
                                Confidence Score: 
                                <strong class="text-primary">
                                    {{ isset($activeTransaction->prediksiAnalisis->confidence_score) ? round($activeTransaction->prediksiAnalisis->confidence_score * 100, 1) . '%' : '-' }}
                                </strong>
                                &bull; Antrean saat itu: <strong>{{ $activeTransaction->prediksiAnalisis->jumlah_antrean ?? 0 }} pesanan</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Rincian Cucian -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Detail Layanan Laundry</h6>
                            <table class="table table-borderless table-sm text-secondary">
                                <tr>
                                    <td width="140"><strong>Waktu Masuk</strong></td>
                                    <td>: {{ $activeTransaction->tgl_masuk->format('d F Y, H:i') }} WIB</td>
                                </tr>
                                <tr>
                                    <td><strong>Operator Staf</strong></td>
                                    <td>: {{ $activeTransaction->kasir->nama ?? 'Staf SINDORY' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Biaya</strong></td>
                                    <td>: <strong class="text-success fs-6">Rp {{ number_format($activeTransaction->total_bayar, 0, ',', '.') }}</strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Paket & Kategori Pakaian</h6>
                            <ul class="list-group list-group-flush small">
                                @foreach($activeTransaction->detailTransaksi as $dt)
                                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0">
                                        <div>
                                            <strong>{{ $dt->paketLayanan->nama_paket ?? 'Paket' }}</strong> 
                                            <span class="text-muted">({{ $dt->kategori_pategori ?? $dt->kategori_pakaian }})</span>
                                        </div>
                                        <span class="badge bg-secondary rounded-pill">{{ $dt->berat_qty }} KG</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@elseif(isset($searchQuery) && $searchQuery !== '')
    <div class="alert alert-warning text-center shadow-sm p-4">
        <i class="bi bi-exclamation-triangle fs-2 text-warning d-block mb-2"></i>
        Data transaksi untuk kata kunci / No. Telepon / Nota <strong>"{{ $searchQuery }}"</strong> tidak ditemukan dalam sistem.<br>
        <small class="text-muted">Silakan periksa kembali Nomor Nota (contoh: EXP-...) atau Nomor Telepon / WA Anda.</small>
    </div>
@endif

@endsection

@push('scripts')
@if(isset($targetTimestamp) && $targetTimestamp > 0 && isset($currentStatus) && $currentStatus !== 'Selesai')
<script>
    (function() {
        const targetTime = {{ $targetTimestamp }};
        const timerElement = document.getElementById('countdown-timer');

        function updateTimer() {
            const now = new Date().getTime();
            const distance = targetTime - now;

            if (distance < 0) {
                timerElement.innerHTML = "00 : 00 : 00";
                timerElement.classList.remove('timer-box');
                timerElement.classList.add('badge', 'bg-warning', 'text-dark', 'fs-4');
                timerElement.innerHTML = "<i class='bi bi-hourglass-split me-2'></i>SEGERA SELESAI";
                return;
            }

            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            const hStr = hours < 10 ? '0' + hours : hours;
            const mStr = minutes < 10 ? '0' + minutes : minutes;
            const sStr = seconds < 10 ? '0' + seconds : seconds;

            timerElement.innerText = `${hStr} : ${mStr} : ${sStr}`;
        }

        updateTimer();
        setInterval(updateTimer, 1000);
    })();
</script>
@endif
@endpush
