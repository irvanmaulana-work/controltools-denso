<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - @yield('title', 'Control Tools')</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f5f6fa;
        }

        .sidebar {
            width: 280px;
            min-height: 100vh;
            background: #212529;
        }

        .sidebar .nav-link {
            border-radius: 10px;
            transition: 0.2s;
            padding: 10px 14px;
        }

        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.18);
            font-weight: 600;
        }

        .main-content {
            flex-grow: 1;
        }

        .profile-avatar {
            width: 40px;
            height: 40px;
            font-weight: bold;
        }

        .content-wrapper {
            padding: 24px;
        }
    </style>
</head>

<body>

    <div class="d-flex">

        {{-- SIDEBAR --}}
        <aside class="sidebar d-flex flex-column p-3">

            <h4 class="text-white mb-4">
                Control Tools
            </h4>

            <nav class="nav flex-column">

                {{-- Dashboard --}}
                <a class="nav-link text-white {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                    href="{{ route('admin.dashboard') }}">

                    <i class="bi bi-house-door me-2"></i>
                    Dashboard
                </a>

                {{-- User Management --}}
                <a class="nav-link text-white {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                    href="{{ route('admin.users.index') }}">

                    <i class="bi bi-people me-2"></i>
                    Manajemen Akun
                </a>

                {{-- Partlist --}}
                <a class="nav-link text-white {{ request()->routeIs('admin.partlists.*') ? 'active' : '' }}"
                    href="{{ route('admin.partlists.index') }}">

                    <i class="bi bi-list-ul me-2"></i>
                    Manajemen Partlist
                </a>

            </nav>

            <hr class="text-white my-4">

            {{-- PROFILE --}}
            <div class="d-flex align-items-center text-white mb-4">

                {{-- Avatar --}}
                <div
                    class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-2 profile-avatar">

                    {{ strtoupper(substr(auth()->user()->name ?? '-', 0, 1)) }}

                </div>

                {{-- User Info --}}
                <div>
                    <div class="fw-semibold">
                        {{ auth()->user()->name ?? '-' }}
                    </div>

                    <small class="text-light">
                        {{ auth()->user()->npk ?? '-' }}
                    </small>
                </div>

            </div>

            {{-- LOGOUT --}}
            <form action="{{ route('logout') }}" method="POST" class="mt-auto">
                @csrf

                <button type="submit" class="btn btn-danger w-100">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Logout
                </button>
            </form>

        </aside>

        {{-- MAIN CONTENT --}}
        <main class="main-content">

            <div class="content-wrapper">

                {{-- ALERT SUCCESS --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- ALERT ERROR --}}
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- PAGE CONTENT --}}
                @yield('content')

            </div>

        </main>

    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')

</body>

</html>