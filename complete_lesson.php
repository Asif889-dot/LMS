<?php
require_once 'User.php';
require_once 'config.php';
require_once 'Enrollment.php'; // Include this to use progress logic

$user = new User();
$enrollment = new Enrollment();

// 1. Session Protection
if (!$user->isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// 2. Process Completion via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id   = $_SESSION['user_id'];
    $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $next_id   = isset($_POST['next_id']) ? intval($_POST['next_id']) : 0;

    if ($lesson_id > 0 && $course_id > 0) {

        // A. Record the lesson completion
        // INSERT IGNORE ensures no error if they click 'Complete' twice
        $stmt = $conn->prepare("INSERT IGNORE INTO lesson_completions (user_id, course_id, lesson_id) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $course_id, $lesson_id);
        $stmt->execute();

        // B. Check Overall Course Progress
        // This is where we decide if they just finished a lesson or the WHOLE course
        $progress = $enrollment->getCourseProgress($user_id, $course_id, $conn);

        // C. Smart Redirection
        if ($next_id > 0) {
            // There is another lesson to watch
            header("Location: view_lesson.php?id=" . $next_id);
        } else {
            // No next_id means this was the last lesson in the list
            if ($progress >= 100) {
                // They finished everything! Update enrollment status if you have that column
                $update = $conn->prepare("UPDATE enrollments SET status = 'completed' WHERE user_id = ? AND course_id = ?");
                $update->bind_param("ii", $user_id, $course_id);
                $update->execute();

                // Send to course details with a "Course Completed" celebration flag
                header("Location: course_details.php?id=" . $course_id . "&status=course_completed");
            } else {
                // They finished the lesson but maybe skipped others? Send back to overview
                header("Location: course_details.php?id=" . $course_id . "&status=lesson_done");
            }
        }
        exit;
    }
}

// Fallback for direct URL access
header("Location: dashboard.php");
exit;
