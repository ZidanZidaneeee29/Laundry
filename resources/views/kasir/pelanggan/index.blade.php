@extends('layouts.app')

@section('title', 'Daftar Pelanggan - SINDORY')

@section('content')
<div class="row mb-3 align-items-center">
    <div class="col-md-12">
        <h4 class="fw-bold mb-0"><i class="bi bi-people text-primary me-2"></i> Daftar Data Pelanggan</h4>
        <small class="text-muted">Data pelanggan terisi & diperbarui secara otomatis saat penginputan transaksi pesanan baru.</small>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <form action="{{ route('kasir.pelanggan.index') }}" method="GET" class="row g-2">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Cari Nama / Telepon Pelanggan..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-secondary w-100 fw-bold"><i class="bi bi-search me-1"></i> Cari</button>
            </div>
            <div class="col-md-3">
                <a href="{{ route('kasir.pelanggan.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>NO</th>
                        <th>Nama Pelanggan</th>
                        <th>No. Telepon / WA</th>
                        <th>Alamat Lengkap</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pelangganList as $p)
                        <tr>
                            <td>{{ $loop->iteration + ($pelangganList->currentPage() - 1) * $pelangganList->perPage() }}</td>
                            <td><strong>{{ $p->user->nama ?? '-' }}</strong></td>
                            <td>{{ $p->no_telepon }}</td>
                            <td>{{ $p->alamat }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEdit-{{ $p->id_pelanggan }}">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <form action="{{ route('kasir.pelanggan.destroy', $p->id_pelanggan) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data pelanggan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Hapus</button>
                                </form>

                                <!-- Modal Edit Pelanggan -->
                                <div class="modal fade text-start" id="modalEdit-{{ $p->id_pelanggan }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('kasir.pelanggan.update', $p->id_pelanggan) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header bg-warning">
                                                    <h5 class="modal-title fw-bold mb-0">Edit Pelanggan: {{ $p->user->nama }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label font-semibold">Nama Lengkap</label>
                                                        <input type="text" name="nama" class="form-control" value="{{ $p->user->nama }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label font-semibold">No. Telepon / WA</label>
                                                        <input type="text" name="no_telepon" class="form-control" value="{{ $p->no_telepon }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label font-semibold">Alamat Lengkap</label>
                                                        <input type="text" name="alamat" class="form-control" value="{{ $p->alamat }}" required>
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
                        <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data pelanggan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $pelangganList->links() }}
        </div>
    </div>
</div>
@endsection
