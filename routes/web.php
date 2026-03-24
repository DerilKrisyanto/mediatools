<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Tools\InvoiceController;
use App\Http\Controllers\Tools\BgRemoverController;
use App\Http\Controllers\Tools\LinkTreeController;
use App\Http\Controllers\Tools\SignatureController;
use App\Http\Controllers\Tools\QrController;
use App\Http\Controllers\Tools\PDFUtilitiesController;
use App\Http\Controllers\Tools\ImageConverterController;
use App\Http\Controllers\Tools\PasswordGeneratorController;
use App\Http\Controllers\Tools\MediaDownloaderController;
use App\Http\Controllers\Tools\FileConverterController;


// Halaman Utama & Tools Lain (Public)
Route::get('/', [HomeController::class,'index'])->name('home');
Route::get('/invoice', [InvoiceController::class,'index'])->name('tools.invoice');

Route::get('/tools/bgremover', [BgRemoverController::class, 'index'])
    ->name('tools.bgremover');
Route::post('/tools/bgremover/process', [BgRemoverController::class, 'process'])
    ->name('tools.bgremover.process');

// Linktree Group
Route::prefix('linktree')->group(function () {
    // PUBLIC
    Route::get('/', [LinkTreeController::class, 'index'])->name('tools.linktree');
    Route::get('/view/{id}', [LinkTreeController::class, 'show'])->name('tools.linktree.show');
    Route::post('/payment/notification', [LinkTreeController::class, 'midtransNotification']);

    // AUTH ONLY
    Route::middleware('auth')->group(function () {
        Route::post('/check-plan', [LinkTreeController::class, 'checkPlan'])->name('tools.linktree.checkplan');
        Route::post('/store', [LinkTreeController::class, 'store'])->name('tools.linktree.store');
    });

});

// Signature Group
Route::prefix('signature')->group(function () {
    // PUBLIC
    Route::get('/', [SignatureController::class, 'index'])->name('tools.signature');
    // AUTH ONLY
    Route::middleware('auth')->group(function () {
        Route::post('/store', [SignatureController::class, 'store'])->name('tools.signature.store');
    });

});

// Qr Group
Route::prefix('qr')->group(function () {
    
    Route::get('/', [QrController::class, 'index'])->name('tools.qr');

    Route::middleware('auth')->group(function () {
        Route::post('/store', [QrController::class, 'store'])->name('tools.qr.store');
    });

});

// PDF Utilities
Route::get('/pdfutilities', [PDFUtilitiesController::class, 'index'])->name('tools.pdfutilities');
Route::post('/pdfutilities/compress', [PDFUtilitiesController::class, 'compress'])->name('tools.pdfutilities.compress');

// Image Converter
Route::get('/imageconverter', [ImageConverterController::class, 'index'])
    ->name('tools.imageconverter');

// Password Generator
Route::get('/password-generator', [PasswordGeneratorController::class, 'index'])
    ->name('tools.passwordgenerator');

// Media Downloader
Route::get('/media-downloader', [MediaDownloaderController::class, 'index'])
     ->name('tools.mediadownloader');

Route::post('/media-downloader/process', [MediaDownloaderController::class, 'process'])
     ->name('tools.mediadownloader.process');


// Files Converter Group
Route::prefix('file-converter')->group(function () {
    Route::get('/', [FileConverterController::class, 'index'])   ->name('tools.fileconverter');
    Route::post('/process', [FileConverterController::class, 'process']) ->name('tools.fileconverter.process');
    Route::get('/download/{filename}', [FileConverterController::class, 'download'])->name('tools.fileconverter.download');
    Route::post('/cleanup', [FileConverterController::class, 'cleanup']) ->name('tools.fileconverter.cleanup');
});

// Standard Laravel Auth Routes
Route::middleware('auth')->group(function () {
    Route::get('/home', function () {
        return view('home.index');
    })->name('home.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';