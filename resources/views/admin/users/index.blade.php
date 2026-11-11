@extends('layouts.admin')

@section('title', 'Manajemen Akun')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="mb-0">Users Management</h2>

        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add User
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th width="70">ID</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>NPK</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th width="160">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>

                                <td class="fw-semibold">
                                    {{ $user->name }}
                                </td>

                                <td>{{ $user->username }}</td>

                                <td>{{ $user->npk ?? '-' }}</td>

                                <td>{{ $user->email ?? '-' }}</td>

                                <td>
                                    @if($user->role == 'admin')
                                        <span class="badge bg-danger">Admin</span>

                                    @elseif($user->role == 'approver')
                                        <span class="badge bg-warning text-dark">
                                            Approver
                                        </span>

                                    @else
                                        <span class="badge bg-secondary">
                                            Member
                                        </span>
                                    @endif
                                </td>

                                <td>

                                    <div class="d-flex gap-1">

                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        @if(auth()->id() != $user->id)

                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                                onsubmit="return confirm('Delete this user?')">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>

                                            </form>

                                        @else

                                            <button class="btn btn-sm btn-secondary" disabled title="Cannot delete yourself">
                                                <i class="bi bi-lock"></i>
                                            </button>

                                        @endif

                                    </div>

                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

            {{-- Pagination --}}
            @if(method_exists($users, 'links'))
                <div class="mt-3">
                    {{ $users->links() }}
                </div>
            @endif

        </div>
    </div>

@endsection