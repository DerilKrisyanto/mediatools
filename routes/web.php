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
use App\Http\Controllers\Tools\MetadataSanitizerController;
use App\Http\Controllers\Tools\ProposalBuilderController;
use App\Http\Controllers\Tools\PasFotoController;
use App\Http\Controllers\Tools\FinanceController;

// ========== Halaman Utama ========== //
Route::get('/', [HomeController::class, 'index'])->name('home');

// ══ SEO URL Redirects — URL ramah kata kunci → URL asli ══
// Redirect 301 = Google transfer "link juice" ke URL tujuan
Route::redirect('/background-remover',       '/bg',               301)->name('tools.bgremover.seo');
Route::redirect('/remove-background',        '/bg',               301);
Route::redirect('/hapus-background',         '/bg',               301);
Route::redirect('/pdf-tools',                '/pdfutilities',     301);
Route::redirect('/pdf-utilities',            '/pdfutilities',     301);
Route::redirect('/compress-pdf',             '/pdfutilities',     301);
Route::redirect('/merge-pdf',                '/pdfutilities',     301);
Route::redirect('/qr-code-generator',        '/qr',               301);
Route::redirect('/buat-qr-code',             '/qr',               301);
Route::redirect('/image-converter',          '/imageconverter',   301);
Route::redirect('/compress-gambar',          '/imageconverter',   301);
Route::redirect('/resize-gambar',            '/imageconverter',   301);
Route::redirect('/convert-pdf',              '/file-converter',   301);
Route::redirect('/pdf-to-word',              '/file-converter',   301);
Route::redirect('/download-video',           '/media-downloader', 301);
Route::redirect('/download-youtube',         '/media-downloader', 301);
Route::redirect('/download-tiktok',          '/media-downloader', 301);
Route::redirect('/password-generator',       '/password-generator', 301);
Route::redirect('/email-signature',          '/signature',        301);
Route::redirect('/buat-invoice',             '/invoice',          301);
Route::redirect('/invoice-generator',        '/invoice',          301);
Route::redirect('/pas-foto',                 '/pasfoto',          301);
Route::redirect('/photo-booth',              '/fotobox',          301);



// ========== Daftar Route Tools ==========

// ========== Invoice ========== //
Route::get('/invoice', [InvoiceController::class, 'index'])->name('tools.invoice');

