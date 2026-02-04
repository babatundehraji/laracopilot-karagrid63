<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of users
     * GET /admin/users
     */
    public function index(Request $request)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $query = User::where('role', '!=', 'admin');

            // Filter by role
            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Search by name or email
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $users = $query->orderBy('created_at', 'desc')->paginate(15);

            return view('admin.users.index', compact('users'));
        } catch (\Exception $e) {
            Log::error('Failed to load users index', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to load users: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified user
     * GET /admin/users/{user}
     */
    public function show(User $user)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            // Load relationships
            $user->load([
                'orders' => function($query) {
                    $query->with(['vendor.user', 'service'])
                          ->orderBy('created_at', 'desc')
                          ->limit(20);
                },
                'transactions' => function($query) {
                    $query->orderBy('created_at', 'desc')->limit(20);
                },
                'conversations' => function($query) {
                    $query->with(['messages' => function($q) {
                        $q->orderBy('created_at', 'desc')->limit(5);
                    }])->orderBy('updated_at', 'desc')->limit(10);
                },
                'activityLogs' => function($query) {
                    $query->orderBy('created_at', 'desc')->limit(20);
                },
                'loginLogs' => function($query) {
                    $query->orderBy('created_at', 'desc')->limit(20);
                }
            ]);

            // Calculate stats
            $stats = [
                'total_orders' => $user->orders()->count(),
                'completed_orders' => $user->orders()->where('status', 'completed')->count(),
                'total_spent' => $user->orders()->where('payment_status', 'paid')->sum('price'),
                'total_transactions' => $user->transactions()->count(),
                'total_conversations' => $user->conversations()->count(),
            ];

            return view('admin.users.show', compact('user', 'stats'));
        } catch (\Exception $e) {
            Log::error('Failed to load user details', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to load user details: ' . $e->getMessage());
        }
    }

    /**
     * Block a user
     * POST /admin/users/{user}/block
     */
    public function block(User $user)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            if ($user->role === 'admin') {
                return back()->with('error', 'Cannot block admin users');
            }

            if ($user->status === 'suspended') {
                return back()->with('error', 'User is already suspended');
            }

            $user->update(['status' => 'suspended']);

            Log::info('Admin blocked user', [
                'admin_id' => Auth::guard('web')->id(),
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

            return back()->with('success', 'User has been suspended successfully');
        } catch (\Exception $e) {
            Log::error('Failed to block user', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to suspend user: ' . $e->getMessage());
        }
    }

    /**
     * Unblock a user
     * POST /admin/users/{user}/unblock
     */
    public function unblock(User $user)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            if ($user->status === 'active') {
                return back()->with('error', 'User is already active');
            }

            $user->update(['status' => 'active']);

            Log::info('Admin unblocked user', [
                'admin_id' => Auth::guard('web')->id(),
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

            return back()->with('success', 'User has been activated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to unblock user', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to activate user: ' . $e->getMessage());
        }
    }
}