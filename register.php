<?php
require_once 'User.php';
require_once 'config.php'; // Ensure sanitize() is defined here
// Load PHPMailer (install via: composer require phpmailer/phpmailer)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$user = new User();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Generate unique verification token
        $token = bin2hex(random_bytes(50));

        // Update your User class register method to accept and store the token
        if ($user->register($username, $email, $password, $token)) {

            // --- PHPMailer SMTP Integration ---
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'mail.softzila.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'noreply@softzila.com';
                $mail->Password   = 'P@kist@n786';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                $mail->setFrom('noreply@softzila.com', 'Smart Choice Academy');
                $mail->addAddress($email, $username);

                $mail->isHTML(true);
                $mail->Subject = 'Verify Your Email - Smart Choice Academy';

                // Verification Link
                $verify_link = "https://portal.smartchoiceacademy.softzila.com/verify_email.php?token=" . $token;

                $mail->Body = "
                    <div style='font-family: Arial; padding: 20px; border: 1px solid #eee;'>
                        <h2>Welcome to Smart Choice Academy, $username!</h2>
                        <p>Please click the button below to verify your email address and activate your account.</p>
                        <a href='$verify_link' style='background: #1e40af; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Verify Email Address</a>
                        <p style='margin-top: 20px; font-size: 12px; color: #777;'>If the button doesn't work, copy and paste this link: $verify_link</p>
                    </div>";

                $mail->send();
                $success = "Registration successful! Please check your email ($email) to verify your account before logging in.";
            } catch (Exception $e) {
                $error = "Account created, but verification email could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $error = "Registration failed. Username or email may already exist.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Register for LMS</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Register</button>
                        </form>
                        <p class="text-center mt-3">Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>