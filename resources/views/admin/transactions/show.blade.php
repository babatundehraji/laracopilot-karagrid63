@extends('admin.layouts.app')

@section('title', 'Transaction Details - ' . $transaction->transaction_number)
@section('page-title', 'Transaction Details')

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

<!-- Transaction Header -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-receipt-cutoff"></i> Transaction Information
            </div>
            <div class="card-body">
                <h3 class="mb-2">{{ $transaction->transaction_number }}</h3>
                <div class="row">
                    <div class="col-md-3">
                        <strong>Type:</strong>
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
                    </div>
                    <div class="col-md-3">
                        <strong>Status:</strong>
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
                    </div>
                    <div class="col-md-3">
                        <strong>Amount:</strong>
                        <span class="text-success" style="font-size: 18px; font-weight: 700;">${{ number_format($transaction->amount, 2) }}</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Date:</strong><br>
                        {{ $transaction->created_at->format('M d, Y H:i:s') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Financial Details -->
        <div class="card mb-4">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-currency-dollar"></i> Financial Details
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Amount:</strong><br>
                        <span style="font-size: 24px; color: #0C733C; font-weight: 700;">${{ number_format($transaction->amount, 2) }}</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Category:</strong><br>
                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $transaction->category ?? 'N/A')) }}</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Method:</strong><br>
                        {{ ucfirst($transaction->method ?? 'N/A') }}
                    </div>
                </div>
                @if($transaction->description)
                    <div class="mt-3">
                        <strong>Description:</strong>
                        <p>{{ $transaction->description }}</p>
                    </div>
                @endif
                @if($transaction->metadata)
                    <div class="mt-3">
                        <strong>Metadata:</strong>
                        <pre class="bg-light p-3 rounded">{{ json_encode(json_decode($transaction->metadata), JSON_PRETTY_PRINT) }}</pre>
                    </div>
                @endif
            </div>
        </div>

        <!-- Order Link -->
        @if($transaction->order)
        <div class="card mb-4">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-cart-check"></i> Linked Order
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Order Number:</strong> {{ $transaction->order->order_number }}</p>
                        <p class="mb-1"><strong>Status:</strong> <span class="badge bg-info">{{ ucfirst($transaction->order->status) }}</span></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Order Amount:</strong> ${{ number_format($transaction->order->price, 2) }}</p>
                        <a href="{{ route('admin.orders.show', $transaction->order) }}" class="btn btn-sm btn-primary mt-2">
                            <i class="bi bi-eye"></i> View Order Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Related Transactions -->
        @if($transaction->relatedTransactions && $transaction->relatedTransactions->count() > 0)
        <div class="card mb-4">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-link-45deg"></i> Related Transactions
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th>Transaction #</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transaction->relatedTransactions as $related)
                            <tr>
                                <td><a href="{{ route('admin.transactions.show', $related) }}">{{ $related->transaction_number }}</a></td>
                                <td><span class="badge bg-secondary">{{ ucfirst($related->transaction_type) }}</span></td>
                                <td>${{ number_format($related->amount, 2) }}</td>
                                <td><span class="badge bg-{{ $related->status === 'completed' ? 'success' : 'warning' }}">{{ ucfirst($related->status) }}</span></td>
                                <td>{{ $related->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Internal Notes -->
        <div class="card mb-4">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-sticky"></i> Internal Notes
            </div>
            <div class="card-body">
                @if($transaction->transaction_notes)
                    <div class="bg-light p-3 rounded mb-3">
                        <pre style="white-space: pre-wrap; margin: 0;">{{ $transaction->transaction_notes }}</pre>
                    </div>
                @else
                    <p class="text-muted">No internal notes yet</p>
                @endif

                <form action="{{ route('admin.transactions.add-note', $transaction) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Add Internal Note</label>
                        <textarea name="note" class="form-control" rows="3" placeholder="Add administrative note..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add Note
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Update Status -->
        <div class="card mb-4">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-pencil-square"></i> Update Status
            </div>
            <div class="card-body">
                <form action="{{ route('admin.transactions.update-status', $transaction) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">New Status</label>
                        <select name="status" class="form-select" required>
                            <option value="">Select Status</option>
                            <option value="pending" {{ $transaction->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ $transaction->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="failed" {{ $transaction->status === 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="refunded" {{ $transaction->status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" onclick="return confirm('Are you sure you want to update this transaction status?')">
                        <i class="bi bi-check-circle"></i> Update Status
                    </button>
                </form>
            </div>
        </div>

        <!-- User Information -->
        @if($transaction->user)
        <div class="card mb-4">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-person"></i> User Information
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Name:</strong><br>{{ $transaction->user->full_name }}</p>
                <p class="mb-1"><strong>Email:</strong><br>{{ $transaction->user->email }}</p>
                <p class="mb-0"><strong>Role:</strong><br><span class="badge bg-info">{{ ucfirst($transaction->user->role) }}</span></p>
                <a href="{{ route('admin.users.show', $transaction->user) }}" class="btn btn-sm btn-primary mt-3 w-100">
                    <i class="bi bi-eye"></i> View User Profile
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
