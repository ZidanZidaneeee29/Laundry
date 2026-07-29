@extends('layouts.app')

@section('title', 'Monitoring Transaksi - Pemilik Express Laundry')

@section('content')
<div class="row mb-3 align-items-center">
    <div class="col-md-6">
        <h4 class="fw-bold mb-0"><i class="bi bi-tv text-primary me-2"></i> Monitoring Operasional Transaksi</h4>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <form action="{{ route('pemilik.monitoring') }}" method="GET" class="row g-2">
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="Antre" {{ request('status') == 'Antre' ? 'selected' : '' }}>Antre</option>
                    <option value="Cuci" {{ request('status') == 'Cuci' ? 'selected' : '' }}>Cuci</option>
                    <option value="Kering" {{ request('status') == 'Kering' ? 'selected' : '' }}>Kering</option>
                    <option value="Setrika" {{ request('status') == 'Setrika' ? 'selected' : '' }}>Setrika</option>
                    <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-secondary w-100 fw-bold"><i class="bi bi-filter me-1"></i> Filter Status</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('pemilik.monitoring') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. Nota</th>
                        <th>Pelanggan</th>
                        <th>Kasir Bertugas</th>
                        <th>Tgl Masuk</th>
                        <th>Paket & Berat</th>
                        <th>Estimasi Random Forest</th>
                        <th>Status</th>
                        <th>Total Bayar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksiList as $t)
                        <tr>
                            <td><strong class="text-primary">{{ $t->no_nota }}</strong></td>
                            <td>{{ $t->pelanggan->user->nama ?? '-' }}</td>
                            <td>{{ $t->kasir->nama ?? '-' }}</td>
                            <td>{{ $t->tgl_masuk->format('d/m/Y H:i') }}</td>
                            <td>
                                {{ $t->detailTransaksi->first()->paketLayanan->nama_paket ?? '-' }}
                                ({{ $t->detailTransaksi->first()->berat_qty ?? 0 }} KG)
                            </td>
                            <td>
                                <strong>{{ $t->estimasi_selesai ? $t->estimasi_selesai->format('d/m/Y H:i') : '-' }}</strong><br>
                                <small class="text-info font-monospace">Durasi: {{ $t->prediksiAnalisis->durasi_estimasi_jam ?? 0 }} Jam</small>
                            </td>
                            <td>
                                <span class="badge 
                                    @if($t->status_pengerjaan === 'Selesai') bg-success
                                    @elseif($t->status_pengerjaan === 'Antre') bg-secondary
                                    @else bg-primary @endif">
                                    {{ $t->status_pengerjaan }}
                                </span>
                            </td>
                            <td><strong class="text-success">Rp {{ number_format($t->total_bayar, 0, ',', '.') }}</strong></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $transaksiList->links() }}
        </div>
    </div>
</div>
@endsection
