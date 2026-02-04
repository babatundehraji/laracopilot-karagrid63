@extends('admin.layouts.app')

@section('title', 'Payment Gateway Logs')
@section('page-title', 'Payment Gateway Logs')

@section('content')
<!-- Navigation Tabs -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link" href="{{ route('admin.transactions.index') }}" style="color: #6c757d;">
            <i class="bi bi-list-ul"></i> Ledger Transactions
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link active" href="{{ route('admin.payment-transactions.index') }}" style="color: #F15A23; font-weight: 600;">
            <i class="bi bi-receipt"></i> Payment Gateway Logs
        </a>
    </li>
</ul>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="mb-0" style="color: #0C733C;">${{ number_format($stats['total_processed'], 2) }}</h4>
                <p class="text-muted mb-0">Total Processed</p>
                <small class="text-muted">Successful payments</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="mb-0" style="color: #28a745;">{{ $stats['success_count'] }}</h4>
                <p class="text-muted mb-0">Successful</p>
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
                <form method="GET" action="{{ route('admin.payment-transactions.index') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Gateway</label>
                            <select name="gateway" class="form-select">
                                <option value="">All Gateways</option>
                                <option value="stripe" {{ request('gateway') === 'stripe' ? 'selected' : '' }}>Stripe</option>
                                <option value="paypal" {{ request('gateway') === 'paypal' ? 'selected' : '' }}>PayPal</option>
                                <option value="flutterwave" {{ request('gateway') === 'flutterwave' ? 'selected' : '' }}>Flutterwave</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
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
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Payment Transactions Table -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-receipt"></i> Payment Gateway Logs ({{ $paymentTransactions->total() }})
            </div>
            <div class="card-body p-0">
                @if($paymentTransactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background: #f8f9fa;">
                                <tr>
                                    <th>Gateway Ref</th>
                                    <th>User</th>
                                    <th>Gateway</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Transaction</th>
                                    <th>Date</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($paymentTransactions as $payment)
                                <tr>
                                    <td><strong>{{ $payment->gateway_reference }}</strong></td>
                                    <td>{{ $payment->user->full_name ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $gatewayClass = match($payment->gateway) {
                                                'stripe' => 'primary',
                                                'paypal' => 'info',
                                                'flutterwave' => 'warning',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $gatewayClass }}">{{ ucfirst($payment->gateway) }}</span>
                                    </td>
                                    <td><strong>${{ number_format($payment->amount, 2) }}</strong></td>
                                    <td>
                                        @php
                                            $statusClass = match($payment->status) {
                                                'success' => 'success',
                                                'pending' => 'warning',
                                                'failed' => 'danger',
                                                'refunded' => 'info',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">{{ ucfirst($payment->status) }}</span>
                                    </td>
                                    <td>
                                        @if($payment->transaction)
                                            <a href="{{ route('admin.transactions.show', $payment->transaction) }}" class="text-primary">
                                                {{ $payment->transaction->transaction_number }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td style="font-size: 13px; color: #6c757d;">
                                        {{ $payment->created_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.payment-transactions.show', $payment) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        {{ $paymentTransactions->links() }}
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size: 64px;"></i>
                        <p class="mt-3">No payment transactions found</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
