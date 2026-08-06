@extends('layouts.app')

@section('title', 'Laporan Keuangan & Omset - Pemilik Express Laundry')

@section('content')
<div class="row mb-3 align-items-center d-print-none">
    <div class="col-md-6">
        <h4 class="fw-bold mb-0"><i class="bi bi-file-earmark-bar-graph text-primary me-2"></i> Laporan Keuangan, Omset & Laba</h4>
    </div>
    <div class="col-md-6 text-end">
        <button onclick="window.print()" class="btn btn-dark fw-bold">
            <i class="bi bi-printer me-1"></i> Cetak Laporan
        </button>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4 d-print-none">
    <div class="card-body p-3">
        <form action="{{ route('pemilik.laporan') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label font-semibold small">Tanggal Mulai</label>
                <input type="date" name="tgl_mulai" class="form-control" value="{{ $tglMulai }}">
            </div>
            <div class="col-md-4">
                <label class="form-label font-semibold small">Tanggal Selesai</label>
                <input type="date" name="tgl_selesai" class="form-control" value="{{ $tglSelesai }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="bi bi-filter me-1"></i> Tampilkan Laporan</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <!-- Header Print -->
        <div class="text-center border-bottom pb-3 mb-4">
            <h3 class="fw-bold text-uppercase mb-1">LAPORAN OMSET & LABA BERSIH SINDORY LAUNDRY</h3>
            <p class="mb-0 text-muted">Periode: <strong>{{ \Carbon\Carbon::parse($tglMulai)->format('d M Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($tglSelesai)->format('d M Y') }}</strong></p>
        </div>

        <div class="row mb-4 text-center g-2">
            <div class="col-md-3">
                <div class="p-3 bg-light rounded border">
                    <small class="text-muted d-block fw-bold text-uppercase">Total Transaksi</small>
                    <h5 class="fw-bold text-primary mb-0">{{ $transaksiList->count() }} Order ({{ number_format($totalKg, 1, ',', '.') }} KG)</h5>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded border">
                    <small class="text-muted d-block fw-bold text-uppercase">Total Omset (Kotor)</small>
                    <h5 class="fw-bold text-info mb-0">Rp {{ number_format($totalOmset, 0, ',', '.') }}</h5>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded border">
                    <small class="text-muted d-block fw-bold text-uppercase">Beban Operasional (35%)</small>
                    <h5 class="fw-bold text-danger mb-0">Rp {{ number_format($estimasiBeban, 0, ',', '.') }}</h5>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-success bg-opacity-10 rounded border border-success">
                    <small class="text-success d-block fw-bold text-uppercase">Laba Bersih (Net Profit)</small>
                    <h5 class="fw-bold text-success mb-0">Rp {{ number_format($labaBersih, 0, ',', '.') }}</h5>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No.</th>
                        <th>No. Nota</th>
                        <th>Tanggal Masuk</th>
                        <th>Nama Pelanggan</th>
                        <th>Layanan & Berat</th>
                        <th>Status</th>
                        <th>Kasir</th>
                        <th class="text-end">Omset (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksiList as $index => $t)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $t->no_nota }}</strong></td>
                            <td>{{ $t->tgl_masuk->format('d/m/Y H:i') }}</td>
                            <td>{{ $t->pelanggan->user->nama ?? '-' }}</td>
                            <td>
                                {{ $t->detailTransaksi->first()->paketLayanan->nama_paket ?? '-' }}
                                ({{ $t->detailTransaksi->first()->berat_qty ?? 0 }} KG)
                            </td>
                            <td><span class="badge bg-success">{{ $t->status_pengerjaan }}</span></td>
                            <td>{{ $t->kasir->nama ?? '-' }}</td>
                            <td class="text-end fw-bold text-success">Rp {{ number_format($t->total_bayar, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada transaksi pada periode ini.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="7" class="text-end fw-bold">TOTAL OMSET KOTOR:</td>
                        <td class="text-end fw-bold fs-5 text-info">Rp {{ number_format($totalOmset, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="table-light">
                        <td colspan="7" class="text-end fw-bold">ESTIMASI BEBAN OPERASIONAL (35%):</td>
                        <td class="text-end fw-bold text-danger">- Rp {{ number_format($estimasiBeban, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="table-success">
                        <td colspan="7" class="text-end fw-bold fs-5 text-success">TOTAL LABA BERSIH (NET PROFIT):</td>
                        <td class="text-end fw-bold fs-5 text-success">Rp {{ number_format($labaBersih, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
