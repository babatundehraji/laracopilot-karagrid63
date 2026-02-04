<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * Ensures user is authenticated via web guard and has admin role.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated via web guard
        if (!auth()->guard('web')->check()) {
            return redirect()->route('admin.login')->with('error', 'Please login to access admin panel');
        }

        // Check if authenticated user has admin role
        if (auth()->user()->role !== 'admin') {
            auth()->logout();
            return redirect()->route('admin.login')->with('error', 'Unauthorized. Admin access only.');
        }

        return $next($request);
    }
}