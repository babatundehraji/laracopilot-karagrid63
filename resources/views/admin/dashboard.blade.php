@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Stats Cards -->
    <div class="col-md-3 mb-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: #e3f2fd; color: #1976d2;">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['total_users']) }}</div>
            <div class="stat-label">Total Users</div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: #f3e5f5; color: #7b1fa2;">
                <i class="bi bi-shop"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['total_vendors']) }}</div>
            <div class="stat-label">Total Vendors</div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: #fff3e0; color: #f57c00;">
                <i class="bi bi-cart3"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['total_orders']) }}</div>
            <div class="stat-label">Total Orders</div>
            @if($stats['pending_orders'] > 0)
                <small class="text-warning">{{ $stats['pending_orders'] }} pending</small>
            @endif
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="stat-card">
            <div class="stat-icon" style="background: #ffebee; color: #c62828;">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['total_disputes']) }}</div>
            <div class="stat-label">Total Disputes</div>
            @if($stats['open_disputes'] > 0)
                <small class="text-danger">{{ $stats['open_disputes'] }} open</small>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <!-- Revenue Card -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-currency-dollar"></i> Total Revenue
            </div>
            <div class="card-body text-center">
                <h2 class="mb-0" style="color: #0C733C; font-weight: 700;">
                    ${{ number_format($stats['total_revenue'], 2) }}
                </h2>
                <p class="text-muted mb-0">From paid orders</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lightning"></i> Quick Actions
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="#" class="btn btn-primary w-100">
                            <i class="bi bi-person-plus"></i> Add User
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="#" class="btn btn-secondary w-100">
                            <i class="bi bi-shop"></i> View Vendors
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="#" class="btn btn-outline-primary w-100">
                            <i class="bi bi-cart3"></i> View Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Orders -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-cart3"></i> Recent Orders</span>
                <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                @if($recentOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                <tr>
                                    <td><strong>{{ $order->order_number }}</strong></td>
                                    <td>{{ $order->user->full_name }}</td>
                                    <td>${{ number_format($order->price, 2) }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match($order->status) {
                                                'pending' => 'warning',
                                                'confirmed' => 'info',
                                                'completed' => 'success',
                                                'cancelled' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $badgeClass }}">{{ ucfirst($order->status) }}</span>
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

    <!-- Recent Disputes -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-exclamation-triangle"></i> Recent Disputes</span>
                <a href="#" class="btn btn-sm btn-outline-danger">View All</a>
            </div>
            <div class="card-body p-0">
                @if($recentDisputes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Dispute #</th>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentDisputes as $dispute)
                                <tr>
                                    <td><strong>{{ $dispute->dispute_number }}</strong></td>
                                    <td>{{ $dispute->order->order_number }}</td>
                                    <td>{{ $dispute->customer->full_name }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match($dispute->status) {
                                                'pending' => 'warning',
                                                'resolved' => 'success',
                                                'rejected' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $badgeClass }}">{{ ucfirst($dispute->status) }}</span>
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
