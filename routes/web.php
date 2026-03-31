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
    Route::get('/debug',              [FileConverterController::class, 'debug'])->name('tools.fileconverter.debug');
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

    $appUrl = rtrim(config('app.url', 'https://mediatools.cloud'), '/');

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

    // Build XML string — NO leading whitespace, starts exactly with <?xml
    $lines = [];
    $lines[] = '<?xml version="1.0" encoding="UTF-8"?>';
    $lines[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
    $lines[] = '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
    $lines[] = '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
    $lines[] = '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

    foreach ($tools as $u) {
        $fullUrl = $appUrl . $u['loc'];
        $lines[] = '  <url>';
        $lines[] = '    <loc>' . htmlspecialchars($fullUrl, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</loc>';
        $lines[] = '    <lastmod>' . $u['lastmod'] . '</lastmod>';
        $lines[] = '    <changefreq>' . $u['changefreq'] . '</changefreq>';
        $lines[] = '    <priority>' . $u['priority'] . '</priority>';

        // Add OG image for homepage
        if ($u['loc'] === '/') {
            $lines[] = '    <image:image>';
            $lines[] = '      <image:loc>' . $appUrl . '/images/og/home.png</image:loc>';
            $lines[] = '      <image:title>MediaTools — All-in-One Media Suite</image:title>';
            $lines[] = '    </image:image>';
        }

        $lines[] = '  </url>';
    }

    $lines[] = '</urlset>';

    $xml = implode("\n", $lines);

    // Use response()->make() — avoids any Blade/middleware interference
    return response()->make($xml, 200, [
        'Content-Type'  => 'application/xml; charset=UTF-8',
        'Cache-Control' => 'public, max-age=86400, stale-while-revalidate=3600',
        'X-Robots-Tag'  => 'noindex',   // sitemap itself shouldn't be indexed
    ]);

})->name('sitemap');

require __DIR__ . '/auth.php';
