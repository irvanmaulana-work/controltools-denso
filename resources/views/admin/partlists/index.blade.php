@extends('layouts.admin')

@section('title', 'Manajemen Partlist')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Partlist</h2>
        <a href="{{ route('admin.partlists.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> Tambah Partlist
        </a>
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
                            <th>Drawing No./Supplier</th>
                            <th>Name</th>
                            <th>Size/Type</th>
                            <th style="width: 90px;">Qty</th>
                            <th>Sub Assy Drawing/Unit</th>
                            <th style="width: 170px;">Location</th>
                            <th style="width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($partlists as $partlist)
                            <tr>
                                <td>{{ $partlist->drawing_no_supplier }}</td>
                                <td>{{ $partlist->name }}</td>
                                <td>{{ $partlist->size_type }}</td>
                                <td>{{ $partlist->qty }}</td>
                                <td>{{ $partlist->sub_assy_drawing_unit }}</td>
                                <td>{{ $partlist->location }}</td>
                                <td>
                                    <a href="{{ route('admin.partlists.edit', $partlist) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form action="{{ route('admin.partlists.destroy', $partlist) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Delete this partlist?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Belum ada data partlist.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection