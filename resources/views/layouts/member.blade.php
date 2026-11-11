<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard - @yield('title', 'Control Tools')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="bg-dark sidebar d-flex flex-column p-3 vh-100" style="width: 280px;">
            <h4 class="text-white mb-4">Control Tools</h4>

            <nav class="nav flex-column">
                @yield('sidebar')
            </nav>

            <br>
            <hr class="text-white">

            <div class="d-flex align-items-center text-white">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                    style="width:40px; height:40px; font-weight:bold;">
                    {{ strtoupper(substr(auth()->user()->name ?? '-', 0, 1)) }}
                </div>

                <div>
                    {{ auth()->user()->name ?? '-' }} <br>
                    <small>{{ auth()->user()->npk ?? '-' }}</small>
                </div>
            </div>

            <a class="nav-link text-white mt-auto" href="{{ route('logout') }}"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
        </div>

        <!-- Main content -->
        <div class="flex-grow-1 p-4">
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>