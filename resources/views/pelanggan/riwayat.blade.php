@extends('layouts.app')

@section('title', 'Riwayat Pesanan - Express Laundry')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2"></i> Riwayat Pesanan Cucian Saya</h5>
                <a href="{{ route('monitoring') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-search me-1"></i> Lacak Nota</a>
            </div>
            <div class="card-body p-0">
                @if($riwayatTransaksi->isEmpty())
                    <div class="p-5 text-center text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-3 text-secondary"></i>
                        <p class="mb-0">Belum ada riwayat transaksi cucian.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No. Nota</th>
                                    <th>Tanggal Masuk</th>
                                    <th>Paket Layanan</th>
                                    <th>Berat (KG)</th>
                                    <th>Status</th>
                                    <th>Estimasi Selesai</th>
                                    <th>Total Bayar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($riwayatTransaksi as $t)
                                    <tr>
                                        <td><strong class="text-primary">{{ $t->no_nota }}</strong></td>
                                        <td>{{ $t->tgl_masuk->format('d/m/Y H:i') }} WIB</td>
                                        <td>{{ $t->detailTransaksi->first()->paketLayanan->nama_paket ?? '-' }}</td>
                                        <td>{{ $t->detailTransaksi->first()->berat_qty ?? '-' }} KG</td>
                                        <td>
                                            <span class="badge 
                                                @if($t->status_pengerjaan === 'Selesai') bg-success
                                                @elseif($t->status_pengerjaan === 'Antre') bg-secondary
                                                @else bg-primary @endif">
                                                {{ $t->status_pengerjaan }}
                                            </span>
                                        </td>
                                        <td>{{ $t->estimasi_selesai ? $t->estimasi_selesai->format('d/m/Y H:i') : '-' }}</td>
                                        <td><strong class="text-success">Rp {{ number_format($t->total_bayar, 0, ',', '.') }}</strong></td>
                                        <td>
                                            <a href="{{ route('monitoring', ['nota' => $t->no_nota]) }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-display"></i> Lacak Live
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3">
                        {{ $riwayatTransaksi->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
