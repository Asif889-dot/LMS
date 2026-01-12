<?php
require_once 'config.php';
require_once 'User.php';
session_start();

$user = new User();
if (!$user->isLoggedIn() || $_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'toggle') {
        // Switch between active and inactive
        $stmt = $conn->prepare("UPDATE users SET status = IF(status='active', 'inactive', 'active') WHERE id = ? AND id != ?");
        $stmt->bind_param("ii", $id, $_SESSION['user_id']);
    } elseif ($action === 'delete') {
        // Permanently remove
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND id != ?");
        $stmt->bind_param("ii", $id, $_SESSION['user_id']);
    }

    if ($stmt->execute()) {
        header("Location: admin_users.php?msg=success");
    } else {
        header("Location: admin_users.php?msg=error");
    }
}
