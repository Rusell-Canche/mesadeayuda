<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPasswordTemporal
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->password_temporal) {
            // Evitar loop si ya está en la ruta de cambio de contraseña
            if (!$request->routeIs('password.change') && !$request->routeIs('password.update')) {
                return redirect()->route('password.change');
            }
        }
        return $next($request);
    }
}
