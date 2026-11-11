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
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <h5 class="mb-0">Add Partlist</h5>
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

                    <form action="{{ route('approver.add_partlist.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        Drawing No./Supplier
                                    </label>

                                    <input type="text" name="drawing_no_supplier" class="form-control" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Name</label>

                                    <input type="text" name="name" class="form-control" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Size/Type</label>

                                    <input type="text" name="size_type" class="form-control" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Qty</label>

                                    <input type="number" class="form-control" value="1" disabled>

                                    <input type="hidden" name="qty" value="1">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        Sub Assy Drawing / Unit
                                    </label>

                                    <input type="text" name="sub_assy_drawing_unit" class="form-control" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Location</label>

                                    <input type="text" name="location" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add
                            </button>

                            <a href="{{ route('approver.add_partlist.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection