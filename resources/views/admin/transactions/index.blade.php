@extends('admin.layouts.app')

@section('title', 'Transactions Management')
@section('page-title', 'Transactions Management')

@section('content')
<!-- Navigation Tabs -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" href="{{ route('admin.transactions.index') }}" style="color: #F15A23; font-weight: 600;">
            <i class="bi bi-list-ul"></i> Ledger Transactions
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" href="{{ route('admin.payment-transactions.index') }}" style="color: #6c757d;">
            <i class="bi bi-receipt"></i> Payment Gateway Logs
        </a>
    </li>
</ul>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="mb-0" style="color: #0C733C;">${{ number_format($stats['total_volume'], 2) }}</h4>
                <p class="text-muted mb-0">Total Volume</p>
                <small class="text-muted">Completed transactions</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="mb-0" style="color: #ffc107;">{{ $stats['pending_count'] }}</h4>
                <p class="text-muted mb-0">Pending</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="mb-0" style="color: #28a745;">{{ $stats['completed_count'] }}</h4>
                <p class="text-muted mb-0">Completed</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="mb-0" style="color: #dc3545;">{{ $stats['failed_count'] }}</h4>
                <p class="text-muted mb-0">Failed</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-filter"></i> Filters
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.transactions.index') }}">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">User</label>
                            <select name="user_id" class="form-select">
                                <option value="">All Users</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Type</label>
                            <select name="transaction_type" class="form-select">
                                <option value="">All Types</option>
                                <option value="payment" {{ request('transaction_type') === 'payment' ? 'selected' : '' }}>Payment</option>
                                <option value="refund" {{ request('transaction_type') === 'refund' ? 'selected' : '' }}>Refund</option>
                                <option value="payout" {{ request('transaction_type') === 'payout' ? 'selected' : '' }}>Payout</option>
                                <option value="adjustment" {{ request('transaction_type') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                                <option value="fee" {{ request('transaction_type') === 'fee' ? 'selected' : '' }}>Fee</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="">All Categories</option>
                                <option value="order_payment" {{ request('category') === 'order_payment' ? 'selected' : '' }}>Order Payment</option>
                                <option value="vendor_payout" {{ request('category') === 'vendor_payout' ? 'selected' : '' }}>Vendor Payout</option>
                                <option value="platform_fee" {{ request('category') === 'platform_fee' ? 'selected' : '' }}>Platform Fee</option>
                                <option value="refund" {{ request('category') === 'refund' ? 'selected' : '' }}>Refund</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Transactions Table -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-list-ul"></i> Ledger Transactions ({{ $transactions->total() }})
            </div>
            <div class="card-body p-0">
                @if($transactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background: #f8f9fa;">
                                <tr>
                                    <th>Transaction #</th>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Order</th>
                                    <th>Date</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                <tr>
                                    <td><strong>{{ $transaction->transaction_number }}</strong></td>
                                    <td>{{ $transaction->user->full_name ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $typeClass = match($transaction->transaction_type) {
                                                'payment' => 'primary',
                                                'refund' => 'warning',
                                                'payout' => 'success',
                                                'adjustment' => 'info',
                                                'fee' => 'secondary',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $typeClass }}">{{ ucfirst($transaction->transaction_type) }}</span>
                                    </td>
                                    <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $transaction->category ?? 'N/A')) }}</span></td>
                                    <td><strong>${{ number_format($transaction->amount, 2) }}</strong></td>
                                    <td>
                                        @php
                                            $statusClass = match($transaction->status) {
                                                'completed' => 'success',
                                                'pending' => 'warning',
                                                'failed' => 'danger',
                                                'refunded' => 'info',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">{{ ucfirst($transaction->status) }}</span>
                                    </td>
                                    <td>
                                        @if($transaction->order)
                                            <a href="{{ route('admin.orders.show', $transaction->order) }}" class="text-primary">
                                                {{ $transaction->order->order_number }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td style="font-size: 13px; color: #6c757d;">
                                        {{ $transaction->created_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.transactions.show', $transaction) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        {{ $transactions->links() }}
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size: 64px;"></i>
                        <p class="mt-3">No transactions found</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
