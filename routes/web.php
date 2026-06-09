<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProgressiveQrCodeController;
use App\Http\Controllers\QrCodeDownloadController;
use App\Livewire\QrCode\QrCodeGenerator;
use App\Livewire\QrCode\QrCodeIndex;
use App\Livewire\QrCode\QrCodeShow;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::get('/p/{token}', ProgressiveQrCodeController::class)->name('qr-code.progressive');

Route::middleware('auth')->group(function (): void {
    Route::view('/', 'pages.home')->name('home');
    Route::get('/qr-code/generator', QrCodeGenerator::class)->name('qr-code.generator');
    Route::get('/qr-codes', QrCodeIndex::class)->name('qr-code.index');
    Route::get('/qr-codes/{qrCode}/edit', QrCodeGenerator::class)->name('qr-code.edit');
    Route::get('/qr-codes/{qrCode}', QrCodeShow::class)->name('qr-code.show');
    Route::get('/qr-codes/{qrCode}/download', QrCodeDownloadController::class)->name('qr-code.download');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
