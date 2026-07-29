@extends('layouts.app')

@section('title', 'Kelola User - Kasir Express Laundry')

@section('content')
<div class="row mb-3 align-items-center">
    <div class="col-md-6">
        <h4 class="fw-bold mb-0"><i class="bi bi-person-gear text-primary me-2"></i> Kelola Data User Sistem</h4>
    </div>
    <div class="col-md-6 text-md-end mt-2 mt-md-0">
        <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
            <i class="bi bi-plus-lg me-1"></i> Tambah User Baru
        </button>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <form action="{{ route('kasir.user.index') }}" method="GET" class="row g-2">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Cari Nama / Email / Role User..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-secondary w-100 fw-bold"><i class="bi bi-search me-1"></i> Cari</button>
            </div>
            <div class="col-md-3">
                <a href="{{ route('kasir.user.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#ID User</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Role Hak Akses</th>
                        <th>Tgl Terdaftar</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td>#{{ $u->id_user }}</td>
                            <td><strong>{{ $u->nama }}</strong></td>
                            <td>{{ $u->email }}</td>
                            <td>
                                <span class="badge 
                                    @if($u->role === 'pemilik') bg-danger
                                    @elseif($u->role === 'kasir') bg-primary
                                    @else bg-success @endif">
                                    {{ strtoupper($u->role) }}
                                </span>
                            </td>
                            <td>{{ $u->created_at ? $u->created_at->format('d/m/Y') : '-' }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditUser-{{ $u->id_user }}">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                @if($u->id_user !== auth()->id())
                                    <form action="{{ route('kasir.user.destroy', $u->id_user) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus user ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Hapus</button>
                                    </form>
                                @endif

                                <!-- Modal Edit User -->
                                <div class="modal fade text-start" id="modalEditUser-{{ $u->id_user }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('kasir.user.update', $u->id_user) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header bg-warning">
                                                    <h5 class="modal-title fw-bold mb-0">Edit User: {{ $u->nama }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label font-semibold">Nama Lengkap</label>
                                                        <input type="text" name="nama" class="form-control" value="{{ $u->nama }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label font-semibold">Email</label>
                                                        <input type="email" name="email" class="form-control" value="{{ $u->email }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label font-semibold">Role Hak Akses</label>
                                                        <select name="role" class="form-select" required>
                                                            <option value="kasir" {{ $u->role === 'kasir' ? 'selected' : '' }}>Kasir</option>
                                                            <option value="pelanggan" {{ $u->role === 'pelanggan' ? 'selected' : '' }}>Pelanggan</option>
                                                            <option value="pemilik" {{ $u->role === 'pemilik' ? 'selected' : '' }}>Pemilik</option>
                                                        </select>
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
                        <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada user.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $users->links() }}
        </div>
    </div>
</div>

<!-- Modal Tambah User -->
<div class="modal fade" id="modalTambahUser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('kasir.user.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold mb-0">Tambah User Sistem Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label font-semibold">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" required placeholder="Nama User">
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-semibold">Email</label>
                        <input type="email" name="email" class="form-control" required placeholder="email@domain.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-semibold">Role Hak Akses</label>
                        <select name="role" class="form-select" required>
                            <option value="kasir">Kasir</option>
                            <option value="pelanggan">Pelanggan</option>
                            <option value="pemilik">Pemilik</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-semibold">Password</label>
                        <input type="password" name="password" class="form-control" required placeholder="Minimal 6 karakter">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold">Simpan User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
