@extends('admin.layouts.app')

@section('title', 'Payment Transaction Details')
@section('page-title', 'Payment Transaction Details')

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

<!-- Payment Header -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-receipt-cutoff"></i> Payment Information
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> This is a read-only view of payment gateway data. No modifications can be made here.
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <strong>Gateway Reference:</strong><br>
                        <code>{{ $payment->gateway_reference }}</code>
                    </div>
                    <div class="col-md-3">
                        <strong>Gateway:</strong>
                        @php
                            $gatewayClass = match($payment->gateway) {
                                'stripe' => 'primary',
                                'paypal' => 'info',
                                'flutterwave' => 'warning',
                                default => 'secondary'
                            };
                        @endphp
                        <span class="badge bg-{{ $gatewayClass }}">{{ ucfirst($payment->gateway) }}</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Status:</strong>
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
                    </div>
                    <div class="col-md-3">
                        <strong>Amount:</strong><br>
                        <span class="text-success" style="font-size: 18px; font-weight: 700;">${{ number_format($payment->amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Payment Details -->
        <div class="card mb-4">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-info-circle"></i> Payment Details
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Gateway Reference:</strong></p>
                        <code>{{ $payment->gateway_reference }}</code>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Amount:</strong></p>
                        <span style="font-size: 20px; color: #0C733C; font-weight: 700;">${{ number_format($payment->amount, 2) }}</span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Gateway:</strong></p>
                        <span class="badge bg-{{ $gatewayClass }}">{{ ucfirst($payment->gateway) }}</span>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Status:</strong></p>
                        <span class="badge bg-{{ $statusClass }}">{{ ucfirst($payment->status) }}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Created:</strong></p>
                        {{ $payment->created_at->format('M d, Y H:i:s') }}
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Updated:</strong></p>
                        {{ $payment->updated_at->format('M d, Y H:i:s') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Gateway Response -->
        <div class="card mb-4">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-code-square"></i> Raw Gateway Response
            </div>
            <div class="card-body">
                @if($prettyResponse)
                    <pre class="bg-dark text-light p-3 rounded" style="max-height: 600px; overflow-y: auto;"><code>{{ $prettyResponse }}</code></pre>
                @else
                    <p class="text-muted">No raw response data available</p>
                @endif
            </div>
        </div>

        <!-- Linked Transaction -->
        @if($payment->transaction)
        <div class="card mb-4">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-link-45deg"></i> Linked Ledger Transaction
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Transaction Number:</strong></p>
                        {{ $payment->transaction->transaction_number }}
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Status:</strong></p>
                        <span class="badge bg-{{ $payment->transaction->status === 'completed' ? 'success' : 'warning' }}">{{ ucfirst($payment->transaction->status) }}</span>
                    </div>
                </div>
                <a href="{{ route('admin.transactions.show', $payment->transaction) }}" class="btn btn-sm btn-primary mt-3">
                    <i class="bi bi-eye"></i> View Transaction Details
                </a>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <!-- User Information -->
        @if($payment->user)
        <div class="card mb-4">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-person"></i> User Information
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Name:</strong><br>{{ $payment->user->full_name }}</p>
                <p class="mb-1"><strong>Email:</strong><br>{{ $payment->user->email }}</p>
                <p class="mb-0"><strong>Role:</strong><br><span class="badge bg-info">{{ ucfirst($payment->user->role) }}</span></p>
                <a href="{{ route('admin.users.show', $payment->user) }}" class="btn btn-sm btn-primary mt-3 w-100">
                    <i class="bi bi-eye"></i> View User Profile
                </a>
            </div>
        </div>
        @endif

        <!-- Quick Stats -->
        <div class="card mb-4">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-bar-chart"></i> Quick Info
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <i class="bi bi-calendar-event text-primary"></i>
                    <strong>Processed:</strong><br>
                    {{ $payment->created_at->diffForHumans() }}
                </p>
                <p class="mb-2">
                    <i class="bi bi-arrow-repeat text-info"></i>
                    <strong>Last Updated:</strong><br>
                    {{ $payment->updated_at->diffForHumans() }}
                </p>
                <p class="mb-0">
                    <i class="bi bi-shield-check text-success"></i>
                    <strong>Read-Only:</strong><br>
                    Gateway data cannot be modified
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
