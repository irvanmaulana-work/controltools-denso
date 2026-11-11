@extends('layouts.member')

@section('title', 'Approve Ticket')

@section('sidebar')
    <a class="nav-link text-white" href="{{ route('approver.dashboard') }}">
        <i class="bi bi-house-door me-2"></i>Dashboard
    </a>

    <a class="nav-link text-white" href="{{ route('approver.tickets.index') }}">
        <i class="bi bi-list-check me-2"></i>Ticket List (Pending)
    </a>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <h5 class="mb-0">Approve Ticket #{{ $ticket->id }}</h5>
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

                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" value="{{ $ticket->title }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Approver</label>
                        <input type="text" class="form-control" value="{{ $ticket->approver->name ?? '-' }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Remark (dari member)</label>
                        <textarea class="form-control" rows="4" disabled>{{ $ticket->remark ?? '-' }}</textarea>
                    </div>

                    <form action="{{ route('approver.tickets.approve.submit', $ticket) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Remark (approval)</label>
                            <textarea name="approval_remark"
                                class="form-control @error('approval_remark') is-invalid @enderror" rows="4"
                                required></textarea>
                            @error('approval_remark')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-send"></i> Send Approval
                            </button>

                            <a href="{{ route('approver.tickets.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back
                            </a>
                        </div>
                    </form>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Notifikasi:</strong> setelah approval, sistem akan menampilkan notifikasi di halaman
                        approver.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection