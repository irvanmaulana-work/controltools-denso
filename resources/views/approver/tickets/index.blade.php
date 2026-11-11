@extends('layouts.member')

@section('title', 'Ticket List (Pending)')

@section('sidebar')

    <a class="nav-link text-white" href="{{ route('approver.dashboard') }}">
        <i class="bi bi-house-door me-2"></i>Dashboard
    </a>

    <a class="nav-link text-white active" href="{{ route('approver.tickets.index') }}">
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
        <h2 class="mb-0">Ticket List</h2>
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
                            <th>Remark</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($tickets as $ticket)
                            <tr>
                                <td>{{ $ticket->id }}</td>
                                <td>{{ $ticket->title }}</td>
                                <td>{{ $ticket->creator->name ?? '-' }}</td>
                                <td style="max-width: 320px;">
                                    {{ $ticket->remark ?? '-' }}
                                </td>
                                <td>
                                    {{ $ticket->created_at->format('Y-m-d H:i') }}
                                </td>

                                <td>
                                    <div class="d-flex gap-2">

                                        <!-- APPROVE BUTTON -->
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#approveModal{{ $ticket->id }}">

                                            <i class="bi bi-check2-circle"></i> Approve
                                        </button>

                                        <!-- APPROVE MODAL -->
                                        <div class="modal fade" id="approveModal{{ $ticket->id }}" tabindex="-1"
                                            aria-hidden="true">

                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">

                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            Confirm Approval
                                                        </h5>

                                                        <button type="button" class="btn-close" data-bs-dismiss="modal">
                                                        </button>
                                                    </div>

                                                    <div class="modal-body">
                                                        Anda yakin akan menyetujui tiket ini?
                                                    </div>

                                                    <div class="modal-footer">

                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                            Cancel
                                                        </button>

                                                        <form action="{{ route('approver.tickets.approve.submit', $ticket) }}"
                                                            method="POST">

                                                            @csrf

                                                            <button type="submit" class="btn btn-primary">
                                                                Ya, Approve
                                                            </button>
                                                        </form>

                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                        <!-- REJECT -->
                                        <a href="{{ route('approver.tickets.reject', $ticket) }}" class="btn btn-sm btn-danger">
                                            <i class="bi bi-x-circle"></i> Reject
                                        </a>

                                    </div>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Tidak ada ticket pending.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection