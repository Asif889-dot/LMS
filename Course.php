<?php
require_once 'config.php';

class Course {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function getAllCourses() {
        $result = $this->conn->query("SELECT * FROM courses ORDER BY created_at DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getCourseById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function addCourse($title, $description, $instructor) {
        $stmt = $this->conn->prepare("INSERT INTO courses (title, description, instructor) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $description, $instructor);
        return $stmt->execute();
    }

    public function updateCourse($id, $title, $description, $instructor) {
        $stmt = $this->conn->prepare("UPDATE courses SET title = ?, description = ?, instructor = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $description, $instructor, $id);
        return $stmt->execute();
    }

    public function deleteCourse($id) {
        $stmt = $this->conn->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>
