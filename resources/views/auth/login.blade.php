<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>CT-Insulator Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(to bottom right, #ffffff, #c77f7f);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* ANIMATION */
        .login-card {
            width: 380px;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            text-align: center;

            animation: fadeSlide 0.8s ease;
        }

        @keyframes fadeSlide {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card img {
            width: 90px;
        }

        .login-card h4 {
            font-weight: bold;
            margin-top: 15px;
        }

        .login-card small {
            color: #666;
        }

        .form-control {
            border-radius: 10px;
            height: 45px;
        }

        .input-group-text {
            border-radius: 10px;
            cursor: pointer;
        }

        .btn-login {
            background-color: #2f4fd8;
            color: white;
            border-radius: 10px;
            height: 45px;
        }

        .btn-login:hover {
            background-color: #1f3bb3;
        }

        /* FOOTER */
        .footer {
            color: white;
            text-align: center;
            padding: 12px;
            font-size: 13px;
        }
    </style>
</head>

<script>
    const form = document.querySelector("form");
    const btn = document.getElementById("loginBtn");
    const text = document.getElementById("btnText");
    const spinner = document.getElementById("spinner");

    form.addEventListener("submit", function () {
        btn.disabled = true;
        text.innerText = "Loading...";
        spinner.classList.remove("d-none");
    });
</script>

<body>

    <div class="wrapper">
        <div class="login-card">

            <!-- Logo -->
            <img src="{{ asset('denso.png') }}" alt="Denso">

            <h4>Selamat datang di<br>CT - Insulator</h4>
            <small>(Control Tools - Insulator)</small>

            <!-- Error -->
            @if(session('error'))
                <div class="alert alert-danger mt-3">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success mt-3">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Form -->
            <form action="/login" method="POST" class="mt-3">
                @csrf

                <div class="mb-3">
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                </div>

                <!-- PASSWORD + SHOW BUTTON -->
                <div class="mb-3 input-group">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Password"
                        required>

                    <span class="input-group-text" onclick="togglePassword()">
                        <i id="eyeIcon" class="fa fa-eye"></i>
                    </span>
                </div>

                <button type="submit" id="loginBtn" class="btn btn-login w-100 mb-2">
                    <span id="btnText">Login</span>
                    <span id="spinner" class="spinner-border spinner-border-sm ms-2 d-none"></span>
                </button>
            </form>

        </div>
    </div>

    <!-- FOOTER -->
    <footer class="footer">
        <div>© 2026 Spark Plug DNIA Fajar Plant.</div>
        <div>Developed by Irvan Maulana</div>
    </footer>

    <!-- SCRIPT -->
    <script>
        function togglePassword() {
            const password = document.getElementById("password");
            const icon = document.getElementById("eyeIcon");

            if (password.type === "password") {
                password.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                password.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>

</body>

</html>