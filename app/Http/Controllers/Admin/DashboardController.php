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

class DashboardController extends Controller
{
    /**
     * Show admin dashboard
     * GET /admin
     */
    public function index()
    {
        // Key statistics
        $totalUsers = User::where('role', '!=', 'admin')->count();
        $totalVendors = Vendor::count();
        $approvedVendors = Vendor::where('status', 'approved')->count();
        $pendingVendors = Vendor::where('status', 'pending')->count();
        $totalServices = Service::count();
        
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $activeOrders = Order::whereIn('status', ['confirmed', 'in_progress'])->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $disputedOrders = Order::where('status', 'disputed')->count();
        
        $totalTransactionsSum = Transaction::sum('amount');
        $totalDisputes = Dispute::count();
        $openDisputes = Dispute::where('status', 'open')->count();

        // Recent data
        $recentUsers = User::where('role', '!=', 'admin')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentOrders = Order::with(['service', 'customer'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentVendors = Vendor::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalVendors',
            'approvedVendors',
            'pendingVendors',
            'totalServices',
            'totalOrders',
            'pendingOrders',
            'activeOrders',
            'completedOrders',
            'disputedOrders',
            'totalTransactionsSum',
            'totalDisputes',
            'openDisputes',
            'recentUsers',
            'recentOrders',
            'recentVendors'
        ));
    }
}