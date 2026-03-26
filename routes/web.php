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

// ========== Halaman Utama ========== //
Route::get('/', [HomeController::class, 'index'])->name('home');

// ========== Invoice ========== //
Route::get('/invoice', [InvoiceController::class, 'index'])->name('tools.invoice');

// ========== Background Remover ========== //
Route::prefix('bg')->group(function () {
    Route::get('/',        [BgRemoverController::class, 'index'])  ->name('tools.bgremover');
    Route::post('/process',[BgRemoverController::class, 'process'])->name('tools.bgremover.process');
});

// ========== LinkTree ========== //
Route::prefix('linktree')->group(function () {
    Route::get('/',               [LinkTreeController::class, 'index'])->name('tools.linktree');
    Route::get('/view/{id}',      [LinkTreeController::class, 'show']) ->name('tools.linktree.show');
    Route::post('/payment/notification', [LinkTreeController::class, 'midtransNotification']);

    Route::middleware('auth')->group(function () {
        Route::post('/check-plan', [LinkTreeController::class, 'checkPlan'])->name('tools.linktree.checkplan');
        Route::post('/store',      [LinkTreeController::class, 'store'])    ->name('tools.linktree.store');
    });
});

// ========== Email Signature ========== //
Route::prefix('signature')->group(function () {
    Route::get('/', [SignatureController::class, 'index'])->name('tools.signature');

    Route::middleware('auth')->group(function () {
        Route::post('/store', [SignatureController::class, 'store'])->name('tools.signature.store');
    });
});

// ========== QR Code ========== //
Route::prefix('qr')->group(function () {
    Route::get('/', [QrController::class, 'index'])->name('tools.qr');

    Route::middleware('auth')->group(function () {
        Route::post('/store', [QrController::class, 'store'])->name('tools.qr.store');
    });
});

// ========== PDF Utilities ========== //
Route::get('/pdfutilities', [PDFUtilitiesController::class, 'index'])->name('tools.pdfutilities');
Route::post('/pdfutilities/compress', [PDFUtilitiesController::class, 'compress'])->name('tools.pdfutilities.compress');

// ========== Image Converter ========== //
Route::get('/imageconverter', [ImageConverterController::class, 'index'])->name('tools.imageconverter');

// ========== Password Generator ========== //
Route::get('/password-generator', [PasswordGeneratorController::class, 'index'])->name('tools.passwordgenerator');

// ========== Media Downloader ========== //
Route::get('/media-downloader',        [MediaDownloaderController::class, 'index'])  ->name('tools.mediadownloader');
Route::post('/media-downloader/process',[MediaDownloaderController::class, 'process'])->name('tools.mediadownloader.process');

// ========== File Converter ========== //
Route::prefix('file-converter')->group(function () {
    Route::get('/',                   [FileConverterController::class, 'index'])   ->name('tools.fileconverter');
    Route::post('/process',           [FileConverterController::class, 'process']) ->name('tools.fileconverter.process');
    Route::get('/download/{filename}',[FileConverterController::class, 'download'])->name('tools.fileconverter.download');
    Route::post('/cleanup',           [FileConverterController::class, 'cleanup']) ->name('tools.fileconverter.cleanup');
});

// ========== Auth Routes ========== //
Route::middleware('auth')->group(function () {
    Route::get('/home', function () {
        return view('home.index');
    })->name('home.index');

    Route::get('/profile',    [ProfileController::class, 'edit'])   ->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update']) ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ========== SITEMAP.XML ========== //
Route::get('/sitemap.xml', function () {
    $tools = [
        ['loc' => '/',                    'priority' => '1.0', 'changefreq' => 'weekly',  'lastmod' => '2025-01-01'],
        ['loc' => '/invoice',             'priority' => '0.9', 'changefreq' => 'monthly', 'lastmod' => '2025-01-01'],
        ['loc' => '/pdfutilities',        'priority' => '0.9', 'changefreq' => 'monthly', 'lastmod' => '2025-01-01'],
        ['loc' => '/file-converter',      'priority' => '0.9', 'changefreq' => 'monthly', 'lastmod' => '2025-01-01'],
        ['loc' => '/imageconverter',      'priority' => '0.9', 'changefreq' => 'monthly', 'lastmod' => '2025-01-01'],
        ['loc' => '/bg',                  'priority' => '0.9', 'changefreq' => 'monthly', 'lastmod' => '2025-01-01'],
        ['loc' => '/media-downloader',    'priority' => '0.9', 'changefreq' => 'monthly', 'lastmod' => '2025-01-01'],
        ['loc' => '/linktree',            'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => '2025-01-01'],
        ['loc' => '/qr',                  'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => '2025-01-01'],
        ['loc' => '/password-generator',  'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => '2025-01-01'],
        ['loc' => '/signature',           'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => '2025-01-01'],
    ];

    $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
    $xml .= '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
    $xml .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"' . "\n";
    $xml .= '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9' . "\n";
    $xml .= '        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

    foreach ($tools as $u) {
        $xml .= "  <url>\n";
        $xml .= "    <loc>https://mediatools.cloud{$u['loc']}</loc>\n";
        $xml .= "    <lastmod>{$u['lastmod']}</lastmod>\n";
        $xml .= "    <changefreq>{$u['changefreq']}</changefreq>\n";
        $xml .= "    <priority>{$u['priority']}</priority>\n";
        if ($u['loc'] === '/') {
            $xml .= "    <image:image>\n";
            $xml .= "      <image:loc>https://mediatools.cloud/images/og/home.png</image:loc>\n";
            $xml .= "      <image:title>MediaTools — All-in-One Media Suite</image:title>\n";
            $xml .= "    </image:image>\n";
        }
        $xml .= "  </url>\n";
    }

    $xml .= '</urlset>';

    return response($xml, 200, [
        'Content-Type'  => 'application/xml; charset=utf-8',
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->name('sitemap');

require __DIR__ . '/auth.php';
