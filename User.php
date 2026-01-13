<?php
require_once 'config.php';

class User
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;

        // Ensure session is started for all User operations
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Registers a new user with a verification token
     * Updated to include $token and set is_verified to 0 by default
     */
    public function register($username, $email, $password, $token, $role = 'student')
    {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $status = 'active';
        $is_verified = 0; // New users must verify email

        $stmt = $this->conn->prepare("INSERT INTO users (username, email, password, role, status, verification_token, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $username, $email, $hashed_password, $role, $status, $token, $is_verified);
        return $stmt->execute();
    }

    /**
     * Handles login, session creation, and email verification check
     * Updated to check is_verified status
     */
    public function login($username, $password)
    {
        // Use $this->conn consistently
        $stmt = $this->conn->prepare("SELECT id, username, password, role, status, is_verified FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {

                // 1. Check if account is disabled by admin
                if ($user['status'] === 'disabled') {
                    return "account_disabled";
                }

                // 2. NEW: Check if email is verified
                if ($user['is_verified'] == 0) {
                    return "not_verified";
                }

                // Start session if not already started
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                // Force a fresh session ID to prevent "ghost" sessions
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['logged_in'] = true;

                return "success";
            }
        }
        return "error";
    }

    /**
     * Checks if a user session is active
     */
    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Clears session data to log out
     */
    public function logout()
    {
        session_unset();
        session_destroy();
    }

    /**
     * Fetches complete user details by ID
     */
    public function getUserById($id)
    {
        $stmt = $this->conn->prepare("SELECT id, username, email, role, status, profile_picture, created_at, is_verified FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Gatekeeper method to verify roles
     */
    public function hasRole($role)
    {
        return (isset($_SESSION['role']) && $_SESSION['role'] === $role);
    }

    /**
     * Updates user profile information
     */
    public function updateProfile($id, $username, $email, $password = null, $profile_picture = null)
    {
        if ($password && $profile_picture !== null) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("UPDATE users SET username = ?, email = ?, password = ?, profile_picture = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $username, $email, $hashed_password, $profile_picture, $id);
        } elseif ($password) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssi", $username, $email, $hashed_password, $id);
        } elseif ($profile_picture !== null) {
            $stmt = $this->conn->prepare("UPDATE users SET username = ?, email = ?, profile_picture = ? WHERE id = ?");
            $stmt->bind_param("sssi", $username, $email, $profile_picture, $id);
        } else {
            $stmt = $this->conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $username, $email, $id);
        }
        return $stmt->execute();
    }
}
