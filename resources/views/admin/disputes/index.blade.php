@extends('admin.layouts.app')

@section('title', 'Disputes Management')
@section('page-title', 'Disputes Management')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-filter"></i> Filters
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.disputes.index') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
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

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" style="background: #F15A23; color: white;">
                <i class="bi bi-exclamation-triangle"></i> Disputes ({{ $disputes->total() }})
            </div>
            <div class="card-body p-0">
                @if($disputes->count() > 0)
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
                                    <th>Opened</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($disputes as $dispute)
                                <tr>
                                    <td><strong>{{ $dispute->dispute_number }}</strong></td>
                                    <td>{{ $dispute->order->order_number }}</td>
                                    <td>{{ $dispute->customer->full_name }}</td>
                                    <td>{{ $dispute->order->vendor->business_name }}</td>
                                    <td>{{ Str::limit($dispute->reason, 40) }}</td>
                                    <td>
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
                                    </td>
                                    <td style="font-size: 13px; color: #6c757d;">
                                        {{ $dispute->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.disputes.show', $dispute) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        {{ $disputes->links() }}
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size: 64px;"></i>
                        <p class="mt-3">No disputes found</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
