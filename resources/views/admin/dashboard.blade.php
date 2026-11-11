@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

<div class="row g-3">

    {{-- Total Partlists --}}
    <div class="col-md-3">
        <div class="card text-white bg-primary shadow-sm border-0">
            <div class="card-body">
                <h6 class="mb-2">Total Partlists</h6>
                <h2 class="mb-0">{{ $totalPartlists }}</h2>
            </div>
        </div>
    </div>

    {{-- Total Users --}}
    <div class="col-md-3">
        <div class="card text-white bg-success shadow-sm border-0">
            <div class="card-body">
                <h6 class="mb-2">Total Users</h6>
                <h2 class="mb-0">{{ $totalUsers }}</h2>
            </div>
        </div>
    </div>

    {{-- Pending Ticket --}}
    <div class="col-md-3">
        <div class="card text-dark bg-warning shadow-sm border-0">
            <div class="card-body">
                <h6 class="mb-2">Pending Ticket</h6>
                <h2 class="mb-0">{{ $pendingTickets }}</h2>
            </div>
        </div>
    </div>

    {{-- Approved Today --}}
    <div class="col-md-3">
        <div class="card text-white bg-info shadow-sm border-0">
            <div class="card-body">
                <h6 class="mb-2">Approved Today</h6>
                <h2 class="mb-0">{{ $approvedToday }}</h2>
            </div>
        </div>
    </div>

</div>

{{-- LOW STOCK + INFO --}}
<div class="row mt-4 g-3">

    {{-- Low Stock --}}
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">

            <div class="card-header bg-danger text-white">
                <h6 class="mb-0">Low Stock Alert</h6>
            </div>

            <div class="card-body p-0">

                @if($lowStocks->count())
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Part Name</th>
                                    <th>Location</th>
                                    <th class="text-end">Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lowStocks as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->location }}</td>
                                        <td class="text-end fw-bold text-danger">
                                            {{ $item->qty }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-3 text-muted">
                        Tidak ada stock kritis.
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- Welcome --}}
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">

            <div class="card-header">
                <h6 class="mb-0">Admin Panel</h6>
            </div>

            <div class="card-body">
                <p class="mb-2">
                    Gunakan sidebar untuk mengelola:
                </p>

                <ul class="mb-0">
                    <li>Master Partlist</li>
                    <li>Manajemen User</li>
                    <li>Monitoring Ticket</li>
                    <li>Kontrol Stock</li>
                </ul>
            </div>

        </div>
    </div>

</div>

{{-- Latest Tickets --}}
<div class="row mt-4">
    <div class="col-12">

        <div class="card shadow-sm border-0">

            <div class="card-header">
                <h6 class="mb-0">Latest Ticket Requests</h6>
            </div>

            <div class="card-body p-0">

                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">

                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Requester</th>
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
                                    <td>{{ $ticket->creator->name ?? '-' }}</td>
                                    <td>{{ $ticket->partlist_name }}</td>
                                    <td>{{ $ticket->qty }}</td>
                                    <td>{{ $ticket->approver->name ?? '-' }}</td>
                                    <td>
                                        @if($ticket->status == 'pending')
                                            <span class="badge bg-warning text-dark">
                                                Pending
                                            </span>
                                        @elseif($ticket->status == 'approved')
                                            <span class="badge bg-success">
                                                Approved
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                Rejected
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        Belum ada ticket.
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>
                </div>

            </div>

        </div>

    </div>
</div>

@endsection