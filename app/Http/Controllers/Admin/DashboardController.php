<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\Order;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Show admin dashboard
     * GET /admin
     */
    public function index()
    {
        try {
            $admin = Auth::guard('web')->user();

            // Get dashboard statistics
            $stats = [
                // Users
                'total_users' => User::where('role', '!=', 'admin')->count(),
                
                // Vendors
                'total_vendors' => Vendor::count(),
                'approved_vendors' => Vendor::where('status', 'approved')->count(),
                'pending_vendors' => Vendor::where('status', 'pending')->count(),
                
                // Services
                'total_services' => Service::count(),
                'approved_services' => Service::where('status', 'approved')->count(),
                
                // Orders by status
                'pending_orders' => Order::where('status', 'pending')->count(),
                'active_orders' => Order::whereIn('status', ['confirmed', 'in_progress'])->count(),
                'completed_orders' => Order::where('status', 'completed')->count(),
                'disputed_orders' => Order::where('status', 'disputed')->count(),
                'total_orders' => Order::count(),
                
                // Disputes
                'total_disputes' => Dispute::count(),
                'open_disputes' => Dispute::whereIn('status', ['pending', 'under_review'])->count(),
                
                // Transaction volume
                'transaction_volume' => Transaction::where('status', 'completed')->sum('amount'),
                'total_transactions' => Transaction::count(),
            ];

            // Latest users (non-admin)
            $latestUsers = User::where('role', '!=', 'admin')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Latest vendors
            $latestVendors = Vendor::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Latest orders
            $latestOrders = Order::with(['user', 'vendor', 'service'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Latest disputes
            $latestDisputes = Dispute::with(['order.user', 'order.vendor', 'customer'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            return view('admin.dashboard.index', compact(
                'admin',
                'stats',
                'latestUsers',
                'latestVendors',
                'latestOrders',
                'latestDisputes'
            ));
        } catch (\Exception $e) {
            Log::error('Failed to load admin dashboard', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to load dashboard data: ' . $e->getMessage());
        }
    }
}