@extends('layouts.admin')

@section('title', 'Tambah User')

@section('content')

    <div class="row justify-content-center">
        <div class="col-md-11 col-lg-9">

            <div class="card shadow-sm border-0">

                <div class="card-header">
                    <h5 class="mb-0">Tambah User Baru</h5>
                </div>

                <div class="card-body">

                    {{-- GLOBAL ERROR --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.users.store') }}" method="POST" id="userCreateForm">
                        @csrf

                        <div class="row g-3">

                            {{-- Nama --}}
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap</label>

                                <input type="text" name="name" value="{{ old('name') }}"
                                    class="form-control @error('name') is-invalid @enderror" required>

                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Username --}}
                            <div class="col-md-6">
                                <label class="form-label">Username</label>

                                <input type="text" name="username" value="{{ old('username') }}"
                                    class="form-control @error('username') is-invalid @enderror" required>

                                @error('username')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- NPK --}}
                            <div class="col-md-6">
                                <label class="form-label">NPK</label>

                                <input type="text" name="npk" value="{{ old('npk') }}"
                                    class="form-control @error('npk') is-invalid @enderror" required>

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

                                <input type="email" name="email" value="{{ old('email') }}"
                                    class="form-control @error('email') is-invalid @enderror">

                                @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Password --}}
                            <div class="col-md-6">
                                <label class="form-label">Password</label>

                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror" required>

                                <small class="text-muted">
                                    Minimal 6 karakter
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

                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>

                            {{-- Role --}}
                            <div class="col-md-6">
                                <label class="form-label">Role</label>

                                <select name="role" class="form-select @error('role') is-invalid @enderror" required>

                                    <option value="">-- Pilih Role --</option>

                                    <option value="member" {{ old('role') == 'member' ? 'selected' : '' }}>
                                        Member
                                    </option>

                                    <option value="approver" {{ old('role') == 'approver' ? 'selected' : '' }}>
                                        Approver
                                    </option>

                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>
                                        Admin
                                    </option>

                                </select>

                                @error('role')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                        </div>

                        {{-- BUTTON --}}
                        <div class="d-flex gap-2 mt-4">

                            <button type="submit" class="btn btn-primary" id="submitBtn">

                                <i class="bi bi-save"></i>
                                Simpan
                            </button>

                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">

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

            const form = document.getElementById('userCreateForm');
            const btn = document.getElementById('submitBtn');

            if (form && btn) {
                form.addEventListener('submit', function () {
                    btn.disabled = true;
                    btn.innerHTML =
                        '<i class="bi bi-hourglass-split"></i> Menyimpan...';
                });
            }

        });
    </script>

@endsection