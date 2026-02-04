@extends('admin.layouts.app')

@section('title', 'Order Details - ' . $order->order_number)
@section('page-title', 'Order Details')

@section('content')
<!-- Order Header -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-cart-check"></i> Order Information
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="mb-2">{{ $order->order_number }}</h3>
                        <p class="mb-1">
                            <strong>Status:</strong>
                            @php
                                $statusClass = match($order->status) {
                                    'pending' => 'warning',
                                    'confirmed' => 'info',
                                    'in_progress' => 'primary',
                                    'completed' => 'success',
                                    'cancelled' => 'secondary',
                                    'disputed' => 'danger',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                        </p>
                        <p class="mb-1">
                            <strong>Payment:</strong>
                            @php
                                $paymentClass = match($order->payment_status) {
                                    'paid' => 'success',
                                    'pending' => 'warning',
                                    'failed' => 'danger',
                                    'refunded' => 'info',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $paymentClass }}">{{ ucfirst($order->payment_status) }}</span>
                        </p>
                        <p class="mb-1"><strong>Total Amount:</strong> <span class="text-success">${{ number_format($order->price, 2) }}</span></p>
                        <p class="mb-0 text-muted"><small>Created: {{ $order->created_at->format('F d, Y H:i:s') }}</small></p>
                    </div>
                    <div class="col-md-4 text-end">
                        @if($order->dispute)
                            <a href="{{ route('admin.disputes.show', $order->dispute) }}" class="btn btn-danger mb-2">
                                <i class="bi bi-exclamation-triangle"></i> View Dispute
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Service Snapshot -->
        <div class="card mb-4">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-grid"></i> Service Details
            </div>
            <div class="card-body">
                <h5>{{ $order->service->title }}</h5>
                <p class="text-muted mb-2">Category: {{ $order->service->category->name ?? 'N/A' }}</p>
                <p>{{ Str::limit($order->service->description ?? 'No description', 200) }}</p>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <strong>Base Price:</strong><br>
                        ${{ number_format($order->service->base_price, 2) }}
                    </div>
                    <div class="col-md-4">
                        <strong>Duration:</strong><br>
                        {{ $order->service->duration ?? 'N/A' }}
                    </div>
                    <div class="col-md-4">
                        <strong>Service Type:</strong><br>
                        {{ ucfirst($order->service->service_type ?? 'N/A') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule Information -->
        <div class="card mb-4">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-calendar-event"></i> Schedule Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Scheduled Date:</strong><br>
                        {{ $order->scheduled_date ? \Carbon\Carbon::parse($order->scheduled_date)->format('M d, Y') : 'Not scheduled' }}
                    </div>
                    <div class="col-md-4">
                        <strong>Scheduled Time:</strong><br>
                        {{ $order->scheduled_time ? \Carbon\Carbon::parse($order->scheduled_time)->format('h:i A') : 'Not scheduled' }}
                    </div>
                    <div class="col-md-4">
                        <strong>Location:</strong><br>
                        {{ $order->location ?? 'Not specified' }}
                    </div>
                </div>
                @if($order->notes)
                    <div class="mt-3">
                        <strong>Customer Notes:</strong><br>
                        <p class="mb-0">{{ $order->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Financials -->
        <div class="card mb-4">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-currency-dollar"></i> Financial Details
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Service Price:</strong><br>
                        <span class="text-success">${{ number_format($order->price, 2) }}</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Platform Fee:</strong><br>
                        <span class="text-danger">${{ number_format($order->platform_fee ?? 0, 2) }}</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Vendor Earnings:</strong><br>
                        <span class="text-primary">${{ number_format($order->vendor_earnings ?? ($order->price - ($order->platform_fee ?? 0)), 2) }}</span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Payment Method:</strong><br>
                        {{ ucfirst($order->payment_method ?? 'N/A') }}
                    </div>
                    <div class="col-md-6">
                        <strong>Payment Status:</strong><br>
                        <span class="badge bg-{{ $paymentClass }}">{{ ucfirst($order->payment_status) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Timeline -->
        @if($order->orderEdits->count() > 0)
        <div class="card mb-4">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-clock-history"></i> Status Timeline
            </div>
            <div class="card-body">
                <div class="timeline">
                    @foreach($order->orderEdits as $edit)
                        @if($edit->field_name === 'status')
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="bi bi-circle-fill" style="color: #F15A23; font-size: 8px;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <strong>{{ ucfirst($edit->new_value) }}</strong>
                                    <span class="text-muted">from {{ ucfirst($edit->old_value) }}</span>
                                    <br>
                                    <small class="text-muted">{{ $edit->created_at->format('M d, Y H:i:s') }}</small>
                                    @if($edit->reason)
                                        <br><small class="text-muted">Reason: {{ $edit->reason }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <!-- Update Status -->
        <div class="card mb-4">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-pencil-square"></i> Update Status
            </div>
            <div class="card-body">
                <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">New Status</label>
                        <select name="status" class="form-select" required>
                            <option value="">Select Status</option>
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="in_progress" {{ $order->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="refunded" {{ $order->status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                            <option value="disputed" {{ $order->status === 'disputed' ? 'selected' : '' }}>Disputed</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle"></i> Update Status
                    </button>
                </form>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="card mb-4">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-person"></i> Customer Information
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Name:</strong><br>{{ $order->user->full_name }}</p>
                <p class="mb-1"><strong>Email:</strong><br>{{ $order->user->email }}</p>
                <p class="mb-0"><strong>Phone:</strong><br>{{ $order->user->phone ?? 'Not provided' }}</p>
            </div>
        </div>

        <!-- Vendor Information -->
        <div class="card mb-4">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-shop"></i> Vendor Information
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Business:</strong><br>{{ $order->vendor->business_name }}</p>
                <p class="mb-1"><strong>Email:</strong><br>{{ $order->vendor->user->email }}</p>
                <p class="mb-0"><strong>Phone:</strong><br>{{ $order->vendor->user->phone ?? 'Not provided' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
