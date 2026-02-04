@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- Statistics Cards -->
<div class="row">
    <!-- Users Stats -->
    <div class="col-md-3 mb-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: #e3f2fd; color: #1976d2;">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['total_users']) }}</div>
            <div class="stat-label">Total Users</div>
        </div>
    </div>

    <!-- Vendors Stats -->
    <div class="col-md-3 mb-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: #f3e5f5; color: #7b1fa2;">
                <i class="bi bi-shop"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['total_vendors']) }}</div>
            <div class="stat-label">Total Vendors</div>
            <div class="d-flex justify-content-between mt-2" style="font-size: 12px;">
                <span class="text-success"><i class="bi bi-check-circle"></i> {{ $stats['approved_vendors'] }} approved</span>
                <span class="text-warning"><i class="bi bi-clock"></i> {{ $stats['pending_vendors'] }} pending</span>
            </div>
        </div>
    </div>

    <!-- Services Stats -->
    <div class="col-md-3 mb-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: #e8f5e9; color: #388e3c;">
                <i class="bi bi-grid"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['approved_services']) }}</div>
            <div class="stat-label">Active Services</div>
            <small class="text-muted">{{ $stats['total_services'] }} total</small>
        </div>
    </div>

    <!-- Orders Stats -->
    <div class="col-md-3 mb-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: #fff3e0; color: #f57c00;">
                <i class="bi bi-cart3"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['total_orders']) }}</div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>
</div>

<!-- Orders Breakdown -->
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card" style="border-left: 4px solid #ffc107;">
            <div class="card-body text-center">
                <h3 class="mb-1" style="color: #ffc107;">{{ number_format($stats['pending_orders']) }}</h3>
                <p class="mb-0 text-muted">Pending Orders</p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card" style="border-left: 4px solid #F15A23;">
            <div class="card-body text-center">
                <h3 class="mb-1" style="color: #F15A23;">{{ number_format($stats['active_orders']) }}</h3>
                <p class="mb-0 text-muted">Active Orders</p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card" style="border-left: 4px solid #28a745;">
            <div class="card-body text-center">
                <h3 class="mb-1" style="color: #28a745;">{{ number_format($stats['completed_orders']) }}</h3>
                <p class="mb-0 text-muted">Completed Orders</p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card" style="border-left: 4px solid #dc3545;">
            <div class="card-body text-center">
                <h3 class="mb-1" style="color: #dc3545;">{{ number_format($stats['disputed_orders']) }}</h3>
                <p class="mb-0 text-muted">Disputed Orders</p>
            </div>
        </div>
    </div>
</div>

<!-- Financial & Disputes Stats -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-currency-dollar"></i> Transaction Volume
            </div>
            <div class="card-body text-center">
                <h2 class="mb-0" style="color: #0C733C; font-weight: 700;">
                    ${{ number_format($stats['transaction_volume'], 2) }}
                </h2>
                <p class="text-muted mb-0">{{ number_format($stats['total_transactions']) }} completed transactions</p>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-exclamation-triangle"></i> Disputes Overview
            </div>
            <div class="card-body text-center">
                <h2 class="mb-0" style="color: #dc3545; font-weight: 700;">
                    {{ number_format($stats['total_disputes']) }}
                </h2>
                <p class="text-muted mb-0">
                    {{ $stats['open_disputes'] }} open disputes require attention
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Latest Users -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-people"></i> Latest Users
            </div>
            <div class="card-body p-0">
                @if($latestUsers->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background: #f8f9fa;">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($latestUsers as $user)
                                <tr>
                                    <td><strong>{{ $user->full_name }}</strong></td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @php
                                            $roleClass = match($user->role) {
                                                'vendor' => 'primary',
                                                'customer' => 'info',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $roleClass }}">{{ ucfirst($user->role) }}</span>
                                    </td>
                                    <td style="font-size: 13px; color: #6c757d;">
                                        {{ $user->created_at->format('M d, Y') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox" style="font-size: 48px;"></i>
                        <p class="mt-2">No users yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Latest Vendors -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-shop"></i> Latest Vendors
            </div>
            <div class="card-body p-0">
                @if($latestVendors->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background: #f8f9fa;">
                                <tr>
                                    <th>Business Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($latestVendors as $vendor)
                                <tr>
                                    <td><strong>{{ $vendor->business_name }}</strong></td>
                                    <td>{{ $vendor->user->email }}</td>
                                    <td>
                                        @php
                                            $statusClass = match($vendor->status) {
                                                'approved' => 'success',
                                                'pending' => 'warning',
                                                'rejected' => 'danger',
                                                'suspended' => 'secondary',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">{{ ucfirst($vendor->status) }}</span>
                                    </td>
                                    <td style="font-size: 13px; color: #6c757d;">
                                        {{ $vendor->created_at->format('M d, Y') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox" style="font-size: 48px;"></i>
                        <p class="mt-2">No vendors yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Latest Orders -->
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-cart3"></i> Latest Orders
            </div>
            <div class="card-body p-0">
                @if($latestOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background: #f8f9fa;">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Vendor</th>
                                    <th>Service</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($latestOrders as $order)
                                <tr>
                                    <td><strong>{{ $order->order_number }}</strong></td>
                                    <td>{{ $order->user->full_name }}</td>
                                    <td>{{ $order->vendor->business_name }}</td>
                                    <td>{{ Str::limit($order->service->title ?? 'N/A', 30) }}</td>
                                    <td><strong>${{ number_format($order->price, 2) }}</strong></td>
                                    <td>
                                        @php
                                            $badgeClass = match($order->status) {
                                                'pending' => 'warning',
                                                'confirmed' => 'info',
                                                'in_progress' => 'primary',
                                                'completed' => 'success',
                                                'cancelled' => 'secondary',
                                                'disputed' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                                    </td>
                                    <td style="font-size: 13px; color: #6c757d;">
                                        {{ $order->created_at->format('M d, Y') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox" style="font-size: 48px;"></i>
                        <p class="mt-2">No orders yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Latest Disputes -->
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-exclamation-triangle"></i> Latest Disputes
            </div>
            <div class="card-body p-0">
                @if($latestDisputes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background: #f8f9fa;">
                                <tr>
                                    <th>Dispute #</th>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Vendor</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($latestDisputes as $dispute)
                                <tr>
                                    <td><strong>{{ $dispute->dispute_number }}</strong></td>
                                    <td>{{ $dispute->order->order_number }}</td>
                                    <td>{{ $dispute->customer->full_name }}</td>
                                    <td>{{ $dispute->order->vendor->business_name }}</td>
                                    <td>{{ Str::limit($dispute->reason, 40) }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match($dispute->status) {
                                                'pending' => 'warning',
                                                'under_review' => 'info',
                                                'resolved' => 'success',
                                                'rejected' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $dispute->status)) }}</span>
                                    </td>
                                    <td style="font-size: 13px; color: #6c757d;">
                                        {{ $dispute->created_at->format('M d, Y') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox" style="font-size: 48px;"></i>
                        <p class="mt-2">No disputes</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
