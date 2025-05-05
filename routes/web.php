<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Monolog\Level;
use Illuminate\Auth\Middleware\Authorize;
Route::pattern('id','[0-9]+'); // artinya ketika ada parameter {id}, maka harus berupa angka

Route::get('login', [AuthController::class,'login'])->name('login');
Route::post('login', [AuthController::class,'postlogin']);
Route::get('logout', [AuthController::class,'logout'])->middleware('auth');
Route::get('/register', [AuthController::class, 'create_ajax']);
Route::post('/register', [AuthController::class, 'store_ajax']);


Route::middleware(['auth'])->group(function(){ // artinya semua route di dalam group ini harus login dulu }

// masukkan semua route yang perlu autentikasi di sini

    Route::get('/', [WelcomeController::class, 'index']);
    Route::get('/user/profil', [UserController::class, 'profil']);
    Route::post('/user/editFoto/{id}', [UserController::class, 'updateFoto_ajax']);
    
    Route::middleware(['authorize:ADM'])->group(function(){
        Route::group(['prefix' => 'user'], function () {
            Route::get('/', [UserController::class, 'index']); // menampilkan halaman awal user
            Route::post('/list', [UserController::class, 'list']); // menampilkan data user dalam bentuk json untuk datatables
            Route::get('/create', [UserController::class, 'create']); // menampilkan halaman form tambah user
            Route::post('/', [UserController::class, 'store']); // menyimpan data user baru
            Route::get('/create_ajax', [UserController::class,'create_ajax']); //menampilkan halaman form tambah user ajax
            Route::post('/ajax', [UserController::class,'store_ajax']); //menyimpan data user baru
            Route::get('/{id}', [UserController::class, 'show']); // menampilkan detail user
            Route::get('/{id}/edit', [UserController::class, 'edit']); // menampilkan halaman form edit user
            Route::put('/{id}', [UserController::class, 'update']); // menyimpan perubahan data user
            Route::get('/{id}/edit_ajax', [UserController::class,'edit_ajax']);//menampilkan halaman form edit user ajax
            Route::put('/{id}/update_ajax', [UserController::class,'update_ajax']);//menyimpan perubahan data user
            Route::get('/{id}/delete_ajax', [UserController::class,'confirm_ajax']);// untuk tampilan form konfirm user ajax
            Route::delete('/{id}/delete_ajax', [UserController::class,'delete_ajax']);// untuk menghapus data user
            Route::delete('/{id}', [UserController::class, 'destroy']); // menghapus data user
            Route::get('/import',[UserController::class,'import']); // ajax form upload excel
            Route::post('/import_ajax',[UserController::class,'import_ajax']); // ajax import excel
            Route::get('/export_excel',[UserController::class,'export_excel']); // ajax export excel
            Route::get('/export_pdf',[UserController::class,'export_pdf']); // ajax export pdf
        });

        Route::group(['prefix' => 'level'], function () {
            Route::get('/', [LevelController::class, 'index']); // menampilkan halaman awal level
            Route::post('/list', [LevelController::class, 'list']); // menampilkan data level dalam bentuk json untuk datatables
            Route::get('/create', [LevelController::class, 'create']); // menampilkan halaman form tambah level
            Route::post('/', [LevelController::class, 'store']); // menyimpan data level baru
            Route::get('/create_ajax', [LevelController::class,'create_ajax']); //menampilkan halaman form tambah level ajax
            Route::post('/ajax', [LevelController::class,'store_ajax']); //menyimpan data level baru
            Route::get('/{id}', [LevelController::class, 'show']); // menampilkan detail level
            Route::get('/{id}/edit', [LevelController::class, 'edit']); // menampilkan halaman form edit level
            Route::put('/{id}', [LevelController::class, 'update']); // menyimpan perubahan data level
            Route::get('/{id}/edit_ajax', [LevelController::class,'edit_ajax']);//menampilkan halaman form edit level ajax
            Route::put('/{id}/update_ajax', [LevelController::class,'update_ajax']);//menyimpan perubahan data level
            Route::get('/{id}/delete_ajax', [LevelController::class,'confirm_ajax']);// untuk tampilan form konfirm level ajax
            Route::delete('/{id}/delete_ajax', [LevelController::class,'delete_ajax']);// untuk menghapus data level
            Route::delete('/{id}', [LevelController::class, 'destroy']); // menghapus data level
            Route::get('/import',[LevelController::class,'import']); // ajax form upload excel
            Route::post('/import_ajax',[LevelController::class,'import_ajax']); // ajax import excel
            Route::get('/export_excel',[LevelController::class,'export_excel']); // ajax export excel
            Route::get('/export_pdf',[LevelController::class,'export_pdf']); // ajax export pdf
        });
    });

    
    Route::middleware(['authorize:ADM,MNG'])->group(function () {
        Route::group(['prefix' => 'barang'], function () {
            Route::get('/', [BarangController::class, 'index']); // menampilkan halaman awal barang
            Route::post('/list', [BarangController::class, 'list']); // menampilkan data barang dalam bentuk json untuk datatables
            Route::get('/create', [BarangController::class, 'create']); // menampilkan halaman form tambah Barang
            Route::post('/', [BarangController::class, 'store']); // menyimpan data Barang baru
            Route::get('/create_ajax', [BarangController::class,'create_ajax']); //menampilkan halaman form tambah barang ajax
            Route::post('/ajax', [BarangController::class,'store_ajax']); //menyimpan data barang baru
            Route::get('/{id}', [BarangController::class, 'show']); // menampilkan detail Barang
            Route::get('/{id}/edit', [BarangController::class, 'edit']); // menampilkan halaman form edit Barang
            Route::put('/{id}', [BarangController::class, 'update']); // menyimpan perubahan data Barang
            Route::get('/{id}/edit_ajax', [BarangController::class,'edit_ajax']);//menampilkan halaman form edit barang ajax
            Route::put('/{id}/update_ajax', [BarangController::class,'update_ajax']);//menyimpan perubahan data barang
            Route::get('/{id}/delete_ajax', [BarangController::class,'confirm_ajax']);// untuk tampilan form konfirm barang ajax
            Route::delete('/{id}/delete_ajax', [BarangController::class,'delete_ajax']);
            Route::delete('/{id}', [BarangController::class, 'destroy']); // menghapus data Barang
            Route::get('/import',[BarangController::class,'import']); // ajax form upload excel
            Route::post('/import_ajax',[BarangController::class,'import_ajax']); // ajax import excel
            Route::get('/export_excel',[BarangController::class,'export_excel']); // ajax export excel
            Route::get('/export_pdf',[BarangController::class,'export_pdf']); // ajax export pdf
        });
        Route::group(['prefix' => 'kategori'], function () {
            Route::get('/', [KategoriController::class, 'index']); // menampilkan halaman awal kategori
            Route::post('/list', [KategoriController::class, 'list']); // menampilkan data kategori dalam bentuk json untuk datatables
            Route::get('/create', [KategoriController::class, 'create']); // menampilkan halaman form tambah kategori
            Route::post('/', [KategoriController::class, 'store']); // menyimpan data kategori baru
            Route::get('/create_ajax', [KategoriController::class,'create_ajax']); //menampilkan halaman form tambah Kategori ajax
            Route::post('/ajax', [KategoriController::class,'store_ajax']); //menyimpan data Kategori baru
            Route::get('/{id}', [KategoriController::class, 'show']); // menampilkan detail kategori
            Route::get('/{id}/edit', [KategoriController::class, 'edit']); // menampilkan halaman form edit kategori
            Route::put('/{id}', [KategoriController::class, 'update']); // menyimpan perubahan data kategori
            Route::get('/{id}/edit_ajax', [KategoriController::class,'edit_ajax']);//menampilkan halaman form edit Kategori ajax
            Route::put('/{id}/update_ajax', [KategoriController::class,'update_ajax']);//menyimpan perubahan data Kategori
            Route::get('/{id}/delete_ajax', [KategoriController::class,'confirm_ajax']);// untuk tampilan form konfirm Kategori ajax
            Route::delete('/{id}/delete_ajax', [KategoriController::class,'delete_ajax']);// untuk menghapus data Kategori
            Route::delete('/{id}', [KategoriController::class, 'destroy']); // menghapus data kategori
            Route::get('/import',[KategoriController::class,'import']); // ajax form upload excel
            Route::post('/import_ajax',[KategoriController::class,'import_ajax']); // ajax import excel
            Route::get('/export_excel',[KategoriController::class,'export_excel']); // ajax export excel
            Route::get('/export_pdf',[KategoriController::class,'export_pdf']); // ajax export pdf
        });
        Route::group(['prefix' => 'supplier'], function () {
            Route::get('/', [SupplierController::class, 'index']); // menampilkan halaman awal stok
            Route::post('/list', [SupplierController::class, 'list']); // menampilkan data Supplier dalam bentuk json untuk datatables
            Route::get('/create', [SupplierController::class, 'create']); // menampilkan halaman form tambah Supplier
            Route::post('/', [SupplierController::class, 'store']); // menyimpan data Supplier baru
            Route::get('/create_ajax', [SupplierController::class,'create_ajax']); //menampilkan halaman form tambah supplier ajax
            Route::post('/ajax', [SupplierController::class,'store_ajax']); //menyimpan data supplier baru
            Route::get('/{id}', [SupplierController::class, 'show']); // menampilkan detail Supplier
            Route::get('/{id}/edit', [SupplierController::class, 'edit']); // menampilkan halaman form edit Supplier
            Route::put('/{id}', [SupplierController::class, 'update']); // menyimpan perubahan data arang
            Route::put('/{id}', [SupplierController::class, 'update']); // menyimpan perubahan data supplier
            Route::get('/{id}/edit_ajax', [SupplierController::class,'edit_ajax']);//menampilkan halaman form edit supplier ajax
            Route::put('/{id}/update_ajax', [SupplierController::class,'update_ajax']);//menyimpan perubahan data supplier
            Route::get('/{id}/delete_ajax', [SupplierController::class,'confirm_ajax']);// untuk tampilan form konfirm supplier ajax
            Route::delete('/{id}/delete_ajax', [SupplierController::class,'delete_ajax']);
            Route::delete('/{id}', [SupplierController::class, 'destroy']); // menghapus data Supplier
            Route::get('/import',[SupplierController::class,'import']); // ajax form upload excel
            Route::post('/import_ajax',[SupplierController::class,'import_ajax']); // ajax import excel
            Route::get('/export_excel',[SupplierController::class,'export_excel']); // ajax export excel
            Route::get('/export_pdf',[SupplierController::class,'export_pdf']); // ajax export pdf
        });
    });

    Route::middleware(['authorize:ADM,MNG,STF'])->group(function(){
        Route::group(['prefix' => 'stok'], function () {
            Route::get('/', [StokController::class, 'index']); // menampilkan halaman awal stok
            Route::post('/list', [StokController::class, 'list']); // menampilkan data stok dalam bentuk json untuk datatables
            Route::get('/create', [StokController::class, 'create']); // menampilkan halaman form tambah stok
            Route::post('/', [StokController::class, 'store']); // menyimpan data stok baru
            Route::get('/create_ajax', [StokController::class,'create_ajax']); //menampilkan halaman form tambah stok ajax
            Route::post('/ajax', [StokController::class,'store_ajax']); //menyimpan data stok baru
            Route::get('/{id}', [StokController::class, 'show']); // menampilkan detail stok
            Route::get('/{id}/edit', [StokController::class, 'edit']); // menampilkan halaman form edit stok
            Route::put('/{id}', [StokController::class, 'update']); // menyimpan perubahan data stok
            Route::get('/{id}/edit_ajax', [StokController::class,'edit_ajax']);//menampilkan halaman form edit stok ajax
            Route::get('/{id}/show_ajax', [StokController::class,'show_ajax']);//menampilkan halaman form edit stok ajax
            Route::put('/{id}/update_ajax', [StokController::class,'update_ajax']);//menyimpan perubahan data stok
            Route::get('/{id}/delete_ajax', [StokController::class,'confirm_ajax']);// untuk tampilan form konfirm stok ajax
            Route::delete('/{id}/delete_ajax', [StokController::class,'delete_ajax']);
            Route::delete('/{id}', [StokController::class, 'destroy']); // menghapus data stok
            Route::get('/import',[StokController::class,'import']); // ajax form upload excel
            Route::post('/import_ajax',[StokController::class,'import_ajax']); // ajax import excel
            Route::get('/export_excel',[StokController::class,'export_excel']); // ajax export excel
            Route::get('/export_pdf',[StokController::class,'export_pdf']); // ajax export pdf
        });
    
        Route::group(['prefix' => 'transaksi'], function () {
            Route::get('/', [TransaksiController::class, 'index']); // Menampilkan halaman awal transaksi
            Route::post('/list', [TransaksiController::class, 'list']); // Menampilkan data transaksi dalam bentuk JSON untuk DataTables
            Route::get('/show_ajax/{id}', [TransaksiController::class, 'show_ajax']); // Menampilkan detail transaksi
            Route::get('/create_ajax', [TransaksiController::class,'create_ajax']); //menampilkan halaman form tambah Transaksi ajax
            Route::post('/ajax', [TransaksiController::class,'store_ajax']); //menyimpan data Transaksi baru
            Route::get('/{id}', [TransaksiController::class, 'show']); // menampilkan detail Transaksi
            Route::get('/{id}/edit', [TransaksiController::class, 'edit']); // menampilkan halaman form edit Transaksi
            Route::put('/{id}', [TransaksiController::class, 'update']); // menyimpan perubahan data Transaksi
            Route::get('/{id}/edit_ajax', [TransaksiController::class,'edit_ajax']);//menampilkan halaman form edit Transaksi ajax
            Route::get('/{id}/show_ajax', [TransaksiController::class,'show_ajax']);//menampilkan halaman form edit Transaksi ajax
            Route::put('/{id}/update_ajax', [TransaksiController::class,'update_ajax']);//menyimpan perubahan data Transaksi
            Route::get('/{id}/delete_ajax', [TransaksiController::class,'confirm_ajax']);// untuk tampilan form konfirm Transaksi ajax
            Route::delete('/{id}/delete_ajax', [TransaksiController::class,'delete_ajax']);
            Route::delete('/{id}', [TransaksiController::class, 'destroy']); // menghapus data Transaksi
            Route::get('/import',[TransaksiController::class,'import']); // ajax form upload excel
            Route::post('/import_ajax',[TransaksiController::class,'import_ajax']); // ajax import excel
            Route::get('/export_excel',[TransaksiController::class,'export_excel']); // ajax export excel
            Route::get('/export_pdf',[TransaksiController::class,'export_pdf']); // ajax export pdf
        });
    });
});
