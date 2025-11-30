<?php

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
    Route::apiResource('barang', \App\Http\Controllers\Api\BarangController::class);
    Route::apiResource('user', \App\Http\Controllers\Api\UserController::class);
});
