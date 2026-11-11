@extends('layouts.member')

@section('title', 'Reject Ticket')

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
    <div class="row justify-content-center">

        <div class="col-md-10 col-lg-8">

            <div class="card shadow-sm border-0">

                <div class="card-header">
                    <h5 class="mb-0">
                        Reject Ticket #{{ $ticket->id }}
                    </h5>
                </div>

                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row">

                        <!-- TITLE -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    Title
                                </label>

                                <input type="text" class="form-control" value="{{ $ticket->title }}" disabled>
                            </div>
                        </div>

                        <!-- APPROVER -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    Approver
                                </label>

                                <input type="text" class="form-control" value="{{ $ticket->approver->name ?? '-' }}"
                                    disabled>
                            </div>
                        </div>

                        <!-- REMARK MEMBER -->
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">
                                    Remark (dari member)
                                </label>

                                <textarea class="form-control" rows="4" disabled>{{ $ticket->remark ?? '-' }}</textarea>
                            </div>
                        </div>

                    </div>

                    <form action="{{ route('approver.tickets.reject.submit', $ticket) }}" method="POST">

                        @csrf

                        <div class="mb-3">

                            <label class="form-label">
                                Remark (rejection)
                            </label>

                            <textarea name="rejection_remark"
                                class="form-control @error('rejection_remark') is-invalid @enderror" rows="4"
                                required></textarea>

                            @error('rejection_remark')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="d-flex gap-2 mt-3">

                            <button type="submit" class="btn btn-danger">

                                <i class="bi bi-x-circle"></i>
                                Send Rejection

                            </button>

                            <a href="{{ route('approver.tickets.index') }}" class="btn btn-secondary">

                                <i class="bi bi-arrow-left"></i>
                                Back

                            </a>

                        </div>

                    </form>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Notifikasi:</strong>
                        setelah rejection, sistem akan menampilkan notifikasi di halaman approver.
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection