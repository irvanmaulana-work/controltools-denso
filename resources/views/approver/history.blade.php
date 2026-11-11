@extends('layouts.member')

@section('title', 'Approver History')

@section('sidebar')
    <a class="nav-link text-white" href="{{ route('approver.dashboard') }}">
        <i class="bi bi-house-door me-2"></i>Dashboard
    </a>

    <a class="nav-link text-white" href="{{ route('approver.tickets.index') }}">
        <i class="bi bi-list-check me-2"></i>Ticket List (Pending)
    </a>

    <a class="nav-link text-white active" href="{{ route('approver.tickets.history') }}">
        <i class="bi bi-clock-history me-2"></i>History
    </a>

    <a class="nav-link text-white active" href="{{ route('approver.add_partlist.index') }}">
        <i class="bi bi-clipboard-check me-2"></i>Add Partlist
    </a>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Approver History</h2>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>From</th>
                            <th>Remark (member)</th>
                            <th>Status</th>
                            <th>Approval Remark</th>
                            <th>Rejection Remark</th>
                            <th>Approved At</th>
                            <th>Rejected At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tickets as $ticket)
                            <tr>
                                <td>{{ $ticket->id }}</td>
                                <td>{{ $ticket->title }}</td>
                                <td>{{ $ticket->creator->name ?? '-' }}</td>
                                <td style="max-width: 320px;">{{ $ticket->remark ?? '-' }}</td>
                                <td>
                                    @php
                                        $status = $ticket->status;
                                        $badge = $status === 'approved' ? 'success' : ($status === 'rejected' ? 'secondary' : 'warning');
                                    @endphp
                                    <span class="badge bg-{{ $badge }}">{{ ucfirst($status) }}</span>
                                </td>
                                <td style="max-width: 320px;">{{ $ticket->approval_remark ?? '-' }}</td>
                                <td style="max-width: 320px;">{{ $ticket->rejection_remark ?? '-' }}</td>
                                <td>{{ $ticket->approved_at ? $ticket->approved_at->format('Y-m-d H:i') : '-' }}</td>
                                <td>{{ $ticket->rejected_at ? $ticket->rejected_at->format('Y-m-d H:i') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">Tidak ada history untuk approver ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection