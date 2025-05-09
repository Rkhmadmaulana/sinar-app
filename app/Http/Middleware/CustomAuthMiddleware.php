<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CustomAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Periksa autentikasi dengan session kustom
        if (!session('authenticated')) {
            return redirect()->route('login')->with('warning', 'Silakan login terlebih dahulu');
        }
        
        return $next($request);
    }
}