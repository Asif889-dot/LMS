<?php
require_once 'config.php';

echo "<h2>Database Diagnostic Tool</h2>";

// 1. Check Connection
if ($conn->connect_error) {
    die("<p style='color:red'>Connection failed: " . $conn->connect_error . "</p>");
}
echo "<p style='color:green'>✔ Database Connected Successfully.</p>";

// 2. Check for the Super Admin User
$username = 'super_admin';
$stmt = $conn->prepare("SELECT id, username, email, role, status, password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p style='color:red'>✘ User '$username' NOT found in the database.</p>";
    echo "<p>Try running the SQL INSERT command again in phpMyAdmin.</p>";
} else {
    $user = $result->fetch_assoc();
    echo "<p style='color:green'>✔ User found!</p>";
    echo "<ul>
            <li><strong>ID:</strong> {$user['id']}</li>
            <li><strong>Role:</strong> {$user['role']}</li>
            <li><strong>Status:</strong> {$user['status']}</li>
            <li><strong>Password Hash:</strong> <small>{$user['password']}</small></li>
          </ul>";

    // 3. Test Password Verification
    $test_pass = 'admin123';
    if (password_verify($test_pass, $user['password'])) {
        echo "<p style='color:green'>✔ Password check PASSED for '$test_pass'.</p>";
    } else {
        echo "<p style='color:red'>✘ Password check FAILED for '$test_pass'. The hash in the DB is incorrect.</p>";
    }
}
