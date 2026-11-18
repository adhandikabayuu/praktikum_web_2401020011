<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\Auth\LoginController;

// Halaman login
Route::get('/login', [LoginController::class, 'show'])
    ->name('login');

// Proses login
Route::post('/login', [LoginController::class, 'login'])
    ->name('login.proses');

// Proses logout
Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout');

// Halaman buku hanya bisa diakses setelah login
Route::middleware('auth')->group(function () {
    Route::resource('buku', BukuController::class)
        ->except('show');
});
