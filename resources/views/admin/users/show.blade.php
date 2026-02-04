@extends('admin.layouts.app')

@section('title', 'User Details - ' . $user->full_name)
@section('page-title', 'User Details')

@section('content')
<!-- User Header Card -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-person-circle"></i> User Information
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="mb-2">{{ $user->full_name }}</h3>
                        <p class="mb-1"><strong>Email:</strong> {{ $user->email }}</p>
                        <p class="mb-1"><strong>Phone:</strong> {{ $user->phone ?? 'Not provided' }}</p>
                        <p class="mb-1">
                            <strong>Role:</strong>
                            @php
                                $roleClass = match($user->role) {
                                    'vendor' => 'primary',
                                    'customer' => 'info',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $roleClass }}">{{ ucfirst($user->role) }}</span>
                        </p>
                        <p class="mb-1">
                            <strong>Status:</strong>
                            @php
                                $statusClass = match($user->status) {
                                    'active' => 'success',
                                    'suspended' => 'danger',
                                    'pending' => 'warning',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">{{ ucfirst($user->status) }}</span>
                        </p>
                        <p class="mb-0 text-muted"><small>Joined: {{ $user->created_at->format('F d, Y') }}</small></p>
                    </div>
                    <div class="col-md-4 text-end">
                        @if($user->status === 'active')
                            <form action="{{ route('admin.users.block', $user) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to suspend this user?')">
                                    <i class="bi bi-lock"></i> Suspend User
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.users.unblock', $user) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to activate this user?')">
                                    <i class="bi bi-unlock"></i> Activate User
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="mb-0" style="color: #F15A23;">{{ $stats['total_orders'] }}</h4>
                <p class="text-muted mb-0">Total Orders</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="mb-0" style="color: #28a745;">{{ $stats['completed_orders'] }}</h4>
                <p class="text-muted mb-0">Completed Orders</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="mb-0" style="color: #0C733C;">${{ number_format($stats['total_spent'], 2) }}</h4>
                <p class="text-muted mb-0">Total Spent</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="mb-0" style="color: #007bff;">{{ $stats['total_conversations'] }}</h4>
                <p class="text-muted mb-0">Conversations</p>
            </div>
        </div>
    </div>
</div>

