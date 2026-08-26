<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UsersAccessController;
use App\Http\Controllers\ManagementPenyakitController;
use App\Http\Controllers\ManagementGejalaController;
use App\Http\Controllers\ManagementRelasiController;
use App\Http\Controllers\KonsultasiController;
use App\Http\Controllers\ManagementSapiController;
use App\Http\Controllers\ManagementPeternakController;
use App\Http\Controllers\ManagementMonitoringController;

// ==== AUTH ROUTES ==== //
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ==== PROTECTED ROUTES ==== //
Route::middleware('cek.session')->group(function () {

    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', fn() => view('pages.dashboard'))->name('dashboard');

    Route::prefix('pakar')->name('pakar.')->group(function () {
        Route::get('/', fn() => view('pages.dashboard'))->name('index');
        Route::get('/create', fn() => view('pages.dashboard'))->name('create');
        Route::get('/keahlian', fn() => view('pages.dashboard'))->name('keahlian');
    });

    // ==== KONSULTASI (sudah connect ke KonsultasiController) ==== //
    Route::prefix('konsultasi')->name('konsultasi.')->group(function () {
        Route::get('/',            [KonsultasiController::class, 'index'])->name('index');
        Route::get('/baru',        [KonsultasiController::class, 'create'])->name('baru');
        Route::post('/proses',     [KonsultasiController::class, 'store'])->name('proses');
        Route::get('/hasil/{id}',  [KonsultasiController::class, 'show'])->name('hasil');
    });

    Route::prefix('pengetahuan')->name('pengetahuan.')->group(function () {
        Route::get('/aturan', fn() => view('pages.dashboard'))->name('aturan');
        Route::get('/fakta', fn() => view('pages.dashboard'))->name('fakta');
        Route::get('/kategori', fn() => view('pages.dashboard'))->name('kategori');

        // ==== MANAJEMEN PENYAKIT (sudah connect ke tabel `penyakit`) ==== //
        Route::prefix('penyakit')->name('penyakit.')->group(function () {
            Route::get('/',            [ManagementPenyakitController::class, 'index'])->name('index');
            Route::post('/',           [ManagementPenyakitController::class, 'store'])->name('store');
            Route::put('/{id}',        [ManagementPenyakitController::class, 'update'])->name('update');
            Route::delete('/{id}',     [ManagementPenyakitController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('gejala')->name('gejala.')->group(function () {
            Route::get('/',        [ManagementGejalaController::class, 'index'])->name('index');
            Route::post('/',       [ManagementGejalaController::class, 'store'])->name('store');
            Route::put('/{id}',    [ManagementGejalaController::class, 'update'])->name('update');
            Route::delete('/{id}', [ManagementGejalaController::class, 'destroy'])->name('destroy');
        });

        // ==== RELASI PENYAKIT-GEJALA (nilai CF pakar) ==== //
        Route::prefix('relasi')->name('relasi.')->group(function () {
            Route::get('/',              [ManagementRelasiController::class, 'index'])->name('index');
            Route::get('/{penyakit}/edit', [ManagementRelasiController::class, 'edit'])->name('edit');
            Route::put('/{penyakit}',    [ManagementRelasiController::class, 'update'])->name('update');
        });
    });

    // ==== MANAJEMEN SAPI (sudah connect ke tabel `tb_sapi`) ==== //
    Route::prefix('sapi')->name('sapi.')->group(function () {
        Route::get('/',        [ManagementSapiController::class, 'index'])->name('index');
        Route::post('/',       [ManagementSapiController::class, 'store'])->name('store');
        Route::put('/{id}',    [ManagementSapiController::class, 'update'])->name('update');
        Route::delete('/{id}', [ManagementSapiController::class, 'destroy'])->name('destroy');
    });

    // ==== MANAJEMEN PETERNAK (sudah connect ke tabel `tb_peternak`) ==== //
    Route::prefix('peternak')->name('peternak.')->group(function () {
        Route::get('/',        [ManagementPeternakController::class, 'index'])->name('index');
        Route::post('/',       [ManagementPeternakController::class, 'store'])->name('store');
        Route::put('/{id}',    [ManagementPeternakController::class, 'update'])->name('update');
        Route::delete('/{id}', [ManagementPeternakController::class, 'destroy'])->name('destroy');
    });

    // ==== MONITORING SAPI (sehat / sakit / perlu ditinjau) ==== //
    Route::prefix('monitoring')->name('monitoring.')->group(function () {
        Route::get('/',                    [ManagementMonitoringController::class, 'index'])->name('index');
        Route::post('/{hasilId}/sakit',    [ManagementMonitoringController::class, 'tandaiSakit'])->name('tandaiSakit');
        Route::post('/{hasilId}/abaikan',  [ManagementMonitoringController::class, 'abaikan'])->name('abaikan');
        Route::post('/{sapiId}/sembuh',    [ManagementMonitoringController::class, 'tandaiSembuh'])->name('sembuh');
    });

    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/bulanan', fn() => view('pages.dashboard'))->name('bulanan');
        Route::get('/statistik', fn() => view('pages.dashboard'))->name('statistik');
    });

    Route::get('/pengaturan', fn() => view('pages.dashboard'))->name('pengaturan');

    Route::prefix('administrator')->name('administrator.')->group(function () {
        Route::get('/useraccess',         [UsersAccessController::class, 'index'])->name('useraccess');
        Route::post('/useraccess',        [UsersAccessController::class, 'store'])->name('useraccess.store');
        Route::put('/useraccess/{id}',    [UsersAccessController::class, 'update'])->name('useraccess.update');
        Route::delete('/useraccess/{id}', [UsersAccessController::class, 'destroy'])->name('useraccess.destroy');
    });

});