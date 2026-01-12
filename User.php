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
     * Registers a new user with a default 'active' status
     */
    public function register($username, $email, $password, $role = 'student')
    {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $status = 'active'; // Default status for new registrations

        $stmt = $this->conn->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $email, $hashed_password, $role, $status);
        return $stmt->execute();
    }

    /**
     * Handles login, session creation, and account status verification
     */
    public function login($username, $password)
    {
        global $conn;

        $stmt = $conn->prepare("SELECT id, username, password, role, status FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                if ($user['status'] === 'disabled') {
                    return "account_disabled";
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
     * Fetches complete user details by ID for profile or admin views
     */
    public function getUserById($id)
    {
        $stmt = $this->conn->prepare("SELECT id, username, email, role, status, created_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Gatekeeper method to verify if the logged-in user has a specific role
     */
    public function hasRole($role)
    {
        return (isset($_SESSION['role']) && $_SESSION['role'] === $role);
    }
}
