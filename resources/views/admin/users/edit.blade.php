@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">

        <div class="card shadow-sm border-0 rounded-4">

            <div class="card-header bg-white border-0">
                <h5 class="mb-0">Edit User: {{ $user->name }}</h5>
            </div>

            <div class="card-body">

                {{-- ERROR LIST --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.users.update', $user) }}"
                    method="POST"
                    id="userEditForm">

                    @csrf
                    @method('PUT')

                    <div class="row g-3">

                        {{-- Nama --}}
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap</label>

                            <input type="text"
                                name="name"
                                value="{{ old('name', $user->name) }}"
                                class="form-control @error('name') is-invalid @enderror"
                                required>

                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Username --}}
                        <div class="col-md-6">
                            <label class="form-label">Username</label>

                            <input type="text"
                                name="username"
                                value="{{ old('username', $user->username) }}"
                                class="form-control @error('username') is-invalid @enderror"
                                required>

                            @error('username')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- NPK --}}
                        <div class="col-md-6">
                            <label class="form-label">NPK</label>

                            <input type="text"
                                name="npk"
                                value="{{ old('npk', $user->npk) }}"
                                class="form-control @error('npk') is-invalid @enderror"
                                required>

                            @error('npk')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="col-md-6">
                            <label class="form-label">
                                Email
                                <small class="text-muted">(Opsional)</small>
                            </label>

                            <input type="email"
                                name="email"
                                value="{{ old('email', $user->email) }}"
                                class="form-control @error('email') is-invalid @enderror">

                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Role --}}
                        <div class="col-md-6">
                            <label class="form-label">Role</label>

                            <select name="role"
                                class="form-select @error('role') is-invalid @enderror"
                                required>

                                <option value="admin"
                                    {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>
                                    Admin
                                </option>

                                <option value="approver"
                                    {{ old('role', $user->role) == 'approver' ? 'selected' : '' }}>
                                    Approver
                                </option>

                                <option value="member"
                                    {{ old('role', $user->role) == 'member' ? 'selected' : '' }}>
                                    Member
                                </option>

                            </select>

                            @error('role')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Password Baru --}}
                        <div class="col-md-6">
                            <label class="form-label">Password Baru</label>

                            <input type="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Kosongkan jika tidak diubah">

                            <small class="text-muted">
                                Minimal 8 karakter
                            </small>

                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Confirm Password --}}
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password</label>

                            <input type="password"
                                name="password_confirmation"
                                class="form-control"
                                placeholder="Ulangi password baru">
                        </div>

                    </div>

                    {{-- BUTTON --}}
                    <div class="d-flex gap-2 mt-4">

                        <button type="submit"
                            class="btn btn-primary"
                            id="submitBtn">

                            <i class="bi bi-save"></i>
                            Update
                        </button>

                        <a href="{{ route('admin.users.index') }}"
                            class="btn btn-secondary">

                            Batal
                        </a>

                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('userEditForm');
    const btn = document.getElementById('submitBtn');

    if (form && btn) {
        form.addEventListener('submit', function () {
            btn.disabled = true;
            btn.innerHTML =
                '<i class="bi bi-hourglass-split"></i> Updating...';
        });
    }

});
</script>

@endsection