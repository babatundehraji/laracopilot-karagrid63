<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminWeb
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated with web guard
        if (!Auth::guard('web')->check()) {
            return redirect()->route('admin.login')
                ->with('error', 'Please login to access the admin panel');
        }

        $user = Auth::guard('web')->user();

        // Check if user has admin role
        if ($user->role !== 'admin') {
            Auth::guard('web')->logout();
            return redirect()->route('admin.login')
                ->with('error', 'You do not have permission to access the admin panel');
        }

        return $next($request);
    }
}