<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RejectAdminUser
{
    /**
     * Handle an incoming request.
     *
     * Blocks admin users from accessing API endpoints.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and is an admin
        if ($request->user() && $request->user()->role === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Admin users cannot access this endpoint',
                'data' => null
            ], 403);
        }

        return $next($request);
    }
}