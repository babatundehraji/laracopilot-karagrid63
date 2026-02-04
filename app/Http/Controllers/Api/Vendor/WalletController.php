<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Api\BaseController;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WalletController extends BaseController
{
    /**
     * Get all transactions for authenticated vendor with filters
     * GET /api/vendor/wallet/transactions
     */
    public function transactions(Request $request)
    {
        try {
            $user = $request->user();

            $query = Transaction::where('user_id', $user->id);

            // Apply filters
            if ($request->has('type') && in_array($request->type, ['credit', 'debit'])) {
                $query->where('type', $request->type);
            }

            if ($request->has('category')) {
                $allowedCategories = ['order', 'refund', 'promotion', 'payout', 'earning', 'fee', 'adjustment'];
                if (in_array($request->category, $allowedCategories)) {
                    $query->where('category', $request->category);
                }
            }

            if ($request->has('status')) {
                $allowedStatuses = ['pending', 'completed', 'reversed'];
                if (in_array($request->status, $allowedStatuses)) {
                    $query->where('status', $request->status);
                }
            }

            // Date range filters
            if ($request->has('date_from')) {
                try {
                    $dateFrom = \Carbon\Carbon::parse($request->date_from)->startOfDay();
                    $query->where('created_at', '>=', $dateFrom);
                } catch (\Exception $e) {
                    // Invalid date format, skip filter
                }
            }

            if ($request->has('date_to')) {
                try {
                    $dateTo = \Carbon\Carbon::parse($request->date_to)->endOfDay();
                    $query->where('created_at', '<=', $dateTo);
                } catch (\Exception $e) {
                    // Invalid date format, skip filter
                }
            }

            // Order and paginate
            $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

            return $this->success([
                'transactions' => $transactions->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'type' => $transaction->type,
                        'category' => $transaction->category,
                        'amount' => $transaction->amount,
                        'description' => $transaction->description,
                        'status' => $transaction->status,
                        'created_at' => $transaction->created_at->toIso8601String()
                    ];
                }),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                    'last_page' => $transactions->lastPage()
                ]
            ], 'Transactions retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve vendor transactions', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve transactions', 500);
        }
    }

    /**
     * Get single transaction details for vendor
     * GET /api/vendor/wallet/transactions/{id}
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();

            $transaction = Transaction::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$transaction) {
                return $this->error('Transaction not found', 404);
            }

            return $this->success([
                'transaction' => [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'category' => $transaction->category,
                    'amount' => $transaction->amount,
                    'description' => $transaction->description,
                    'reference_type' => $transaction->reference_type,
                    'reference_id' => $transaction->reference_id,
                    'status' => $transaction->status,
                    'created_at' => $transaction->created_at->toIso8601String(),
                    'updated_at' => $transaction->updated_at->toIso8601String()
                ]
            ], 'Transaction details retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve vendor transaction details', [
                'user_id' => $request->user()->id,
                'transaction_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve transaction details', 500);
        }
    }

    /**
     * Get wallet summary for authenticated vendor
     * GET /api/vendor/wallet/summary
     */
    public function summary(Request $request)
    {
        try {
            $user = $request->user();

            // Total earnings (completed credits with category 'earning')
            $totalEarnings = Transaction::where('user_id', $user->id)
                ->where('type', 'credit')
                ->where('category', 'earning')
                ->where('status', 'completed')
                ->sum('amount');

            // Total payouts (completed debits with category 'payout')
            $totalPayouts = Transaction::where('user_id', $user->id)
                ->where('type', 'debit')
                ->where('category', 'payout')
                ->where('status', 'completed')
                ->sum('amount');

            // Pending earnings (pending credits with category 'earning')
            $pendingEarnings = Transaction::where('user_id', $user->id)
                ->where('type', 'credit')
                ->where('category', 'earning')
                ->where('status', 'pending')
                ->sum('amount');

            // Pending payouts (pending debits with category 'payout')
            $pendingPayouts = Transaction::where('user_id', $user->id)
                ->where('type', 'debit')
                ->where('category', 'payout')
                ->where('status', 'pending')
                ->sum('amount');

            // Available balance (all completed credits - all completed debits)
            $completedCredits = Transaction::where('user_id', $user->id)
                ->where('type', 'credit')
                ->where('status', 'completed')
                ->sum('amount');

            $completedDebits = Transaction::where('user_id', $user->id)
                ->where('type', 'debit')
                ->where('status', 'completed')
                ->sum('amount');

            $availableBalance = $completedCredits - $completedDebits;

            return $this->success([
                'wallet' => [
                    'total_earnings' => number_format($totalEarnings, 2, '.', ''),
                    'total_payouts' => number_format($totalPayouts, 2, '.', ''),
                    'pending_earnings' => number_format($pendingEarnings, 2, '.', ''),
                    'pending_payouts' => number_format($pendingPayouts, 2, '.', ''),
                    'available_balance' => number_format($availableBalance, 2, '.', '')
                ]
            ], 'Wallet summary retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve vendor wallet summary', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return $this->error('Failed to retrieve wallet summary', 500);
        }
    }
}