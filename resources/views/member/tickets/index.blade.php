@extends('layouts.member')

@section('title', 'Ticket List')

@section('sidebar')
    <a class="nav-link text-white" href="{{ route('member.dashboard') }}">
        <i class="bi bi-house-door me-2"></i>Dashboard
    </a>

    <a class="nav-link text-white" href="{{ route('member.tickets.create') }}">
        <i class="bi bi-plus-circle me-2"></i>Create Ticket
    </a>

    <a class="nav-link text-white active" href="{{ route('member.tickets.index') }}">
        <i class="bi bi-list-check me-2"></i>Ticket List
    </a>



    <a class="nav-link text-white" href="{{ route('member.stockouts.history') }}">
        <i class="bi bi-truck me-2"></i>Stockout History
    </a>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Ticket List</h2>
        <a href="{{ route('member.tickets.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> Create Ticket
        </a>
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
                            <th>No</th>
                            <th>Ticket ID</th>
                            <th>Created At</th>
                            <th>Requester</th>
                            <th>Line Requester</th>
                            <th>Approver</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tickets as $ticket)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>

                                            <td>
                                                <span class="fw-bold text-primary">
                                                    #TRM-{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }}
                                                </span>
                                            </td>

                                            <td>
                                                {{ $ticket->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB
                                            </td>

                                            <td>{{ $ticket->requester }}</td>

                                            <td>{{ $ticket->line_requester }}</td>

                                            <td>{{ $ticket->approver->name ?? '-' }}</td>

                                            <td>
                                                <span class="badge bg-{{ $ticket->status === 'pending'
                            ? 'warning'
                            : ($ticket->status === 'approved'
                                ? 'success'
                                : 'secondary') }}">
                                                    {{ ucfirst($ticket->status) }}
                                                </span>
                                            </td>
                                        </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Belum ada ticket.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection