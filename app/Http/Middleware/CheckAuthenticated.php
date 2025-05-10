<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('authenticated')) {
            return redirect()->route('login')->with('failed', 'Anda Harus Login');
        }
        
        return $next($request);
    }
}
