@extends('layouts.member')

@section('title', 'Add Partlist')

@section('sidebar')
    <a class="nav-link text-white" href="{{ route('approver.dashboard') }}">
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
        <h2 class="mb-0">Add Partlist</h2>

        <a href="{{ route('approver.add_partlist.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> Add Partlist
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-striped align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>Drawing No./Supplier</th>
                            <th>Partlist</th>
                            <th>Qty</th>
                            <th>Created At</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($partlists as $partlist)
                            <tr>

                                <td>
                                    {{ $partlist->drawing_no_supplier }}
                                </td>

                                <td>
                                    {{ $partlist->name }}
                                </td>

                                <td>
                                    {{ $partlist->qty }}
                                </td>

                                <td>
                                    {{ $partlist->created_at?->format('Y-m-d H:i') }}
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    No data
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>

            </div>
        </div>
    </div>
@endsection