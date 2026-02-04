<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions
     * GET /admin/transactions
     */
    public function index(Request $request)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $query = Transaction::with(['user', 'order']);

            // Filter by user
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Filter by transaction type
            if ($request->filled('transaction_type')) {
                $query->where('transaction_type', $request->transaction_type);
            }

            // Filter by category
            if ($request->filled('category')) {
                $query->where('category', $request->category);
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

            $transactions = $query->orderBy('created_at', 'desc')->paginate(15);

            // Calculate stats
            $stats = [
                'total_volume' => Transaction::where('status', 'completed')->sum('amount'),
                'pending_count' => Transaction::where('status', 'pending')->count(),
                'completed_count' => Transaction::where('status', 'completed')->count(),
                'failed_count' => Transaction::where('status', 'failed')->count(),
            ];

            // Get users for filter dropdown
            $users = User::orderBy('full_name')->get();

            return view('admin.transactions.index', compact('transactions', 'users', 'stats'));
        } catch (\Exception $e) {
            Log::error('Failed to load transactions index', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to load transactions: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified transaction
     * GET /admin/transactions/{transaction}
     */
    public function show(Transaction $transaction)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            // Load relationships
            $transaction->load([
                'user',
                'order.vendor',
                'relatedTransactions'
            ]);

            return view('admin.transactions.show', compact('transaction'));
        } catch (\Exception $e) {
            Log::error('Failed to load transaction details', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to load transaction details: ' . $e->getMessage());
        }
    }

    /**
     * Add internal note to transaction
     * POST /admin/transactions/{transaction}/add-note
     */
    public function addNote(Request $request, Transaction $transaction)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $request->validate([
                'note' => 'required|string|min:5'
            ]);

            $admin = Auth::guard('web')->user();
            $timestamp = now()->format('Y-m-d H:i:s');
            $newNote = "[{$timestamp}] {$admin->full_name}: {$request->note}";

            // Append to existing notes
            $existingNotes = $transaction->transaction_notes ?? '';
            $updatedNotes = $existingNotes ? $existingNotes . "\n\n" . $newNote : $newNote;

            $transaction->update([
                'transaction_notes' => $updatedNotes
            ]);

            Log::info('Admin added note to transaction', [
                'admin_id' => $admin->id,
                'transaction_id' => $transaction->id,
                'transaction_number' => $transaction->transaction_number
            ]);

            return back()->with('success', 'Internal note added successfully');
        } catch (\Exception $e) {
            Log::error('Failed to add transaction note', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to add note: ' . $e->getMessage());
        }
    }

    /**
     * Update transaction status
     * POST /admin/transactions/{transaction}/update-status
     */
    public function updateStatus(Request $request, Transaction $transaction)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $request->validate([
                'status' => 'required|in:pending,completed,failed,refunded'
            ]);

            $oldStatus = $transaction->status;
            $newStatus = $request->status;

            if ($oldStatus === $newStatus) {
                return back()->with('error', 'Transaction already has this status');
            }

            $transaction->update(['status' => $newStatus]);

            // Add automatic note about status change
            $admin = Auth::guard('web')->user();
            $timestamp = now()->format('Y-m-d H:i:s');
            $autoNote = "[{$timestamp}] {$admin->full_name}: Status changed from {$oldStatus} to {$newStatus}";
            
            $existingNotes = $transaction->transaction_notes ?? '';
            $updatedNotes = $existingNotes ? $existingNotes . "\n\n" . $autoNote : $autoNote;
            
            $transaction->update(['transaction_notes' => $updatedNotes]);

            Log::info('Admin updated transaction status', [
                'admin_id' => $admin->id,
                'transaction_id' => $transaction->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);

            return back()->with('success', "Transaction status updated from {$oldStatus} to {$newStatus}");
        } catch (\Exception $e) {
            Log::error('Failed to update transaction status', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }
}