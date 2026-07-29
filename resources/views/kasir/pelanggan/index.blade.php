@extends('layouts.app')

@section('title', 'Kelola Pelanggan - Kasir Express Laundry')

@section('content')
<div class="row mb-3 align-items-center">
    <div class="col-md-6">
        <h4 class="fw-bold mb-0"><i class="bi bi-people text-primary me-2"></i> Kelola Data Pelanggan</h4>
    </div>
    <div class="col-md-6 text-md-end mt-2 mt-md-0">
        <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPelanggan">
            <i class="bi bi-person-plus me-1"></i> Tambah Pelanggan Baru
        </button>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <form action="{{ route('kasir.pelanggan.index') }}" method="GET" class="row g-2">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Cari Nama / Email / Telepon Pelanggan..." value="{{ request('search') }}">
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
                        <th>#ID</th>
                        <th>Nama Pelanggan</th>
                        <th>Email</th>
                        <th>No. Telepon</th>
                        <th>Alamat</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pelangganList as $p)
                        <tr>
                            <td>#{{ $p->id_pelanggan }}</td>
                            <td><strong>{{ $p->user->nama ?? '-' }}</strong></td>
                            <td>{{ $p->user->email ?? '-' }}</td>
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

                                <!-- Modal Edit -->
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
                                                        <label class="form-label font-semibold">Email</label>
                                                        <input type="email" name="email" class="form-control" value="{{ $p->user->email }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label font-semibold">No. Telepon</label>
                                                        <input type="text" name="no_telepon" class="form-control" value="{{ $p->no_telepon }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label font-semibold">Alamat</label>
                                                        <input type="text" name="alamat" class="form-control" value="{{ $p->alamat }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label font-semibold">Password Baru (opsional)</label>
                                                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
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
                        <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada pelanggan registered.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $pelangganList->links() }}
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambahPelanggan" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('kasir.pelanggan.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold mb-0">Tambah Pelanggan Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label font-semibold">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" required placeholder="Nama Pelanggan">
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-semibold">Email</label>
                        <input type="email" name="email" class="form-control" required placeholder="email@domain.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-semibold">Password Default</label>
                        <input type="password" name="password" class="form-control" required value="password123">
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-semibold">No. Telepon / WA</label>
                        <input type="text" name="no_telepon" class="form-control" required placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-semibold">Alamat</label>
                        <input type="text" name="alamat" class="form-control" required placeholder="Alamat rumah">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold">Simpan Pelanggan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
