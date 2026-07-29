@extends('layouts.app')

@section('title', 'Login - SINDORY')

@section('content')
<div class="row justify-content-center mt-4">
    <div class="col-md-5">
        <div class="card shadow-sm border-0 card-interactive">
            <div class="card-header bg-white text-center py-3 border-bottom">
                <img src="{{ asset('images/logo.png') }}" alt="SINDORY Logo" height="70" class="mb-2">
                <h3 class="mb-0 fw-bold text-dark">SINDORY</h3>
                <small class="text-muted">Sistem Informasi Indo Express Laundry (75 Menit Selesai)</small>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="login" class="form-label font-semibold">Username atau Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control @error('login') is-invalid @enderror" id="login" name="login" value="{{ old('login') }}" required autofocus placeholder="Masukkan Username atau Email">
                        </div>
                        @error('login')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label font-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required placeholder="Masukkan password">
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary btn-lg fs-6 fw-bold">
                            <i class="bi bi-box-arrow-in-right me-2"></i> MASUK SYSTEM
                        </button>
                    </div>
                </form>

                <div class="text-center mt-3 pt-3 border-top">
                    <p class="text-muted small mb-1">Belum memiliki akun Pelanggan?</p>
                    <a href="{{ route('register') }}" class="text-decoration-none fw-bold">Daftar Akun Pelanggan Baru</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
