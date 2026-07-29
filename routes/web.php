<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Pelanggan\MonitoringController;
use App\Http\Controllers\Kasir\TransaksiController as KasirTransaksiController;
use App\Http\Controllers\Kasir\PelangganController as KasirPelangganController;
use App\Http\Controllers\Kasir\UserController as KasirUserController;
use App\Http\Controllers\Pemilik\DashboardController as PemilikDashboardController;

// Public & Landing / Monitoring Routes
Route::get('/', [MonitoringController::class, 'index'])->name('monitoring');
Route::get('/monitoring', [MonitoringController::class, 'index'])->name('pelanggan.monitoring');
Route::get('/riwayat', [MonitoringController::class, 'riwayat'])->name('pelanggan.riwayat');

// Guest Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Kasir Routes
    Route::prefix('kasir')->name('kasir.')->group(function () {
        Route::get('/transaksi', [KasirTransaksiController::class, 'index'])->name('transaksi.index');
        Route::get('/transaksi/create', [KasirTransaksiController::class, 'create'])->name('transaksi.create');
        Route::post('/transaksi/predict', [KasirTransaksiController::class, 'predictApi'])->name('transaksi.predict');
        Route::post('/transaksi', [KasirTransaksiController::class, 'store'])->name('transaksi.store');
        Route::get('/transaksi/{id}', [KasirTransaksiController::class, 'show'])->name('transaksi.show');
        Route::patch('/transaksi/{id}/status', [KasirTransaksiController::class, 'updateStatus'])->name('transaksi.update-status');

        // Kelola Pelanggan
        Route::get('/pelanggan', [KasirPelangganController::class, 'index'])->name('pelanggan.index');
        Route::post('/pelanggan', [KasirPelangganController::class, 'store'])->name('pelanggan.store');
        Route::put('/pelanggan/{id}', [KasirPelangganController::class, 'update'])->name('pelanggan.update');
        Route::delete('/pelanggan/{id}', [KasirPelangganController::class, 'destroy'])->name('pelanggan.destroy');

        // Kelola User
        Route::get('/user', [KasirUserController::class, 'index'])->name('user.index');
        Route::post('/user', [KasirUserController::class, 'store'])->name('user.store');
        Route::put('/user/{id}', [KasirUserController::class, 'update'])->name('user.update');
        Route::delete('/user/{id}', [KasirUserController::class, 'destroy'])->name('user.destroy');
    });

    // Pemilik Routes
    Route::prefix('pemilik')->name('pemilik.')->group(function () {
        Route::get('/dashboard', [PemilikDashboardController::class, 'index'])->name('dashboard');
        Route::get('/monitoring', [PemilikDashboardController::class, 'monitoring'])->name('monitoring');
        Route::get('/laporan', [PemilikDashboardController::class, 'laporan'])->name('laporan');
    });
});
