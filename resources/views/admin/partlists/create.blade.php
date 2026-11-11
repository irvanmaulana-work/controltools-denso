@extends('layouts.admin')

@section('title', 'Tambah Partlist')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Tambah Partlist Baru</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.partlists.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Drawing No./Supplier</label>
                            <input type="text" name="drawing_no_supplier"
                                class="form-control @error('drawing_no_supplier') is-invalid @enderror"
                                value="{{ old('drawing_no_supplier') }}" required>
                            @error('drawing_no_supplier')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Size / Type</label>
                            <input type="text" name="size_type"
                                class="form-control @error('size_type') is-invalid @enderror" value="{{ old('size_type') }}"
                                required>
                            @error('size_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Qty</label>
                            <input type="number" name="qty" class="form-control @error('qty') is-invalid @enderror"
                                value="{{ old('qty') }}" min="0" required>
                            @error('qty')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sub Assy Drawing / Unit</label>
                            <input type="text" name="sub_assy_drawing_unit"
                                class="form-control @error('sub_assy_drawing_unit') is-invalid @enderror"
                                value="{{ old('sub_assy_drawing_unit') }}" required>
                            @error('sub_assy_drawing_unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
                                value="{{ old('location') }}" required>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="{{ route('admin.partlists.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection