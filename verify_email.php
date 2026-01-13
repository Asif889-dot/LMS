<?php
require_once 'config.php';

$msg = "No token provided.";
$class = "alert-warning";
$icon = "help-circle";
$title = "Verification Status";

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if token exists
    $query = "SELECT id FROM users WHERE verification_token = ? AND is_verified = 0 LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];

        // Update user to verified
        $update = "UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?";
        $u_stmt = $conn->prepare($update);
        $u_stmt->bind_param("i", $user_id);

        if ($u_stmt->execute()) {
            $title = "Email Verified!";
            $msg = "Your email has been successfully verified. You can now access your dashboard.";
            $class = "alert-success";
            $icon = "check-circle";
        }
    } else {
        $title = "Verification Failed";
        $msg = "The verification link is invalid, expired, or has already been used.";
        $class = "alert-danger";
        $icon = "x-circle";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email | LMS-PRO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
        }

        .verify-card {
            border: none;
            max-width: 500px;
            margin-top: 100px;
        }

        .icon-box {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .bg-success-light {
            background: #dcfce7;
            color: #166534;
        }

        .bg-danger-light {
            background: #fee2e2;
            color: #991b1b;
        }

        .bg-warning-light {
            background: #fef9c3;
            color: #854d0e;
        }
    </style>
</head>

<body>

    <div class="container d-flex justify-content-center">
        <div class="card verify-card shadow-sm p-4 p-sm-5 text-center rounded-4">

            <div class="icon-box <?php echo str_replace('alert-', 'bg-', $class); ?>-light">
                <i data-lucide="<?php echo $icon; ?>" style="width: 32px; height: 32px;"></i>
            </div>

            <h2 class="fw-bold mb-3"><?php echo $title; ?></h2>
            <p class="text-secondary mb-4"><?php echo $msg; ?></p>

            <?php if ($class === "alert-success"): ?>
                <a href="login.php" class="btn btn-primary w-100 py-2 rounded-3 fw-bold shadow-sm text-decoration-none">
                    Go to Login
                </a>
            <?php else: ?>
                <a href="register.php" class="btn btn-outline-secondary w-100 py-2 rounded-3 fw-bold text-decoration-none">
                    Back to Registration
                </a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>