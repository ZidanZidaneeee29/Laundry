@extends('layouts.app')

@section('title', 'Monitoring Pesanan - SINDORY')

@section('content')
<!-- Search Hero Banner -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #0f172a, #0284c7);">
            <div class="card-body p-4 p-md-5 text-center position-relative overflow-hidden">
                <div class="mb-3">
                    <img src="{{ asset('images/logo.png') }}" alt="SINDORY Logo" height="75" class="rounded bg-white p-2 shadow-sm">
                </div>
                <h2 class="fw-bold mb-2">SINDORY &bull; Status Cucian Real-Time</h2>
                <p class="text-white-50 mb-4 fs-6">Sistem Informasi Indo Express Laundry dengan Estimasi Presisi Random Forest Regressor</p>

                <form action="{{ route('monitoring') }}" method="GET" class="row justify-content-center">
                    <div class="col-md-7 col-lg-6">
                        <div class="input-group input-group-lg shadow-sm">
                            <span class="input-group-text bg-white border-0 text-primary"><i class="bi bi-receipt"></i></span>
                            <input type="text" name="nota" class="form-control border-0" placeholder="Masukkan Nomor Nota (contoh: EXP-20260729-001)" value="{{ request('nota') }}" required>
                            <button class="btn btn-warning fw-bold px-4 text-dark" type="submit">
                                <i class="bi bi-search me-1"></i> LACAK
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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
                            <div class="row g-2 justify-content-center">
                                <div class="col-4">
                                    <div class="p-2 border rounded bg-white shadow-sm">
                                        <small class="text-muted d-block extra-small">Durasi Estimasi</small>
                                        <strong class="fs-5 text-primary">{{ $activeTransaction->prediksiAnalisis->durasi_estimasi_jam ?? 0 }} Jam</strong>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 border rounded bg-white shadow-sm">
                                        <small class="text-muted d-block extra-small">Confidence Score</small>
                                        <strong class="fs-5 text-success">{{ round(($activeTransaction->prediksiAnalisis->confidence_score ?? 0.95) * 100, 1) }}%</strong>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 border rounded bg-white shadow-sm">
                                        <small class="text-muted d-block extra-small">Antrean Saat Itu</small>
                                        <strong class="fs-5 text-dark">{{ $activeTransaction->prediksiAnalisis->jumlah_antrean ?? 0 }} Pesanan</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Pesanan Table -->
                    <div class="table-responsive border-top pt-3">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Paket Layanan</th>
                                    <th>Kategori Pakaian</th>
                                    <th>Berat (KG)</th>
                                    <th>Harga per KG</th>
                                    <th>Total Bayar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activeTransaction->detailTransaksi as $detail)
                                    <tr>
                                        <td><strong>{{ $detail->paketLayanan->nama_paket ?? '-' }}</strong></td>
                                        <td>{{ $detail->kategori_pakaian }}</td>
                                        <td>{{ $detail->berat_qty }} KG</td>
                                        <td>Rp {{ number_format($detail->paketLayanan->harga_per_kg ?? 0, 0, ',', '.') }}</td>
                                        <td><strong class="text-success fs-6">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@elseif($searchNota)
    <div class="alert alert-warning text-center shadow-sm p-4">
        <i class="bi bi-exclamation-triangle fs-2 text-warning d-block mb-2"></i>
        Nomor nota <strong>"{{ $searchNota }}"</strong> tidak ditemukan dalam sistem. Silakan periksa kembali nomor nota Anda.
    </div>
@endif

@auth
    @if(auth()->user()->role === 'pelanggan' && isset($riwayatTransaksi) && $riwayatTransaksi->count() > 0)
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-journal-text me-2 text-primary"></i> Riwayat Pesanan Saya</h6>
                        <a href="{{ route('pelanggan.riwayat') }}" class="btn btn-sm btn-outline-primary fw-semibold">Lihat Semua Riwayat</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>No. Nota</th>
                                        <th>Tanggal Masuk</th>
                                        <th>Status Pengerjaan</th>
                                        <th>Estimasi Selesai</th>
                                        <th>Total Bayar</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($riwayatTransaksi as $t)
                                        <tr>
                                            <td><strong class="text-primary">{{ $t->no_nota }}</strong></td>
                                            <td>{{ $t->tgl_masuk->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <span class="badge 
                                                    @if($t->status_pengerjaan === 'Selesai') bg-success
                                                    @elseif($t->status_pengerjaan === 'Antre') bg-secondary
                                                    @else bg-primary @endif">
                                                    {{ $t->status_pengerjaan }}
                                                </span>
                                            </td>
                                            <td>{{ $t->estimasi_selesai ? $t->estimasi_selesai->format('d/m/Y H:i') : '-' }}</td>
                                            <td>Rp {{ number_format($t->total_bayar, 0, ',', '.') }}</td>
                                            <td>
                                                <a href="{{ route('monitoring', ['nota' => $t->no_nota]) }}" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-eye me-1"></i> Detail
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
@endauth

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
