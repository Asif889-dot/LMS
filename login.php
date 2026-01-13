<?php
ob_start();
require_once 'config.php';
require_once 'User.php';

$user = new User();

// Redirect if already logged in
if ($user->isLoggedIn()) {
    $role = $_SESSION['role'];
    if ($role === 'admin') header("Location: admin_users.php");
    elseif ($role === 'teacher') header("Location: teacher_dashboard.php");
    else header("Location: dashboard.php");
    exit;
}

$error = "";
$warning = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = htmlspecialchars(strip_tags(trim($_POST['username'])));
    $password = $_POST['password'];

    $login_status = $user->login($username, $password);

    if ($login_status === "success") {
        $role = $_SESSION['role'];

        // --- CRITICAL FIX START ---
        session_write_close();
        // --- CRITICAL FIX END ---

        if ($role === 'teacher') {
            header("Location: teacher_dashboard.php");
        } elseif ($role === 'admin') {
            header("Location: admin_users.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    } elseif ($login_status === "not_verified") {
        $warning = "Your email is not verified. Please check your inbox for the verification link.";
    } elseif ($login_status === "account_disabled") {
        $error = "This account has been disabled by the administrator.";
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | LMS-PRO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .bg-auth {
            background: #f0f4f8;
            min-height: 100vh;
        }

        .auth-card {
            border: none;
            transition: all 0.3s ease;
        }

        .input-group-text {
            border-color: #e2e8f0;
        }

        .form-control {
            border-color: #e2e8f0;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            border-color: #3b82f6;
        }
    </style>
</head>

<body class="bg-auth">

    <div class="container">
        <div class="row min-vh-100 align-items-center justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="text-center mb-4">
                    <a href="index.php" class="text-decoration-none d-inline-flex align-items-center mb-2">
                        <i data-lucide="book-open" class="text-primary me-2" style="width: 32px; height: 32px;"></i>
                        <span class="fs-3 fw-bold text-dark">LMS-PRO</span>
                    </a>
                </div>

                <div class="auth-card p-4 p-sm-5 rounded-4 shadow-sm bg-white">
                    <h3 class="fw-bold mb-4">Welcome Back</h3>

                    <?php if ($error): ?>
                        <div class="alert alert-danger d-flex align-items-center border-0 py-2 mb-4">
                            <i data-lucide="alert-circle" class="me-2" style="width: 18px;"></i>
                            <div style="font-size: 0.85rem;"><?php echo $error; ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($warning): ?>
                        <div class="alert alert-warning d-flex align-items-center border-0 py-2 mb-4">
                            <i data-lucide="mail" class="me-2" style="width: 18px;"></i>
                            <div style="font-size: 0.85rem;"><?php echo $warning; ?></div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" autocomplete="off">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted text-uppercase">Username or Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i data-lucide="user" style="width: 16px;"></i></span>
                                <input type="text" class="form-control" name="username" placeholder="Username or Email" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between">
                                <label class="form-label small fw-semibold text-muted text-uppercase">Password</label>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i data-lucide="lock" style="width: 16px;"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                                <button class="btn btn-outline-light border bg-white text-muted" type="button" id="togglePassword">
                                    <i data-lucide="eye" id="toggleIcon" style="width: 18px;"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 shadow-sm mb-3 fw-bold">
                            Sign in
                        </button>
                    </form>

                    <p class="text-center mb-0 small text-secondary">
                        New user? <a href="register.php" class="text-primary fw-bold text-decoration-none">Sign up</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        lucide.createIcons();

        const toggleBtn = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#password');
        const toggleIcon = document.querySelector('#toggleIcon');

        toggleBtn.addEventListener('click', () => {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            const newIcon = isPassword ? 'eye-off' : 'eye';
            toggleIcon.setAttribute('data-lucide', newIcon);
            lucide.createIcons();
        });
    </script>
</body>

</html>