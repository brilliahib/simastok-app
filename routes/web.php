<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\PergerakanBarangController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('admin')->group(function () {
        Route::get('/kasir', [KasirController::class, 'index'])->name('kasir.index');
        Route::post('/kasir/checkout', [KasirController::class, 'checkout'])->name('kasir.checkout');
        Route::get('/kasir/transaksi', [KasirController::class, 'history'])->name('kasir.history');
        Route::get('/kasir/transaksi/export.pdf', [KasirController::class, 'exportHistoryPdf'])->name('kasir.transaksi.export');
        Route::get('/kasir/transaksi/{transaksi}/nota.pdf', [KasirController::class, 'receiptPdf'])->whereNumber('transaksi')->name('kasir.transaksi.nota');
        Route::get('/kasir/transaksi/{transaksi}', [KasirController::class, 'show'])->whereNumber('transaksi')->name('kasir.transaksi.show');

        Route::get('/barang/create', [BarangController::class, 'create'])->name('barang.create');
        Route::post('/barang', [BarangController::class, 'store'])->name('barang.store');
        Route::get('/barang/{barang}/edit', [BarangController::class, 'edit'])->name('barang.edit');
        Route::put('/barang/{barang}', [BarangController::class, 'update'])->name('barang.update');
        Route::delete('/barang/{barang}', [BarangController::class, 'destroy'])->name('barang.destroy');
        Route::resource('kategori', KategoriController::class)->except(['show']);
        Route::resource('supplier', SupplierController::class)->except(['show']);
    });

    Route::get('/barang/masuk', [PergerakanBarangController::class, 'createMasuk'])->name('barang.masuk');
    Route::post('/barang/masuk', [PergerakanBarangController::class, 'storeMasuk'])->name('barang.storeMasuk');
    Route::get('/barang/masuk/nota/{notaPenerimaanBarang}', [PergerakanBarangController::class, 'downloadNota'])->name('barang.masuk.nota');
    Route::get('/barang/keluar', [PergerakanBarangController::class, 'createKeluar'])->name('barang.keluar');
    Route::post('/barang/keluar', [PergerakanBarangController::class, 'storeKeluar'])->name('barang.storeKeluar');
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export', [LaporanController::class, 'exportPDF'])->name('laporan.export');
    Route::get('/laporan/barang-masuk', [LaporanController::class, 'barangMasuk'])->name('laporan.barangMasuk');
    Route::get('/laporan/barang-masuk/export', [LaporanController::class, 'exportBarangMasukPDF'])->name('laporan.barangMasuk.export');
    Route::get('/barang', [BarangController::class, 'index'])->name('barang.index');
    Route::get('/barang/{barang}', [BarangController::class, 'show'])->name('barang.show');
});

require __DIR__ . '/auth.php';