<!-- Tabs -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs" id="userTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                            <i class="bi bi-person"></i> Profile
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab">
                            <i class="bi bi-cart"></i> Orders ({{ $stats['total_orders'] }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="transactions-tab" data-bs-toggle="tab" data-bs-target="#transactions" type="button" role="tab">
                            <i class="bi bi-credit-card"></i> Transactions ({{ $stats['total_transactions'] }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="messages-tab" data-bs-toggle="tab" data-bs-target="#messages" type="button" role="tab">
                            <i class="bi bi-chat"></i> Messages
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab">
                            <i class="bi bi-activity"></i> Activity Log
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="logins-tab" data-bs-toggle="tab" data-bs-target="#logins" type="button" role="tab">
                            <i class="bi bi-clock-history"></i> Login Logs
                        </button>
                    </li>
                </ul>

                <div class="tab-content mt-3" id="userTabsContent">
                    <!-- Profile Tab -->
                    <div class="tab-pane fade show active" id="profile" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Full Name:</strong> {{ $user->full_name }}</p>
                                <p><strong>Email:</strong> {{ $user->email }}</p>
                                <p><strong>Phone:</strong> {{ $user->phone ?? 'Not provided' }}</p>
                                <p><strong>Role:</strong> <span class="badge bg-{{ $roleClass }}">{{ ucfirst($user->role) }}</span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Status:</strong> <span class="badge bg-{{ $statusClass }}">{{ ucfirst($user->status) }}</span></p>
                                <p><strong>Email Verified:</strong> {{ $user->email_verified_at ? 'Yes' : 'No' }}</p>
                                <p><strong>Member Since:</strong> {{ $user->created_at->format('F d, Y') }}</p>
                                <p><strong>Last Updated:</strong> {{ $user->updated_at->format('F d, Y H:i:s') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Tab -->
                    <div class="tab-pane fade" id="orders" role="tabpanel">
                        @if($user->orders->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Vendor</th>
                                            <th>Service</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->orders as $order)
                                        <tr>
                                            <td><strong>{{ $order->order_number }}</strong></td>
                                            <td>{{ $order->vendor->business_name ?? 'N/A' }}</td>
                                            <td>{{ Str::limit($order->service->title ?? 'N/A', 40) }}</td>
                                            <td>${{ number_format($order->price, 2) }}</td>
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
                                                <span class="badge bg-{{ $badgeClass }}">{{ ucfirst($order->status) }}</span>
                                            </td>
                                            <td>{{ $order->created_at->format('M d, Y') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center py-4">No orders found</p>
                        @endif
                    </div>

                    <!-- Transactions Tab -->
                    <div class="tab-pane fade" id="transactions" role="tabpanel">
                        @if($user->transactions->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Transaction #</th>
                                            <th>Type</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->transactions as $transaction)
                                        <tr>
                                            <td><strong>{{ $transaction->transaction_number }}</strong></td>
                                            <td><span class="badge bg-info">{{ ucfirst($transaction->type) }}</span></td>
                                            <td>${{ number_format($transaction->amount, 2) }}</td>
                                            <td>
                                                @php
                                                    $statusClass = match($transaction->status) {
                                                        'completed' => 'success',
                                                        'pending' => 'warning',
                                                        'failed' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $statusClass }}">{{ ucfirst($transaction->status) }}</span>
                                            </td>
                                            <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center py-4">No transactions found</p>
                        @endif
                    </div>

                    <!-- Messages Tab -->
                    <div class="tab-pane fade" id="messages" role="tabpanel">
                        @if($user->conversations->count() > 0)
                            @foreach($user->conversations as $conversation)
                            <div class="card mb-3">
                                <div class="card-header" style="background: #f8f9fa;">
                                    <strong>Conversation #{{ $conversation->id }}</strong>
                                    <span class="badge bg-secondary float-end">{{ $conversation->messages->count() }} messages</span>
                                </div>
                                <div class="card-body">
                                    @if($conversation->messages->count() > 0)
                                        @foreach($conversation->messages->take(3) as $message)
                                        <div class="mb-2">
                                            <small class="text-muted">{{ $message->created_at->format('M d, Y H:i') }}</small>
                                            <p class="mb-1">{{ Str::limit($message->message, 100) }}</p>
                                        </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted mb-0">No messages</p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        @else
                            <p class="text-muted text-center py-4">No conversations found</p>
                        @endif
                    </div>

                    <!-- Activity Log Tab -->
                    <div class="tab-pane fade" id="activity" role="tabpanel">
                        @if($user->activityLogs->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Action</th>
                                            <th>Description</th>
                                            <th>IP Address</th>
                                            <th>Timestamp</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->activityLogs as $log)
                                        <tr>
                                            <td><span class="badge bg-primary">{{ $log->action }}</span></td>
                                            <td>{{ Str::limit($log->description ?? 'N/A', 60) }}</td>
                                            <td>{{ $log->ip_address ?? 'N/A' }}</td>
                                            <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center py-4">No activity logs found</p>
                        @endif
                    </div>

                    <!-- Login Logs Tab -->
                    <div class="tab-pane fade" id="logins" role="tabpanel">
                        @if($user->loginLogs->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>IP Address</th>
                                            <th>User Agent</th>
                                            <th>Status</th>
                                            <th>Login Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->loginLogs as $loginLog)
                                        <tr>
                                            <td>{{ $loginLog->ip_address }}</td>
                                            <td>{{ Str::limit($loginLog->user_agent ?? 'N/A', 50) }}</td>
                                            <td>
                                                @php
                                                    $statusClass = $loginLog->status === 'success' ? 'success' : 'danger';
                                                @endphp
                                                <span class="badge bg-{{ $statusClass }}">{{ ucfirst($loginLog->status) }}</span>
                                            </td>
                                            <td>{{ $loginLog->created_at->format('M d, Y H:i:s') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center py-4">No login logs found</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
