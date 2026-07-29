@extends('layouts.app')

@section('title', 'Kelola Transaksi - Kasir Express Laundry')

@section('content')
<div class="row mb-3 align-items-center">
    <div class="col-md-6">
        <h4 class="fw-bold mb-0"><i class="bi bi-journal-plus text-primary me-2"></i> Halaman Transaksi Kasir</h4>
    </div>
    <div class="col-md-6 text-md-end mt-2 mt-md-0">
        <a href="{{ route('kasir.transaksi.create') }}" class="btn btn-primary fw-bold shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Tambah Transaksi Pesanan
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <form action="{{ route('kasir.transaksi.index') }}" method="GET" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Cari No. Nota / Nama Pelanggan..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
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
                <button type="submit" class="btn btn-secondary w-100 fw-bold"><i class="bi bi-filter me-1"></i> Filter</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('kasir.transaksi.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
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
                        <th>Tgl Masuk</th>
                        <th>Layanan</th>
                        <th>Estimasi Selesai (RF)</th>
                        <th>Status</th>
                        <th>Total Bayar</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksi as $t)
                        <tr>
                            <td><strong class="text-primary">{{ $t->no_nota }}</strong></td>
                            <td>
                                <strong>{{ $t->pelanggan->user->nama ?? '-' }}</strong><br>
                                <small class="text-muted">{{ $t->pelanggan->no_telepon ?? '-' }}</small>
                            </td>
                            <td>{{ $t->tgl_masuk->format('d/m/Y H:i') }}</td>
                            <td>
                                {{ $t->detailTransaksi->first()->paketLayanan->nama_paket ?? '-' }}
                                ({{ $t->detailTransaksi->first()->berat_qty ?? 0 }} KG)
                            </td>
                            <td>
                                <strong>{{ $t->estimasi_selesai ? $t->estimasi_selesai->format('d/m/Y H:i') : '-' }}</strong><br>
                                <small class="text-info font-monospace">RF: {{ $t->prediksiAnalisis->durasi_estimasi_jam ?? 0 }} Jam</small>
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
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('kasir.transaksi.show', $t->id_transaksi) }}" class="btn btn-outline-info" title="Lihat Nota & Transparansi RF">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                    <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalStatus-{{ $t->id_transaksi }}" title="Update Status">
                                        <i class="bi bi-pencil-square"></i> Status
                                    </button>
                                </div>

                                <!-- Modal Update Status -->
                                <div class="modal fade text-start" id="modalStatus-{{ $t->id_transaksi }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('kasir.transaksi.update-status', $t->id_transaksi) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-header bg-warning text-dark">
                                                    <h5 class="modal-header-title fw-bold mb-0">Update Status Nota {{ $t->no_nota }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label font-semibold">Status Pengerjaan Saat Ini</label>
                                                        <select name="status_pengerjaan" class="form-select fw-bold" required>
                                                            <option value="Antre" {{ $t->status_pengerjaan === 'Antre' ? 'selected' : '' }}>1. Antre</option>
                                                            <option value="Cuci" {{ $t->status_pengerjaan === 'Cuci' ? 'selected' : '' }}>2. Cuci</option>
                                                            <option value="Kering" {{ $t->status_pengerjaan === 'Kering' ? 'selected' : '' }}>3. Kering</option>
                                                            <option value="Setrika" {{ $t->status_pengerjaan === 'Setrika' ? 'selected' : '' }}>4. Setrika</option>
                                                            <option value="Selesai" {{ $t->status_pengerjaan === 'Selesai' ? 'selected' : '' }}>5. Selesai (Siap Diambil)</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary fw-bold">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Belum ada data transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $transaksi->links() }}
        </div>
    </div>
</div>
@endsection
