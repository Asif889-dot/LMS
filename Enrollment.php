<?php
require_once 'config.php';

class Enrollment
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function enroll($user_id, $course_id)
    {
        $stmt = $this->conn->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE status = 'enrolled'");
        $stmt->bind_param("ii", $user_id, $course_id);
        return $stmt->execute();
    }

    public function drop($user_id, $course_id)
    {
        $stmt = $this->conn->prepare("UPDATE enrollments SET status = 'dropped' WHERE user_id = ? AND course_id = ?");
        $stmt->bind_param("ii", $user_id, $course_id);
        return $stmt->execute();
    }

    public function getEnrollmentsByUser($user_id)
    {
        $stmt = $this->conn->prepare("
            SELECT c.*, e.status, e.enrolled_at
            FROM courses c
            JOIN enrollments e ON c.id = e.course_id
            WHERE e.user_id = ? AND e.status = 'enrolled'
            ORDER BY e.enrolled_at DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    // Inside Enrollment class
    public function getCourseProgress($user_id, $course_id, $conn)
    {
        // 1. Get total lessons
        $total_sql = "SELECT COUNT(l.id) as total FROM lessons l 
                  JOIN modules m ON l.module_id = m.id 
                  WHERE m.course_id = ?";

        $stmt = $conn->prepare($total_sql);

        // ERROR CHECKING:
        if (!$stmt) {
            die("SQL Error in Total Lessons: " . $conn->error);
        }

        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $total = $stmt->get_result()->fetch_assoc()['total'];

        if ($total == 0) return 0;

        // 2. Get completed lessons
        $comp_sql = "SELECT COUNT(*) as completed FROM lesson_completions 
                  WHERE user_id = ? AND course_id = ?";

        $stmt = $conn->prepare($comp_sql);

        if (!$stmt) {
            die("SQL Error in Completed Lessons: " . $conn->error);
        }

        $stmt->bind_param("ii", $user_id, $course_id);
        $stmt->execute();
        $completed = $stmt->get_result()->fetch_assoc()['completed'];

        return round(($completed / $total) * 100);
    }

    public function isEnrolled($user_id, $course_id)
    {
        $stmt = $this->conn->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ? AND status = 'enrolled'");
        $stmt->bind_param("ii", $user_id, $course_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
}
