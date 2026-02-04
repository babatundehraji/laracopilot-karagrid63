<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Show admin dashboard
     * GET /admin
     */
    public function dashboard()
    {
        try {
            $admin = Auth::guard('web')->user();

            // Get dashboard statistics
            $stats = [
                'total_users' => User::where('role', '!=', 'admin')->count(),
                'total_vendors' => Vendor::count(),
                'total_orders' => Order::count(),
                'pending_orders' => Order::where('status', 'pending')->count(),
                'total_disputes' => Dispute::count(),
                'open_disputes' => Dispute::where('status', 'pending')->count(),
                'total_revenue' => Order::where('payment_status', 'paid')->sum('price')
            ];

            // Recent orders
            $recentOrders = Order::with(['user', 'vendor', 'service'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Recent disputes
            $recentDisputes = Dispute::with(['order', 'customer', 'vendor'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            return view('admin.dashboard', compact('admin', 'stats', 'recentOrders', 'recentDisputes'));
        } catch (\Exception $e) {
            Log::error('Failed to load admin dashboard', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to load dashboard data');
        }
    }
}