<?php
require_once 'config.php';
require_once 'User.php';

$user = new User();
if (!$user->isLoggedIn() || $_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Use the register method from your existing User class
    if ($user->register($username, $email, $password, $role)) {
        header("Location: admin_users.php?success=AccountCreated");
    } else {
        header("Location: admin_users.php?error=FailedToCreate");
    }
}
