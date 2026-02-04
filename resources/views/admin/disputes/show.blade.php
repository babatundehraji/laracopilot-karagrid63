@extends('admin.layouts.app')

@section('title', 'Dispute Details - ' . $dispute->dispute_number)
@section('page-title', 'Dispute Details')

@section('content')
<!-- Dispute Header -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-exclamation-triangle"></i> Dispute Information
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="mb-2">{{ $dispute->dispute_number }}</h3>
                        <p class="mb-1">
                            <strong>Status:</strong>
                            @php
                                $statusClass = match($dispute->status) {
                                    'pending' => 'warning',
                                    'under_review' => 'info',
                                    'resolved' => 'success',
                                    'rejected' => 'danger',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $dispute->status)) }}</span>
                        </p>
                        <p class="mb-1"><strong>Reason:</strong> {{ $dispute->reason }}</p>
                        <p class="mb-0 text-muted"><small>Opened: {{ $dispute->created_at->format('F d, Y H:i:s') }}</small></p>
                        @if($dispute->resolved_at)
                            <p class="mb-0 text-muted"><small>Resolved: {{ $dispute->resolved_at->format('F d, Y H:i:s') }}</small></p>
                        @endif
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="{{ route('admin.orders.show', $dispute->order) }}" class="btn btn-primary">
                            <i class="bi bi-cart3"></i> View Order
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Dispute Details -->
        <div class="card mb-4">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-file-text"></i> Dispute Details
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Reason:</strong>
                    <p>{{ $dispute->reason }}</p>
                </div>
                <div class="mb-3">
                    <strong>Description:</strong>
                    <p>{{ $dispute->description ?? 'No additional description provided' }}</p>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Filed By:</strong><br>
                        {{ $dispute->customer->full_name }}<br>
                        <small class="text-muted">{{ $dispute->customer->email }}</small>
                    </div>
                    <div class="col-md-6">
                        <strong>Filed On:</strong><br>
                        {{ $dispute->created_at->format('M d, Y H:i:s') }}
                    </div>
                </div>
                @if($dispute->evidence)
                    <div class="mt-3">
                        <strong>Evidence:</strong>
                        <p>{{ $dispute->evidence }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Linked Order Summary -->
        <div class="card mb-4">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-cart-check"></i> Linked Order Summary
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Order Number:</strong> {{ $dispute->order->order_number }}</p>
                        <p class="mb-1"><strong>Service:</strong> {{ $dispute->order->service->title }}</p>
                        <p class="mb-1"><strong>Customer:</strong> {{ $dispute->order->user->full_name }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Vendor:</strong> {{ $dispute->order->vendor->business_name }}</p>
                        <p class="mb-1"><strong>Amount:</strong> <span class="text-success">${{ number_format($dispute->order->price, 2) }}</span></p>
                        <p class="mb-1">
                            <strong>Order Status:</strong>
                            @php
                                $orderStatusClass = match($dispute->order->status) {
                                    'pending' => 'warning',
                                    'confirmed' => 'info',
                                    'in_progress' => 'primary',
                                    'completed' => 'success',
                                    'cancelled' => 'secondary',
                                    'disputed' => 'danger',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $orderStatusClass }}">{{ ucfirst($dispute->order->status) }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @if($dispute->status === 'resolved')
        <!-- Resolution Details -->
        <div class="card mb-4">
            <div class="card-header" style="background: #28a745; color: white;">
                <i class="bi bi-check-circle"></i> Resolution Details
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Resolution:</strong> {{ ucfirst(str_replace('_', ' ', $dispute->resolution)) }}</p>
                <p class="mb-1"><strong>Resolved At:</strong> {{ $dispute->resolved_at->format('M d, Y H:i:s') }}</p>
                @if($dispute->resolution_notes)
                    <div class="mt-3">
                        <strong>Resolution Notes:</strong>
                        <p>{{ $dispute->resolution_notes }}</p>
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <!-- Resolution Form -->
        @if($dispute->status !== 'resolved')
        <div class="card mb-4">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-clipboard-check"></i> Resolve Dispute
            </div>
            <div class="card-body">
                <form action="{{ route('admin.disputes.resolve', $dispute) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Resolution Action *</label>
                        <select name="resolution" class="form-select" required>
                            <option value="">Select Resolution</option>
                            <option value="refund_customer">Refund Customer</option>
                            <option value="release_vendor">Release to Vendor</option>
                            <option value="partial_refund">Partial Refund</option>
                            <option value="no_action">No Action</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Resolution Notes *</label>
                        <textarea name="resolution_notes" class="form-control" rows="4" required placeholder="Explain your resolution decision..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100" onclick="return confirm('Are you sure you want to resolve this dispute? This action cannot be undone.')">
                        <i class="bi bi-check-circle"></i> Resolve Dispute
                    </button>
                </form>
            </div>
        </div>
        @endif

        <!-- Customer Info -->
        <div class="card mb-4">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-person"></i> Customer
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Name:</strong><br>{{ $dispute->customer->full_name }}</p>
                <p class="mb-1"><strong>Email:</strong><br>{{ $dispute->customer->email }}</p>
                <p class="mb-0"><strong>Phone:</strong><br>{{ $dispute->customer->phone ?? 'Not provided' }}</p>
            </div>
        </div>

        <!-- Vendor Info -->
        <div class="card mb-4">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-shop"></i> Vendor
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Business:</strong><br>{{ $dispute->order->vendor->business_name }}</p>
                <p class="mb-1"><strong>Email:</strong><br>{{ $dispute->order->vendor->user->email }}</p>
                <p class="mb-0"><strong>Phone:</strong><br>{{ $dispute->order->vendor->user->phone ?? 'Not provided' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
