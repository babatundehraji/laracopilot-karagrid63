<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminAuthController extends Controller
{
    /**
     * Show admin login form
     * GET /admin/login
     */
    public function showLogin()
    {
        // If already authenticated as admin, redirect to dashboard
        if (auth()->check() && auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /**
     * Authenticate admin user
     * POST /admin/login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $email = $request->email;
        $password = $request->password;

        // Find user
        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
        }

        // Check if user is admin
        if ($user->role !== 'admin') {
            return back()->withErrors(['email' => 'Unauthorized. Admin access only.'])->withInput();
        }

        // Check password
        if (!Hash::check($password, $user->password)) {
            return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
        }

        // Check if user is suspended
        if ($user->status === 'suspended') {
            return back()->withErrors(['email' => 'Account suspended. Please contact support.'])->withInput();
        }

        // Login user via web guard
        Auth::guard('web')->login($user, $request->filled('remember'));

        // Update last login
        $user->updateLastLogin();

        return redirect()->route('admin.dashboard')->with('success', 'Welcome back, ' . $user->full_name);
    }

    /**
     * Logout admin user
     * POST /admin/logout
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Logged out successfully');
    }
}