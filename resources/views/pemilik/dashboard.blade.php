@extends('layouts.app')

@section('title', 'Dashboard Pemilik - Express Laundry')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h3 class="fw-bold mb-1"><i class="bi bi-speedometer2 text-primary me-2"></i> Executive Dashboard SINDORY</h3>
        <p class="text-muted">Ringkasan Kinerja Bisnis & Monitoring Komputasi Cerdas Random Forest Regressor</p>
    </div>
</div>

<!-- Stat Cards -->
<div class="row mb-4 g-3">
    <div class="col-md-3">
        <div class="card border-0 text-white shadow-sm card-interactive" style="background: linear-gradient(135deg, #0284c7, #0369a1);">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-uppercase fw-bold text-white-50">Total Omset Pendapatan</small>
                    <h3 class="fw-bold mb-0 mt-2">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h3>
                </div>
                <div class="p-3 rounded-circle bg-white bg-opacity-20">
                    <i class="bi bi-cash-stack fs-2 text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 text-white shadow-sm card-interactive" style="background: linear-gradient(135deg, #6366f1, #4338ca);">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-uppercase fw-bold text-white-50">Total Pesanan</small>
                    <h3 class="fw-bold mb-0 mt-2">{{ $totalTransaksi }} Order</h3>
                </div>
                <div class="p-3 rounded-circle bg-white bg-opacity-20">
                    <i class="bi bi-receipt fs-2 text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 text-white shadow-sm card-interactive" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-uppercase fw-bold text-white-50">Sedang Diproses (Aktif)</small>
                    <h3 class="fw-bold mb-0 mt-2">{{ $transaksiAktif }} Order</h3>
                </div>
                <div class="p-3 rounded-circle bg-white bg-opacity-20">
                    <i class="bi bi-hourglass-split fs-2 text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 text-white shadow-sm card-interactive" style="background: linear-gradient(135deg, #10b981, #059669);">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-uppercase fw-bold text-white-50">Confidence Score RF</small>
                    <h3 class="fw-bold mb-0 mt-2">{{ $avgConfidencePercent }}%</h3>
                </div>
                <div class="p-3 rounded-circle bg-white bg-opacity-20">
                    <i class="bi bi-cpu fs-2 text-white"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Table Transaksi Terkini -->
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <h5 class="mb-0 fw-bold"><i class="bi bi-journal-check me-2 text-primary"></i> Transaksi Terkini</h5>
                <a href="{{ route('pemilik.monitoring') }}" class="btn btn-sm btn-outline-primary fw-bold">Lihat Semua Monitoring</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No. Nota</th>
                                <th>Pelanggan</th>
                                <th>Paket Layanan</th>
                                <th>Estimasi Selesai (RF)</th>
                                <th>Status Pengerjaan</th>
                                <th>Total Bayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($latestTransactions as $lt)
                                <tr>
                                    <td><strong class="text-primary">{{ $lt->no_nota }}</strong></td>
                                    <td>{{ $lt->pelanggan->user->nama ?? '-' }}</td>
                                    <td>{{ $lt->detailTransaksi->first()->paketLayanan->nama_paket ?? '-' }}</td>
                                    <td>{{ $lt->estimasi_selesai ? $lt->estimasi_selesai->format('d/m/Y H:i') : '-' }}</td>
                                    <td>
                                        <span class="badge 
                                            @if($lt->status_pengerjaan === 'Selesai') bg-success
                                            @elseif($lt->status_pengerjaan === 'Antre') bg-secondary
                                            @else bg-primary @endif">
                                            {{ $lt->status_pengerjaan }}
                                        </span>
                                    </td>
                                    <td><strong class="text-success">Rp {{ number_format($lt->total_bayar, 0, ',', '.') }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
