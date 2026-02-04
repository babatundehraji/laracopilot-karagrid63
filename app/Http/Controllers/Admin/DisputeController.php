<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DisputeController extends Controller
{
    /**
     * Display a listing of disputes
     * GET /admin/disputes
     */
    public function index(Request $request)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $query = Dispute::with(['order.user', 'order.vendor', 'customer']);

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $disputes = $query->orderBy('created_at', 'desc')->paginate(15);

            return view('admin.disputes.index', compact('disputes'));
        } catch (\Exception $e) {
            Log::error('Failed to load disputes index', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to load disputes: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified dispute
     * GET /admin/disputes/{dispute}
     */
    public function show(Dispute $dispute)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            // Load relationships
            $dispute->load([
                'order.user',
                'order.vendor.user',
                'order.service',
                'customer'
            ]);

            return view('admin.disputes.show', compact('dispute'));
        } catch (\Exception $e) {
            Log::error('Failed to load dispute details', [
                'dispute_id' => $dispute->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to load dispute details: ' . $e->getMessage());
        }
    }

    /**
     * Resolve a dispute
     * POST /admin/disputes/{dispute}/resolve
     */
    public function resolve(Request $request, Dispute $dispute)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $request->validate([
                'resolution' => 'required|in:refund_customer,release_vendor,partial_refund,no_action',
                'resolution_notes' => 'required|string|min:10'
            ]);

            if ($dispute->status === 'resolved') {
                return back()->with('error', 'This dispute has already been resolved');
            }

            // Update dispute
            $dispute->update([
                'status' => 'resolved',
                'resolution' => $request->resolution,
                'resolution_notes' => $request->resolution_notes,
                'resolved_at' => now(),
                'resolved_by' => Auth::guard('web')->id()
            ]);

            // Update order status based on resolution
            $order = $dispute->order;
            switch ($request->resolution) {
                case 'refund_customer':
                    $order->update(['status' => 'cancelled']);
                    break;
                case 'release_vendor':
                    $order->update(['status' => 'completed']);
                    break;
                case 'partial_refund':
                    // Keep order status as is, just mark dispute resolved
                    break;
                case 'no_action':
                    // Keep order status as is
                    break;
            }

            Log::info('Admin resolved dispute', [
                'admin_id' => Auth::guard('web')->id(),
                'dispute_id' => $dispute->id,
                'dispute_number' => $dispute->dispute_number,
                'resolution' => $request->resolution
            ]);

            return back()->with('success', 'Dispute has been resolved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to resolve dispute', [
                'dispute_id' => $dispute->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to resolve dispute: ' . $e->getMessage());
        }
    }
}