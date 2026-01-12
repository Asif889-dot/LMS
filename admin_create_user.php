<?php
require_once 'User.php';
require_once 'config.php';

$user = new User();

// Protect the page: Only allow logged-in Admins
if (!$user->isLoggedIn() || !$user->hasRole('admin')) {
    header("Location: login.php");
    exit;
}

$message = "";
$messageType = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Basic validation
    if (empty($username) || empty($email) || empty($password)) {
        $message = "All fields are required.";
        $messageType = "danger";
    } else {
        // Use the register method from your User class
        if ($user->register($username, $email, $password, $role)) {
            $message = "Account created successfully!";
            $messageType = "success";
        } else {
            $message = "Error: Username or Email might already exist.";
            $messageType = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Staff | LMS-PRO</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        }

        body {
            background: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
        }

        .create-card {
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            background: white;
            overflow: hidden;
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .form-control,
        .form-select {
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            background-color: #fcfdfe;
        }

        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            border-color: #6366f1;
        }

        .btn-submit {
            background: var(--primary-gradient);
            border: none;
            padding: 0.8rem;
            font-weight: 700;
            border-radius: 0.75rem;
            transition: transform 0.2s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .back-link {
            text-decoration: none;
            color: #64748b;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: #6366f1;
        }
    </style>
</head>

<body>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">

                <div class="mb-4">
                    <a href="admin_users.php" class="back-link">
                        <i data-lucide="arrow-left" class="me-2" style="width: 18px;"></i>
                        Back to Management
                    </a>
                </div>

                <div class="create-card card">
                    <div class="p-4 p-md-5">
                        <div class="text-center mb-5">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                                <i data-lucide="user-plus" class="text-primary" style="width: 32px; height: 32px;"></i>
                            </div>
                            <h3 class="fw-bold">Create Staff Account</h3>
                            <p class="text-muted">Register a new teacher or administrator for the platform.</p>
                        </div>

                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $messageType; ?> border-0 rounded-3 d-flex align-items-center mb-4">
                                <i data-lucide="<?php echo ($messageType == 'success') ? 'check-circle' : 'alert-circle'; ?>" class="me-2"></i>
                                <div class="small fw-medium"><?php echo $message; ?></div>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Full Username</label>
                                    <input type="text" name="username" class="form-control" placeholder="e.g. john_doe" required>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" placeholder="staff@lms-pro.com" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Temporary Password</label>
                                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label class="form-label">Assign Role</label>
                                    <select name="role" class="form-select" required>
                                        <option value="teacher">Teacher / Instructor</option>
                                        <option value="admin">System Administrator</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-submit w-100 shadow-sm text-white">
                                <i data-lucide="plus" class="me-1" style="width: 18px;"></i>
                                Finalize and Create Account
                            </button>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <p class="text-muted small">The new user will be able to log in immediately with their temporary credentials.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>