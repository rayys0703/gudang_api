<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
//use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\JenisBarangController;
use App\Http\Controllers\Api\StatusBarangController;
use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Api\BarangMasukController;
use App\Http\Controllers\Api\KeperluanController;
use App\Http\Controllers\Api\PermintaanBarangKeluarController;

/* Autentikasi */
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);
Route::middleware('auth:api')->get('me', [AuthController::class, 'me']);

/* Perlu Login */
Route::middleware('auth:api')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::get('/suppliers', [SupplierController::class, 'index']);
    Route::post('/suppliers', [SupplierController::class, 'store']);
    Route::get('/suppliers/{id}', [SupplierController::class, 'edit']);
    Route::put('/suppliers/{id}', [SupplierController::class, 'update']);
    Route::delete('/suppliers/{id}', [SupplierController::class, 'delete']);
    Route::post('/suppliers/delete-selected', [SupplierController::class, 'deleteSelected']);

    Route::get('/customers', [CustomerController::class, 'index']);
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::get('/customers/{id}', [CustomerController::class, 'edit']);
    Route::put('/customers/{id}', [CustomerController::class, 'update']);
    Route::delete('/customers/{id}', [CustomerController::class, 'delete']);
    Route::post('/customers/delete-selected', [CustomerController::class, 'deleteSelected']);

    Route::get('/jenisbarang', [JenisBarangController::class, 'index']);
    Route::post('/jenisbarang', [JenisBarangController::class, 'store']);
    Route::get('/jenisbarang/{id}', [JenisBarangController::class, 'edit']);
    Route::put('/jenisbarang/{id}', [JenisBarangController::class, 'update']);
    Route::delete('/jenisbarang/{id}', [JenisBarangController::class, 'delete']);
    Route::post('/jenisbarang/delete-selected', [JenisBarangController::class, 'deleteSelected']);

    Route::get('/statusbarang', [StatusBarangController::class, 'index']);
    Route::post('/statusbarang', [StatusBarangController::class, 'store']);
    Route::get('/statusbarang/{id}', [StatusBarangController::class, 'edit']);
    Route::put('/statusbarang/{id}', [StatusBarangController::class, 'update']);
    Route::delete('/statusbarang/{id}', [StatusBarangController::class, 'delete']);
    Route::post('/statusbarang/delete-selected', [StatusBarangController::class, 'deleteSelected']);

    Route::get('/barangmasuk', [BarangMasukController::class, 'index']);
    Route::get('/barangmasuk/create/{id?}', [BarangMasukController::class, 'create']);
    Route::get('/barangmasuk/get-by-jenis/{id}', [BarangMasukController::class, 'getBarangByJenis']);
    Route::post('/barangmasuk', [BarangMasukController::class, 'store']);
    Route::put('/barangmasuk/{id}', [BarangMasukController::class, 'update']);
    Route::delete('/barangmasuk/{id}', [BarangMasukController::class, 'delete']);
    Route::post('/barangmasuk/delete-selected', [BarangMasukController::class, 'deleteSelected']);
    Route::get('/barangmasuk/{id}', [BarangMasukController::class, 'show']);

    Route::get('/keperluan', [KeperluanController::class, 'index']);
    Route::post('/keperluan', [KeperluanController::class, 'store']);
    Route::get('/keperluan/{id}', [KeperluanController::class, 'edit']);
    Route::put('/keperluan/{id}', [KeperluanController::class, 'update']);
    Route::delete('/keperluan/{id}', [KeperluanController::class, 'delete']);
    Route::post('/keperluan/delete-selected', [KeperluanController::class, 'deleteSelected']);
});

Route::get('/barang', [BarangController::class, 'index']);
Route::get('/barang/create', [BarangController::class, 'create']);
Route::post('/barang', [BarangController::class, 'store']);
Route::get('/barang/{id}', [BarangController::class, 'edit']);
Route::put('/barang/{id}', [BarangController::class, 'update']);
Route::delete('/barang/{id}', [BarangController::class, 'delete']);
Route::post('/barang/delete-selected', [BarangController::class, 'deleteSelected']);

Route::get('/permintaanbarangkeluar', [PermintaanBarangKeluarController::class, 'index']);
Route::get('/permintaanbarangkeluar/create', [PermintaanBarangKeluarController::class, 'create']);
Route::get('/permintaanbarangkeluar/get-by-jenis/{id}', [PermintaanBarangKeluarController::class, 'getBarangByJenis']);
Route::get('/permintaanbarangkeluar/get-by-barang/{id}', [PermintaanBarangKeluarController::class, 'getSerialNumberByBarang']);
Route::post('/permintaanbarangkeluar', [PermintaanBarangKeluarController::class, 'store']);
Route::post('/permintaanbarangkeluar/update-status', [PermintaanBarangKeluarController::class, 'updateStatus']);
Route::put('/permintaanbarangkeluar/{id}', [PermintaanBarangKeluarController::class, 'update']);
Route::delete('/permintaanbarangkeluar/{id}', [PermintaanBarangKeluarController::class, 'delete']);
Route::get('/permintaanbarangkeluar/{id}', [PermintaanBarangKeluarController::class, 'show']);


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
