@extends('layouts.member')

@section('title', 'Stockout History')

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

    <a class="nav-link text-white active" href="{{ route('member.stockouts.history') }}">
        <i class="bi bi-truck me-2"></i>Stockout History
    </a>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Stockout History</h2>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Ticket ID</th>
                            <th>Drawing No./Supplier</th>
                            <th>Partlist Name</th>
                            <th>Size/Type</th>
                            <th>Qty</th>
                            <th>Sub Assy Drawing/Unit</th>
                            <th>Location</th>
                            <th>Requester</th>
                            <th>Line Requester</th>
                            <th>Created At</th>
                            <th>Approver</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stockouts as $stockout)
                                        @php
                                            $ticket = $stockout->ticket;
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>

                                            <td>
                                                <span class="fw-bold text-primary">
                                                    #TRM-{{ str_pad(optional($ticket)->id ?? 0, 4, '0', STR_PAD_LEFT) }}
                                                </span>
                                            </td>

                                            <td>{{ $ticket->partlist->drawing_no_supplier ?? optional($ticket)->drawing_no_supplier }}</td>
                                            <td>{{ $ticket->partlist->name ?? optional($ticket)->partlist_name }}</td>
                                            <td>{{ $ticket->partlist->size_type ?? optional($ticket)->size_type }}</td>
                                            <td>{{ $ticket->qty ?? $stockout->qty }}</td>
                                            <td>{{ $ticket->partlist->sub_assy_drawing_unit ?? optional($ticket)->sub_assy_drawing_unit }}
                                            </td>

                                            <td>{{ $ticket->partlist->location ?? optional($ticket)->location }}</td>
                                            <td>{{ optional($ticket)->requester }}</td>
                                            <td>{{ optional($ticket)->line_requester }}</td>
                                            <td>
                                                @if (optional($ticket)->created_at)
                                                    {{ $ticket->created_at->format('Y-m-d H:i') }}
                                                @endif
                                            </td>
                                            <td>{{ optional(optional($ticket)->approver)->name }}</td>

                                            <td>
                                                @php
                                                    $status = optional($ticket)->status;
                                                @endphp
                                                <span class="badge bg-{{ $status === 'pending'
                            ? 'warning'
                            : ($status === 'approved'
                                ? 'success'
                                : 'danger') }}">
                                                    {{ $status ? ucfirst($status) : '-' }}
                                                </span>
                                            </td>
                                        </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-center text-muted">Belum ada stockout.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection