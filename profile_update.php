<?php
require_once 'User.php';
require_once 'config.php';

$user = new User();

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$current_user = $user->getUserById($user_id);

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Handle profile picture upload
    $profile_picture_path = isset($current_user['profile_picture']) ? $current_user['profile_picture'] : null; // Keep existing if no new upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/profile_pics/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_name = basename($_FILES['profile_picture']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($file_ext, $allowed_exts)) {
            $errors[] = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        } elseif ($_FILES['profile_picture']['size'] > 2 * 1024 * 1024) { // 2MB limit
            $errors[] = "File size must be less than 2MB.";
        } else {
            $new_file_name = 'user_' . $user_id . '_' . time() . '.' . $file_ext;
            $target_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_path)) {
                // Delete old profile picture if exists
                if (isset($current_user['profile_picture']) && $current_user['profile_picture'] && file_exists($current_user['profile_picture'])) {
                    unlink($current_user['profile_picture']);
                }
                $profile_picture_path = $target_path;
            } else {
                $errors[] = "Failed to upload profile picture.";
            }
        }
    }

    // Validation
    $errors = $errors ?? [];

    if (empty($username)) {
        $errors[] = "Username is required.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters long.";
        }
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }
    }

    // Check if username or email already exists for another user
    if (!empty($username) && $username !== $current_user['username']) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->bind_param("si", $username, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "Username already exists.";
        }
        $stmt->close();
    }

    if (!empty($email) && $email !== $current_user['email']) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "Email already exists.";
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $update_password = !empty($password) ? $password : null;
        if ($user->updateProfile($user_id, $username, $email, $update_password, $profile_picture_path)) {
            // Update session variables
            $_SESSION['username'] = $username;
            $_SESSION['user_name'] = $username; // Assuming this is used in dashboard
            $message = "Profile updated successfully!";
            $message_type = "success";
            $current_user = $user->getUserById($user_id); // Refresh data
        } else {
            $message = "Failed to update profile. Please try again.";
            $message_type = "danger";
        }
    } else {
        $message = implode("<br>", $errors);
        $message_type = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile | Smart Choice Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --sca-primary: #2563eb;
            --sca-secondary: #64748b;
        }

        body {
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
        }

        .navbar {
            background: white;
            border-bottom: 1px solid #e2e8f0;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .btn-primary {
            background-color: var(--sca-primary);
            border: none;
        }

        .form-control:focus {
            border-color: var(--sca-primary);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light sticky-top mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="dashboard.php">
                Smart Choice Academy
            </a>
            <div class="ms-auto">
                <a href="dashboard.php" class="btn btn-outline-primary btn-sm rounded-pill">Back to Dashboard</a>
            </div>
        </div>
    </nav>

    <main class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card p-4">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">Update Profile</h2>
                        <p class="text-muted">Keep your account information up to date</p>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username"
                                   value="<?php echo htmlspecialchars($current_user['username']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?php echo htmlspecialchars($current_user['email']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="profile_picture" class="form-label">Profile Picture</label>
                            <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
                            <div class="form-text">Allowed formats: JPG, JPEG, PNG, GIF. Max size: 2MB</div>
                            <?php if (isset($current_user['profile_picture']) && $current_user['profile_picture']): ?>
                                <div class="mt-2">
                                    <small class="text-muted">Current profile picture:</small><br>
                                    <img src="<?php echo htmlspecialchars($current_user['profile_picture']); ?>" alt="Current Profile" class="rounded-circle mt-1" style="width: 50px; height: 50px; object-fit: cover;">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <div class="form-text">Minimum 6 characters</div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>
