<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminAuthController extends Controller
{
    /**
     * Show admin login form
     * GET /admin/login
     */
    public function showLogin()
    {
        // Redirect to dashboard if already logged in as admin
        if (Auth::guard('web')->check() && Auth::guard('web')->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /**
     * Handle admin login
     * POST /admin/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];

        // Attempt authentication with web guard
        if (Auth::guard('web')->attempt($credentials, $request->has('remember'))) {
            $user = Auth::guard('web')->user();

            // Check if user has admin role
            if ($user->role !== 'admin') {
                Auth::guard('web')->logout();

                Log::warning('Non-admin user attempted to access admin panel', [
                    'email' => $request->email,
                    'role' => $user->role
                ]);

                return back()->withErrors([
                    'email' => 'You do not have permission to access the admin panel'
                ])->withInput($request->only('email'));
            }

            // Regenerate session for security
            $request->session()->regenerate();

            Log::info('Admin user logged in', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return redirect()->intended(route('admin.dashboard'))
                ->with('success', 'Welcome back, ' . $user->full_name);
        }

        Log::warning('Failed admin login attempt', [
            'email' => $request->email
        ]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records'
        ])->withInput($request->only('email'));
    }

    /**
     * Handle admin logout
     * POST /admin/logout
     */
    public function logout(Request $request)
    {
        $user = Auth::guard('web')->user();

        if ($user) {
            Log::info('Admin user logged out', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'You have been logged out successfully');
    }
}