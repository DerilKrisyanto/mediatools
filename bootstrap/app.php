<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'linktree/payment/notification',
        ]);
        $middleware->append(\App\Http\Middleware\CanonicalUrl::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // ── Antisipasi 419 Page Expired ───────────────────────────────────
        $exceptions->render(function (TokenMismatchException $e, $request) {
            if ($request->routeIs('logout') || $request->is('logout')) {
                \Illuminate\Support\Facades\Auth::guard('web')->logout();
                if ($request->hasSession()) {
                    $request->session()->flush();
                }
                return redirect('/')->with('status', 'Anda telah berhasil keluar.');
            }
            return redirect()->route('login')
                ->withErrors([
                    'email' => 'Sesi Anda telah berakhir karena tidak aktif. Silakan masuk kembali.',
                ]);
        });

    })->create();
