<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PengembalianController;
use App\Http\Controllers\ReservasiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\DashboardController;


// Login GET — tanpa middleware guest agar bisa redirect saat sudah login
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.store');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', fn () => Inertia::render('Dashboard/AdminDashboard'))->name('dashboard');
    });

    Route::middleware('role:admin,pustakawan')->prefix('pustakawan')->name('pustakawan.')->group(function () {
        Route::get('/dashboard', fn () => Inertia::render('Dashboard/PustakawanDashboard'))->name('dashboard');
    });

    Route::middleware('role:admin,pustakawan,anggota')->prefix('anggota')->name('anggota.')->group(function () {
        Route::get('/dashboard', fn () => Inertia::render('Dashboard/AnggotaDashboard'))->name('dashboard');
    });

    // Buku - write access hanya admin/pustakawan
    Route::middleware('role:admin,pustakawan')->group(function () {
        Route::get('buku/create', [BukuController::class, 'create'])->name('buku.create');
        Route::post('buku', [BukuController::class, 'store'])->name('buku.store');
        Route::get('buku/{buku}/edit', [BukuController::class, 'edit'])->name('buku.edit');
        Route::put('buku/{buku}', [BukuController::class, 'update'])->name('buku.update');
        Route::delete('buku/{buku}', [BukuController::class, 'destroy'])->name('buku.destroy');
    });
    
    // Buku - read access untuk semua role
    Route::middleware('role:admin,pustakawan,anggota')->group(function () {
        Route::get('buku', [BukuController::class, 'index'])->name('buku.index');
        Route::get('buku/{buku}', [BukuController::class, 'show'])->name('buku.show');
    });

    // Anggota Routes - Sprint 3
    Route::middleware('role:admin,pustakawan')->group(function () {
        Route::resource('anggota', AnggotaController::class)->parameters([
        'anggota' => 'anggota',
    ]);
    Route::get('anggota/{anggota}/cetak-kartu', [AnggotaController::class, 'cetakKartu'])
        ->name('anggota.cetak-kartu');
    });
    // Peminjaman Routes - Sprint 4
    Route::middleware('role:admin,pustakawan')->group(function () {
        Route::resource('peminjaman', PeminjamanController::class)->except(['edit', 'update']);
        Route::get('peminjaman-riwayat', [PeminjamanController::class, 'riwayat'])->name('peminjaman.riwayat');
    });
    // Riwayat peminjaman untuk anggota
    Route::middleware('role:anggota')->group(function () {
        Route::get('peminjaman-saya', [PeminjamanController::class, 'peminjamansaya'])->name('peminjaman.saya');
    });
    // Pengembalian Routes - Sprint 5
    Route::middleware('role:admin,pustakawan')->group(function () {
        Route::get('pengembalian', [PengembalianController::class, 'index'])->name('pengembalian.index');
        Route::get('pengembalian/{peminjaman}', [PengembalianController::class, 'show'])->name('pengembalian.show');
        Route::post('pengembalian/{peminjaman}/proses', [PengembalianController::class, 'proses'])->name('pengembalian.proses');
        Route::post('pengembalian/{peminjaman}/perpanjang', [PengembalianController::class, 'perpanjang'])->name('pengembalian.perpanjang');
        Route::get('denda', [PengembalianController::class, 'denda'])->name('pengembalian.denda');
        Route::post('denda/{denda}/bayar', [PengembalianController::class, 'bayarDenda'])->name('pengembalian.bayar-denda');
    });
    Route::middleware('role:anggota')->group(function () {
        Route::post('peminjaman-saya/{peminjaman}/perpanjang', [PeminjamanController::class, 'perpanjangMandiri'])->name('peminjaman.perpanjang-mandiri');
    });
    // Reservasi Routes - Sprint 6
    Route::middleware('role:admin,pustakawan,anggota')->group(function () {
        Route::get('reservasi', [ReservasiController::class, 'index'])->name('reservasi.index');
        Route::get('reservasi/create', [ReservasiController::class, 'create'])->name('reservasi.create');
        Route::post('reservasi', [ReservasiController::class, 'store'])->name('reservasi.store');
        Route::delete('reservasi/{reservasi}', [ReservasiController::class, 'destroy'])->name('reservasi.destroy');
        Route::post('reservasi/{reservasi}/ambil', [ReservasiController::class, 'ambil'])->name('reservasi.ambil');
    });
    Route::middleware('role:admin,pustakawan')->group(function () {
        Route::get('reservasi-kelola', [ReservasiController::class, 'kelola'])->name('reservasi.kelola');
    });
    // Laporan Routes - Sprint 7
    Route::middleware('role:admin,pustakawan')->prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::get('statistik-peminjaman', [LaporanController::class, 'statistikPeminjaman'])->name('statistik-peminjaman');
        Route::get('buku-terpopuler', [LaporanController::class, 'bukuTerpopuler'])->name('buku-terpopuler');
        Route::get('anggota-teraktif', [LaporanController::class, 'anggotaTeraktif'])->name('anggota-teraktif');
        Route::get('denda-terkumpul', [LaporanController::class, 'dendaTerkumpul'])->name('denda-terkumpul');
        Route::get('ekspor-pdf', [LaporanController::class, 'eksporPDF'])->name('ekspor-pdf');
        Route::get('ekspor-excel', [LaporanController::class, 'eksporExcel'])->name('ekspor-excel');
    });
    // Admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
    });

    // Pustakawan
    Route::middleware('role:admin,pustakawan')->prefix('pustakawan')->name('pustakawan.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'pustakawan'])->name('dashboard');
    });

    // Anggota
    Route::middleware('role:admin,pustakawan,anggota')->prefix('anggota')->name('anggota.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'anggota'])->name('dashboard');
    });
});

Route::get('/', function () {
    return redirect()->route('login');
});