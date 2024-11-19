<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
//use App\Http\Controllers\Api\UserController;
// use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\JenisBarangController;
use App\Http\Controllers\Api\StatusBarangController;
use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Api\BarangMasukController;
use App\Http\Controllers\Api\BarangKeluarController;
use App\Http\Controllers\Api\KeperluanController;
use App\Http\Controllers\Api\PermintaanBarangKeluarController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\SerialNumberController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\ProfileController;

/* Autentikasi JWT */
Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [RegisterController::class, 'login']);
Route::middleware(['jwt.verify'])->get('/user', [RegisterController::class, 'getUserData']);

/* Autentikasi Lama (Sanctum) */
// Route::post('register', [AuthController::class, 'register']);
// Route::post('login', [AuthController::class, 'login']);
// Route::post('logout', [AuthController::class, 'logout']);
// Route::middleware('auth:api')->get('me', [AuthController::class, 'me']);

/* Role Management */
Route::get('/roles', [RoleController::class, 'index']);
Route::get('/roles/create', [RoleController::class, 'create']);
Route::post('/roles', [RoleController::class, 'store']);
Route::get('/roles/{id}/edit', [RoleController::class, 'edit']);
Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
Route::get('/roles/assign', [RoleController::class, 'indexAssignRole']);
Route::put('/roles/assign/{user}', [RoleController::class, 'assignRole'])->name('roles.assign');

