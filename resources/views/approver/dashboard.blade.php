@extends('layouts.member')

@section('title', 'Approver Dashboard')

@section('sidebar')
    <a class="nav-link text-white active" href="{{ route('approver.dashboard') }}">
        <i class="bi bi-house-door me-2"></i>Dashboard
    </a>

    <a class="nav-link text-white" href="{{ route('approver.tickets.index') }}">
        <i class="bi bi-list-check me-2"></i>Ticket List (Pending)
    </a>

    <a class="nav-link text-white" href="{{ route('approver.tickets.history') }}">
        <i class="bi bi-clock-history me-2"></i>History
    </a>

    <a class="nav-link text-white active" href="{{ route('approver.add_partlist.index') }}">
        <i class="bi bi-clipboard-check me-2"></i>Add Partlist
    </a>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Approver Dashboard</h2>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="row g-3">

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">

                        <div>
                            <div class="text-muted">
                                Pending Tickets
                            </div>

                            <div class="display-6">
                                {{ $pendingCount ?? 0 }}
                            </div>
                        </div>

                        <i class="bi bi-list-check fs-3"></i>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">

                        <div>
                            <div class="text-muted">
                                Approved Tickets
                            </div>

                            <div class="display-6">
                                {{ $approvedCount ?? 0 }}
                            </div>
                        </div>

                        <i class="bi bi-check2-circle fs-3 text-success"></i>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">

                        <div>
                            <div class="text-muted">
                                Rejected Tickets
                            </div>

                            <div class="display-6">
                                {{ $rejectedCount ?? 0 }}
                            </div>
                        </div>

                        <i class="bi bi-x-circle fs-3 text-danger"></i>

                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="card shadow-sm mt-4">

        <div class="card-header bg-transparent">
            <h5 class="mb-0">
                Pending Tickets (Preview)
            </h5>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-striped align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Requester</th>
                            <th>Created At</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse(($pendingTickets ?? collect()) as $t)

                            <tr>
                                <td>#{{ $t->id }}</td>

                                <td>
                                    {{ $t->title }}
                                </td>

                                <td>
                                    {{ $t->creator->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $t->created_at?->format('Y-m-d H:i') ?? '-' }}
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="text-center text-muted">

                                    Tidak ada pending tiket.

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>
        </div>
    </div>
@endsection