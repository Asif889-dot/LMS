<?php
require_once 'User.php';
require_once 'config.php';
$user = new User();

if (!$user->isLoggedIn() || !$user->hasRole('teacher')) {
    header("Location: login.php");
    exit;
}

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$teacher_id = $_SESSION['user_id'];

// Verify ownership
$stmt = $conn->prepare("SELECT title FROM courses WHERE id = ? AND teacher_id = ?");
$stmt->bind_param("ii", $course_id, $teacher_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) die("Access Denied.");

// Handle Adding Module
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_module'])) {
    $m_title = htmlspecialchars($_POST['module_title']);
    $stmt = $conn->prepare("INSERT INTO modules (course_id, title) VALUES (?, ?)");
    $stmt->bind_param("is", $course_id, $m_title);
    $stmt->execute();
}

// Fetch Modules and Lessons
$modules = [];
$res = $conn->query("SELECT * FROM modules WHERE course_id = $course_id ORDER BY order_number ASC");
while ($row = $res->fetch_assoc()) {
    $m_id = $row['id'];
    $lesson_res = $conn->query("SELECT * FROM lessons WHERE module_id = $m_id ORDER BY order_number ASC");
    $row['lessons'] = $lesson_res->fetch_all(MYSQLI_ASSOC);
    $modules[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Course Builder | LMS-PRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            background: #f1f5f9;
            font-family: sans-serif;
        }

        .module-card {
            border: none;
            border-left: 5px solid #3b82f6;
            margin-bottom: 20px;
        }

        .lesson-item {
            background: #fff;
            border: 1px solid #e2e8f0;
            padding: 10px;
            border-radius: 8px;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold"><?php echo $course['title']; ?></h1>
                <p class="text-muted">Course Curriculum Builder</p>
            </div>
            <a href="teacher_dashboard.php" class="btn btn-outline-dark">Done Building</a>
        </div>

        <div class="row">
            <div class="col-md-8">
                <?php foreach ($modules as $module): ?>
                    <div class="card module-card shadow-sm p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0"><?php echo $module['title']; ?></h5>
                            <a href="add_lesson.php?module_id=<?php echo $module['id']; ?>&course_id=<?php echo $course_id; ?>" class="btn btn-sm btn-primary">+ Add Lesson</a>
                        </div>

                        <div class="mt-3">
                            <?php foreach ($module['lessons'] as $lesson): ?>
                                <div class="lesson-item d-flex align-items-center">
                                    <i data-lucide="file-text" class="me-2 text-muted" style="width:16px"></i>
                                    <span><?php echo $lesson['title']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="col-md-4">
                <div class="card p-4 sticky-top" style="top: 20px;">
                    <h5 class="fw-bold mb-3">Add Module</h5>
                    <form method="POST">
                        <input type="text" name="module_title" class="form-control mb-3" placeholder="Module Name (e.g. Getting Started)" required>
                        <button type="submit" name="add_module" class="btn btn-dark w-100">Create Module</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>