/* Hanya Bisa Diakses Jika Sudah Login */
Route::middleware(['jwt.verify'])->group(function() {
    /* Dashboard */
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/daily-activity', [DashboardController::class, 'getDailyActivity']);

    /* User Management */
    Route::put('/user/update', [ProfileController::class, 'update']);

    /* Barang */
    Route::get('/barang/create', [BarangController::class, 'create']);
    Route::post('/barang', [BarangController::class, 'store']);
    Route::get('/barang/{id}', [BarangController::class, 'edit']);
    Route::put('/barang/{id}', [BarangController::class, 'update']);
    Route::delete('/barang/{id}', [BarangController::class, 'delete']);
    Route::post('/barang/delete-selected', [BarangController::class, 'deleteSelected']);

    /* Permintaan Barang Keluar */
    Route::get('/permintaanbarangkeluar', [PermintaanBarangKeluarController::class, 'index']); // direct
    Route::get('/permintaanbarangkeluar/create', [PermintaanBarangKeluarController::class, 'create']);
    Route::get('/permintaanbarangkeluar/onlyfor', [PermintaanBarangKeluarController::class, 'indexForOneUser']);
    Route::post('/permintaanbarangkeluar', [PermintaanBarangKeluarController::class, 'store']);
    Route::get('/permintaanbarangkeluar/get-by-jenis/{id}', [PermintaanBarangKeluarController::class, 'getBarangByJenis']);
    Route::get('/permintaanbarangkeluar/get-by-barang/{id}', [PermintaanBarangKeluarController::class, 'getSerialNumberByBarang']);
    Route::post('/permintaanbarangkeluar/update-status', [PermintaanBarangKeluarController::class, 'updateStatus']);
    Route::post('/permintaanbarangkeluar/insert-project-po', [PermintaanBarangKeluarController::class, 'insertProjectPO']);
    Route::put('/permintaanbarangkeluar/{id}', [PermintaanBarangKeluarController::class, 'update']);
    Route::delete('/permintaanbarangkeluar/{id}', [PermintaanBarangKeluarController::class, 'delete']);
    Route::get('/permintaanbarangkeluar/{id}', [PermintaanBarangKeluarController::class, 'show']); // direct
    Route::get('/permintaanbarangkeluar/show-detail-sn/{id}', [PermintaanBarangKeluarController::class, 'showDetailSN']); // direct
    Route::get('/permintaanbarangkeluar/get-stok/{barang_id}', [PermintaanBarangKeluarController::class, 'getStok']);
});

    /* Permintaan Barang Keluar */
    Route::get('/permintaanbarangkeluar/selectSN/{id}', [PermintaanBarangKeluarController::class, 'selectSN'])->name('permintaanbarangkeluar.selectSN');
    Route::post('/permintaanbarangkeluar/setSN', [PermintaanBarangKeluarController::class, 'setSN'])->name('permintaanbarangkeluar.setSN');

    /* Barang */
    Route::get('/barang', [BarangController::class, 'index']);

    /* Supplier */
    Route::get('/suppliers', [SupplierController::class, 'index']);
    Route::post('/suppliers', [SupplierController::class, 'store']);
    Route::get('/suppliers/{id}', [SupplierController::class, 'edit']);
    Route::put('/suppliers/{id}', [SupplierController::class, 'update']);
    Route::delete('/suppliers/{id}', [SupplierController::class, 'delete']);
    Route::post('/suppliers/delete-selected', [SupplierController::class, 'deleteSelected']);

    /* Jenis Barang */
    Route::get('/jenisbarang', [JenisBarangController::class, 'index']);
    Route::post('/jenisbarang', [JenisBarangController::class, 'store']);
    Route::get('/jenisbarang/{id}', [JenisBarangController::class, 'edit']);
    Route::put('/jenisbarang/{id}', [JenisBarangController::class, 'update']);
    Route::delete('/jenisbarang/{id}', [JenisBarangController::class, 'delete']);
    Route::post('/jenisbarang/delete-selected', [JenisBarangController::class, 'deleteSelected']);

    /* Status Barang */
    Route::get('/statusbarang', [StatusBarangController::class, 'index']);
    Route::post('/statusbarang', [StatusBarangController::class, 'store']);
    Route::get('/statusbarang/{id}', [StatusBarangController::class, 'edit']);
    Route::put('/statusbarang/{id}', [StatusBarangController::class, 'update']);
    Route::delete('/statusbarang/{id}', [StatusBarangController::class, 'delete']);
    Route::post('/statusbarang/delete-selected', [StatusBarangController::class, 'deleteSelected']);

    /* Barang Masuk */
    Route::get('/barangmasuk', [BarangMasukController::class, 'index']);
    Route::get('/barangmasuk/create/{id?}', [BarangMasukController::class, 'create']);
    Route::get('/barangmasuk/get-by-jenis/{id}', [BarangMasukController::class, 'getBarangByJenis']);
    Route::post('/barangmasuk', [BarangMasukController::class, 'store']);
    Route::post('/barangmasuk/excel', [BarangMasukController::class, 'storeExcel']);
    Route::put('/barangmasuk/{id}', [BarangMasukController::class, 'update']);
    Route::delete('/barangmasuk/{id}', [BarangMasukController::class, 'delete']);
    Route::post('/barangmasuk/delete-selected', [BarangMasukController::class, 'deleteSelected']);
    Route::get('/barangmasuk/{id}', [BarangMasukController::class, 'show']);

    /* Barang Keluar */
    Route::get('/barangkeluar', [BarangKeluarController::class, 'index'])->name('barangkeluar.index');
    Route::get('/barangkeluar/create/{id?}', [BarangKeluarController::class, 'create'])->name('barangkeluar.create');
    Route::get('/barangkeluar/get-by-jenis/{id}', [BarangKeluarController::class, 'getBarangByJenis']);
    Route::post('/barangkeluar/store', [BarangKeluarController::class, 'store'])->name('barangkeluar.store');
    Route::put('/barangkeluar/update/{id}', [BarangKeluarController::class, 'update'])->name('barangkeluar.update');
    Route::get('/barangkeluar/delete/{id}', [BarangKeluarController::class, 'delete'])->name('barangkeluar.delete');
    Route::post('/barangkeluar/delete-selected', [BarangKeluarController::class, 'deleteSelected']);
    Route::get('/barangkeluar/{id}', [BarangKeluarController::class, 'show']);

    /* Jenis Keperluan */
    Route::get('/keperluan', [KeperluanController::class, 'index']);
    Route::post('/keperluan', [KeperluanController::class, 'store']);
    Route::get('/keperluan/{id}', [KeperluanController::class, 'edit']);
    Route::put('/keperluan/{id}', [KeperluanController::class, 'update']);
    Route::delete('/keperluan/{id}', [KeperluanController::class, 'delete']);
    Route::post('/keperluan/delete-selected', [KeperluanController::class, 'deleteSelected']);

    /* Customer */
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::get('/customers/{id}', [CustomerController::class, 'edit']);
    Route::put('/customers/{id}', [CustomerController::class, 'update']);
    Route::delete('/customers/{id}', [CustomerController::class, 'delete']);
    Route::post('/customers/delete-selected', [CustomerController::class, 'deleteSelected']);

    /* Laporan */
    Route::get('/laporan/stok', [LaporanController::class, 'stok']);
    Route::get('/laporan/stok/{id}', [LaporanController::class, 'stokDetail']);
    Route::get('/laporan/barangmasuk', [LaporanController::class, 'barangmasuk'])->name('laporan.barangmasuk.index');
    Route::get('/laporan/barangmasuk/{id}', [LaporanController::class, 'getDetailBarangMasuk'])->name('laporan.barangmasuk.getDetailBarangMasuk');
    Route::get('/laporan/barangkeluar', [LaporanController::class, 'barangkeluar'])->name('laporan.barangkeluar.index');
    Route::get('/laporan/barangkeluar/{id}', [LaporanController::class, 'getDetailBarangKeluar'])->name('laporan.barangkeluar.getDetailBarangKeluar');

    /* Unknown - Hanya untuk Test */
    Route::get('/serialnumber', [SerialNumberController::class, 'index']);
    //Route::get('/serialnumber/{id}', [SerialNumberController::class, 'cekByBarang']);
    Route::get('/serialnumber/{id}', [SerialNumberController::class, 'cekBySN']);

// Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);
// Route::middleware('auth:sanctum')->get('user', [AuthController::class, 'user']);

// Route::post('auth', function(Request $request){
//     $valid = Auth::attempt($request->all());

//     if($valid){
//         $user = Auth::user();
//         $user->api_token = Str::random(100);
//         $user->save();

//         $user->makeVisible('api_token');

//         return $user;
//     }

//     return response()->json([
//         'message' => 'Email dan Password tidak ditemukan'
//     ], 404);
// });

// Route::post('user/register', [UserController::class, 'register']);
