<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanonicalUrl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // 1. Redirect http → https
        if (!$request->isSecure() && app()->environment('production')) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        // 2. Redirect trailing slash (kecuali root)
        $url = $request->fullUrl();

        if ($request->path() != '/' && substr($url, -1) == '/') {
            return redirect(rtrim($url, '/'), 301);
        }

        return $next($request);
    }
}
