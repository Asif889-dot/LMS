<?php
require_once 'Enrollment.php';
require_once 'User.php';

header('Content-Type: application/json');

$user = new User();
$enrollment = new Enrollment();

if (!$user->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['course_id'], $_POST['action'])) {
    $course_id = (int)$_POST['course_id'];
    $user_id = $_SESSION['user_id'];
    $action = $_POST['action'];

    $result = false;

    // Switch between the two methods in your Enrollment class
    if ($action === 'enroll') {
        $result = $enrollment->enroll($user_id, $course_id);
    } elseif ($action === 'drop') {
        $result = $enrollment->drop($user_id, $course_id);
    }

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Success!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database operation failed']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
}
