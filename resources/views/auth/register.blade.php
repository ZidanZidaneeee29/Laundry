@extends('layouts.app')

@section('title', 'Register Pelanggan - SINDORY')

@section('content')
<div class="row justify-content-center mt-3">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-success text-white text-center py-3">
                <h4 class="mb-0 fw-bold"><i class="bi bi-person-plus me-2"></i>Registrasi Akun SINDORY</h4>
                <small class="text-white-50">Sistem Informasi Indo Express Laundry</small>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <input type="hidden" name="role" value="pelanggan">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nama" class="form-label font-semibold">Nama Lengkap</label>
                            <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama') }}" required placeholder="Nama lengkap Anda">
                            @error('nama') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label font-semibold">Username</label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username') }}" required placeholder="Username unik">
                            @error('username') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label font-semibold">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required placeholder="email@domain.com">
                        @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="no_telepon" class="form-label font-semibold">No. Telepon / WhatsApp</label>
                            <input type="text" class="form-control @error('no_telepon') is-invalid @enderror" id="no_telepon" name="no_telepon" value="{{ old('no_telepon') }}" required placeholder="08xxxxxxxxxx">
                            @error('no_telepon') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="alamat" class="form-label font-semibold">Alamat Lengkap</label>
                            <input type="text" class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" value="{{ old('alamat') }}" required placeholder="Jl. Raya No. 123">
                            @error('alamat') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label font-semibold">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required placeholder="Minimal 6 karakter">
                            @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="password_confirmation" class="form-label font-semibold">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="Ulangi password">
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-success btn-lg fs-6 fw-bold">
                            <i class="bi bi-check-circle me-2"></i> DAFTAR SEKARANG
                        </button>
                    </div>
                </form>

                <div class="text-center mt-3 pt-3 border-top">
                    <p class="text-muted small mb-1">Sudah memiliki akun?</p>
                    <a href="{{ route('login') }}" class="text-decoration-none fw-bold">Login ke Sistem</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
