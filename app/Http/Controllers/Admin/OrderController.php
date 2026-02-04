<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderEdit;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Display a listing of orders
     * GET /admin/orders
     */
    public function index(Request $request)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $query = Order::with(['user', 'vendor', 'service']);

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by date range
            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            // Filter by customer
            if ($request->filled('customer_id')) {
                $query->where('user_id', $request->customer_id);
            }

            // Filter by vendor
            if ($request->filled('vendor_id')) {
                $query->where('vendor_id', $request->vendor_id);
            }

            $orders = $query->orderBy('created_at', 'desc')->paginate(15);

            // Get customers and vendors for filter dropdowns
            $customers = User::where('role', 'customer')->orderBy('full_name')->get();
            $vendors = Vendor::with('user')->orderBy('business_name')->get();

            return view('admin.orders.index', compact('orders', 'customers', 'vendors'));
        } catch (\Exception $e) {
            Log::error('Failed to load orders index', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to load orders: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified order
     * GET /admin/orders/{order}
     */
    public function show(Order $order)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            // Load relationships
            $order->load([
                'user',
                'vendor.user',
                'service.category',
                'orderEdits' => function($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'dispute'
            ]);

            return view('admin.orders.show', compact('order'));
        } catch (\Exception $e) {
            Log::error('Failed to load order details', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to load order details: ' . $e->getMessage());
        }
    }

    /**
     * Update order status
     * POST /admin/orders/{order}/update-status
     */
    public function updateStatus(Request $request, Order $order)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $request->validate([
                'status' => 'required|in:pending,confirmed,in_progress,completed,cancelled,refunded,disputed'
            ]);

            $oldStatus = $order->status;
            $newStatus = $request->status;

            if ($oldStatus === $newStatus) {
                return back()->with('error', 'Order already has this status');
            }

            // Update order status
            $order->update(['status' => $newStatus]);

            // Log the status change in order_edits table
            OrderEdit::create([
                'order_id' => $order->id,
                'edited_by' => Auth::guard('web')->id(),
                'field_name' => 'status',
                'old_value' => $oldStatus,
                'new_value' => $newStatus,
                'reason' => 'Admin status update'
            ]);

            Log::info('Admin updated order status', [
                'admin_id' => Auth::guard('web')->id(),
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);

            return back()->with('success', "Order status updated from {$oldStatus} to {$newStatus}");
        } catch (\Exception $e) {
            Log::error('Failed to update order status', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to update order status: ' . $e->getMessage());
        }
    }
}