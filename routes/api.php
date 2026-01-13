<?php

use App\Http\Controllers\Api\Admin\DonasiAdminController;
use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Api\DonasiController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\MeetupSpotController;
use App\Http\Controllers\Api\TransaksiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;

// Endpoint publik untuk registrasi dan login
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::get('/barang', [BarangController::class, 'index']);
Route::get('/barang/{id}', [BarangController::class, 'show']);
Route::get('kategori', [KategoriController::class, 'index']);
Route::post('/forgot-password', [NewPasswordController::class, 'forgotPassword']);

// Endpoint yang memerlukan otentikasi
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/admin/donasi', [DonasiAdminController::class, 'index']);
    Route::put('/admin/donasi/{id}', [DonasiAdminController::class, 'updateStatus']);
    
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
    Route::post('/donasi', [DonasiController::class, 'ajukanDonasi']);
    Route::get('/donasi/riwayat', [DonasiController::class, 'riwayatDonasi']);
    // Item Routes
    Route::post('/barang', [BarangController::class, 'store']);
    Route::put('/barang/{id}', [BarangController::class, 'update']);
    Route::delete('/barang/{id}', [BarangController::class, 'destroy']);
    Route::apiResource('user', \App\Http\Controllers\Api\UserController::class);
    Route::apiResource('ulasan', \App\Http\Controllers\Api\UlasanController::class);
    Route::get('meetupspot', [MeetupSpotController::class, 'index']);
    Route::apiResource('transaksi', \App\Http\Controllers\Api\TransaksiController::class);
    Route::post('/transaksi/{id}/verifikasi-akhir', [TransaksiController::class, 'verifikasiAkhir']);
});
