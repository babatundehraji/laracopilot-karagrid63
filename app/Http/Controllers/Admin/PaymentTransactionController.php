<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentTransactionController extends Controller
{
    /**
     * Display a listing of payment transactions
     * GET /admin/payment-transactions
     */
    public function index(Request $request)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $query = PaymentTransaction::with(['user', 'transaction']);

            // Filter by gateway
            if ($request->filled('gateway')) {
                $query->where('gateway', $request->gateway);
            }

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

            $paymentTransactions = $query->orderBy('created_at', 'desc')->paginate(15);

            // Calculate stats
            $stats = [
                'total_processed' => PaymentTransaction::where('status', 'success')->sum('amount'),
                'success_count' => PaymentTransaction::where('status', 'success')->count(),
                'pending_count' => PaymentTransaction::where('status', 'pending')->count(),
                'failed_count' => PaymentTransaction::where('status', 'failed')->count(),
            ];

            return view('admin.payment-transactions.index', compact('paymentTransactions', 'stats'));
        } catch (\Exception $e) {
            Log::error('Failed to load payment transactions index', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to load payment transactions: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified payment transaction
     * GET /admin/payment-transactions/{payment}
     */
    public function show(PaymentTransaction $payment)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            // Load relationships
            $payment->load(['user', 'transaction']);

            // Pretty print raw_response JSON
            $prettyResponse = null;
            if ($payment->raw_response) {
                $decoded = is_string($payment->raw_response) ? json_decode($payment->raw_response, true) : $payment->raw_response;
                $prettyResponse = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }

            return view('admin.payment-transactions.show', compact('payment', 'prettyResponse'));
        } catch (\Exception $e) {
            Log::error('Failed to load payment transaction details', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to load payment transaction details: ' . $e->getMessage());
        }
    }
}