// ========== Background Remover ========== //
Route::prefix('bg')->group(function () {
    Route::get('/',         [BgRemoverController::class, 'index'])  ->name('tools.bgremover');
    Route::post('/process', [BgRemoverController::class, 'process'])->name('tools.bgremover.process');
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

// ========== Finance (auth-only) ========== //
Route::prefix('finance')->group(function () {
    Route::middleware('auth')->group(function () {
        Route::get('/',                    [FinanceController::class, 'index'])  ->name('tools.finance');
        Route::post('/transactions',       [FinanceController::class, 'store'])  ->name('tools.finance.transactions.store');
        Route::delete('/transactions/{id}',[FinanceController::class, 'destroy'])->name('tools.finance.transactions.destroy');
        Route::get('/print',               [FinanceController::class, 'print'])  ->name('tools.finance.print');
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
Route::get('/pdfutilities',         [PDFUtilitiesController::class, 'index'])   ->name('tools.pdfutilities');
Route::post('/pdfutilities/compress',[PDFUtilitiesController::class, 'compress'])->name('tools.pdfutilities.compress');

// ========== Image Converter ========== //
Route::get('/imageconverter', [ImageConverterController::class, 'index'])->name('tools.imageconverter');

// ========== Password Generator ========== //
Route::get('/password-generator', [PasswordGeneratorController::class, 'index'])->name('tools.passwordgenerator');

// ========== Media Downloader ========== //
Route::get('/media-downloader',                  [MediaDownloaderController::class, 'index'])   ->name('tools.mediadownloader');
Route::post('/media-downloader/process',         [MediaDownloaderController::class, 'process']) ->name('tools.mediadownloader.process');
Route::get('/media-downloader/download/{token}', [MediaDownloaderController::class, 'download'])->name('tools.mediadownloader.download');
Route::post('/media-downloader/cleanup',         [MediaDownloaderController::class, 'cleanup']) ->name('tools.mediadownloader.cleanup');

// ========== File Converter ========== //
Route::prefix('file-converter')->group(function () {
    Route::get('/',                 [FileConverterController::class, 'index'])   ->name('tools.fileconverter');
    Route::post('/process',         [FileConverterController::class, 'process']) ->name('tools.fileconverter.process');
    Route::get('/download/{token}', [FileConverterController::class, 'download'])->name('tools.fileconverter.download');
    Route::post('/cleanup',         [FileConverterController::class, 'cleanup']) ->name('tools.fileconverter.cleanup');
});

// ========== Metadata & Privacy Sanitizer ========== //
Route::prefix('sanitizer')->group(function () {
    Route::get('/', [MetadataSanitizerController::class, 'index'])
        ->name('tools.sanitizer');

    Route::post('/scan', [MetadataSanitizerController::class, 'scan'])
        ->name('tools.sanitizer.scan')
        ->middleware('throttle:20,1');

    Route::post('/process', [MetadataSanitizerController::class, 'process'])
        ->name('tools.sanitizer.process')
        ->middleware('throttle:20,1');

    Route::get('/download/{token}', [MetadataSanitizerController::class, 'download'])
        ->name('tools.sanitizer.download')
        ->where('token', '[a-zA-Z0-9]{48}')
        ->middleware('throttle:60,1');
});

// ========== PasFoto / Smart Photo Studio ========== //
Route::prefix('pasfoto')->group(function () {
    Route::get('/', [PasFotoController::class, 'index'])->name('tools.pasfoto');
});

// ========== Fotobox Online ========== //
Route::get('/fotobox', function () {
    return view('tools.fotobox.index');
})->name('tools.fotobox');

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
    $today  = now()->format('Y-m-d');

    $urls = [
        // ── Tier 1: Beranda ──
        ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'weekly', 'lastmod' => $today,
            'image' => ['loc' => '/images/og/home.png', 'title' => 'MediaTools — Tools Digital Gratis: Invoice, PDF, QR Code, Background Remover']],

        // ── Tier 2: Tools volume tinggi ──
        ['loc' => '/bg',               'priority' => '0.9', 'changefreq' => 'monthly', 'lastmod' => $today,
            'image' => ['loc' => '/images/og/bgremover.png',        'title' => 'Background Remover Gratis — Hapus Background Foto Online dengan AI']],
        ['loc' => '/invoice',          'priority' => '0.9', 'changefreq' => 'monthly', 'lastmod' => $today,
            'image' => ['loc' => '/images/og/invoice.png',   'title' => 'Invoice Generator Gratis Online — Buat Tagihan Profesional']],
        ['loc' => '/pdfutilities',     'priority' => '0.9', 'changefreq' => 'monthly', 'lastmod' => $today,
            'image' => ['loc' => '/images/og/pdfutilities.png',       'title' => 'PDF Utilities — Merge, Split & Compress PDF Gratis Online']],
        ['loc' => '/file-converter',   'priority' => '0.9', 'changefreq' => 'monthly', 'lastmod' => $today,
            'image' => ['loc' => '/images/og/fileconverter.png', 'title' => 'File Converter Online — PDF ke Word, Excel, JPG Gratis']],
        ['loc' => '/imageconverter',   'priority' => '0.9', 'changefreq' => 'monthly', 'lastmod' => $today,
            'image' => ['loc' => '/images/og/imageconverter.png',     'title' => 'Image Converter Gratis — Resize, Compress & Konversi Gambar Online']],
        ['loc' => '/media-downloader', 'priority' => '0.9', 'changefreq' => 'monthly', 'lastmod' => $today,
            'image' => ['loc' => '/images/og/mediadownloader.png',     'title' => 'Media Downloader — Download YouTube, TikTok & Instagram Gratis']],

        // ── Tier 3: Tools branding & keamanan ──
        ['loc' => '/linktree',           'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => $today,
            'image' => ['loc' => '/images/og/linktree.png',  'title' => 'LinkTree Builder Gratis — Buat Halaman Link in Bio Profesional']],
        ['loc' => '/qr',                 'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => $today,
            'image' => ['loc' => '/images/og/qr.png',        'title' => 'QR Code Generator Gratis — Buat QR Code Custom & Branded']],
        ['loc' => '/password-generator', 'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => $today,
            'image' => ['loc' => '/images/og/passwordgenerator.png',  'title' => 'Password Generator Gratis — Buat Password Kuat & Aman Instan']],
        ['loc' => '/signature',          'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => $today,
            'image' => ['loc' => '/images/og/signature.png', 'title' => 'Email Signature Generator Profesional Gratis — Gmail & Outlook']],
        ['loc' => '/sanitizer',          'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => $today,
            'image' => ['loc' => '/images/og/home.png', 'title' => 'File Privacy Sanitizer — Hapus Metadata & Lindungi Privasi File']],
        ['loc' => '/fotobox', 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => $today,
            'image' => ['loc' => '/images/og/home.png', 'title' => 'FotoBox Online Gratis — Photo Booth 6 Foto + Template | MediaTools']],
        ['loc' => '/pasfoto', 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => $today,
            'image' => ['loc' => '/images/og/home.png', 'title' => 'Smart Photo Studio — Pas Foto Online Gratis 2x3 3x4 4x6 | MediaTools']]

        // ── Finance: auth-required, tidak diindex Google ──
        // (dikecualikan dari sitemap karena membutuhkan login)
    ];

    $lines   = [];
    $lines[] = '<?xml version="1.0" encoding="UTF-8"?>';
    $lines[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
    $lines[] = '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
    $lines[] = '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
    $lines[] = '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9';
    $lines[] = '          http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

    foreach ($urls as $u) {
        $fullUrl = rtrim(preg_replace('/^http:\/\//', 'https://', $appUrl . $u['loc']), '/');
        if ($u['loc'] === '/') $fullUrl = rtrim($appUrl, '/');

        $lines[] = '  <url>';
        $lines[] = '    <loc>' . htmlspecialchars($fullUrl, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</loc>';
        $lines[] = '    <lastmod>' . $u['lastmod'] . '</lastmod>';
        $lines[] = '    <changefreq>' . $u['changefreq'] . '</changefreq>';
        $lines[] = '    <priority>' . $u['priority'] . '</priority>';

        if (!empty($u['image'])) {
            $imgUrl  = $appUrl . $u['image']['loc'];
            $lines[] = '    <image:image>';
            $lines[] = '      <image:loc>'   . htmlspecialchars($imgUrl,           ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</image:loc>';
            $lines[] = '      <image:title>' . htmlspecialchars($u['image']['title'], ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</image:title>';
            $lines[] = '    </image:image>';
        }
        $lines[] = '  </url>';
    }

    $lines[] = '</urlset>';

    return response()->make(implode("\n", $lines), 200, [
        'Content-Type'  => 'application/xml; charset=UTF-8',
        'Cache-Control' => 'public, max-age=43200, stale-while-revalidate=3600',
        'X-Robots-Tag'  => 'noindex',
    ]);
})->name('sitemap');

require __DIR__ . '/auth.php';
