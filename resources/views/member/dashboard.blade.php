@extends('layouts.member')

@section('title', 'Dashboard Member')

@section('sidebar')
    <a class="nav-link text-white" href="{{ route('member.dashboard') }}">
        <i class="bi bi-house-door me-2"></i>Dashboard
    </a>

    <a class="nav-link text-white" href="{{ route('member.tickets.create') }}">
        <i class="bi bi-plus-circle me-2"></i>Create Ticket
    </a>

    <a class="nav-link text-white" href="{{ route('member.tickets.index') }}">
        <i class="bi bi-list-check me-2"></i>Ticket List
    </a>



    <a class="nav-link text-white" href="{{ route('member.stockouts.history') }}">
        <i class="bi bi-truck me-2"></i>Stockout History
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5>My Tickets</h5>
                    <h2>{{ $myTickets }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5>Approved</h5>
                    <h2>{{ $approved }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5>Pending Approval</h5>
                    <h2>{{ $pending }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Welcome to Member Dashboard</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">Gunakan sidebar untuk membuat dan melacak ticket serta histori stockout.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">

            <h6 class="mb-0">
                Latest Tickets
            </h6>

            <a href="{{ route('member.tickets.index') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-list-ul"></i>
                Lihat Semua Ticket
            </a>

        </div>

        <div class="card-body p-0">
            <table class="table table-hover mb-0">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Part</th>
                        <th>Qty</th>
                        <th>Approver</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($latestTickets as $ticket)
                        <tr>
                            <td>{{ $ticket->id }}</td>
                            <td>{{ $ticket->partlist_name }}</td>
                            <td>{{ $ticket->qty }}</td>
                            <td>{{ $ticket->approver->name ?? '-' }}</td>

                            <td>
                                @if($ticket->status == 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($ticket->status == 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                Belum ada ticket.
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>
        </div>
    </div>
@endsection