<?php

use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\MeetupSpotController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;


// Endpoint publik untuk registrasi dan login
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);


// Endpoint yang memerlukan otentikasi
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
    
    // Item Routes
    
    Route::apiResource('user', \App\Http\Controllers\Api\UserController::class);
    Route::get('kategori', [KategoriController::class, 'index']);
    Route::get('meetupspot', [MeetupSpotController::class, 'index']);
    Route::apiResource('barang', \App\Http\Controllers\Api\BarangController::class);
});
