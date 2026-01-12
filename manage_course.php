<?php
require_once 'User.php';
require_once 'config.php';
$user = new User();

// 1. Protection & Identity Check
if (!$user->isLoggedIn() || !$user->hasRole('teacher')) {
    header("Location: login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$success = $error = "";

// 2. Security: Ensure this teacher actually owns this course
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ? AND teacher_id = ?");
$stmt->bind_param("ii", $course_id, $teacher_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    die("Course not found or access denied.");
}

// 3. Handle Updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_course'])) {
    $title = htmlspecialchars(strip_tags(trim($_POST['title'])));
    $category = htmlspecialchars(strip_tags(trim($_POST['category'])));
    $description = htmlspecialchars(strip_tags(trim($_POST['description'])));

    $update_stmt = $conn->prepare("UPDATE courses SET title = ?, category = ?, description = ? WHERE id = ? AND teacher_id = ?");
    $update_stmt->bind_param("sssii", $title, $category, $description, $course_id, $teacher_id);

    if ($update_stmt->execute()) {
        $success = "Course updated successfully!";
        // Refresh local data
        $course['title'] = $title;
        $course['category'] = $category;
        $course['description'] = $description;
    } else {
        $error = "Update failed: " . $conn->error;
    }
}

// 4. Handle Deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_course'])) {
    $delete_stmt = $conn->prepare("DELETE FROM courses WHERE id = ? AND teacher_id = ?");
    $delete_stmt->bind_param("ii", $course_id, $teacher_id);

    if ($delete_stmt->execute()) {
        header("Location: teacher_dashboard.php?msg=deleted");
        exit;
    } else {
        $error = "Delete failed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Course | LMS-PRO</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            background: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .content-builder-card {
            background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
            color: white;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.875rem;
            color: #475569;
        }

        .btn-primary {
            background: #2563eb;
            border: none;
            border-radius: 8px;
            padding: 10px 24px;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold mb-0">Course Settings</h2>
                        <p class="text-muted mb-0">Edit details for: <span class="text-primary fw-medium"><?php echo htmlspecialchars($course['title']); ?></span></p>
                    </div>
                    <a href="teacher_dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">
                        <i data-lucide="arrow-left" class="icon-sm me-1" style="width:16px"></i> Dashboard
                    </a>
                </div>

                <div class="card content-builder-card p-4 mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="fw-bold">Curriculum Builder</h4>
                            <p class="mb-0 opacity-75">Organize your course into modules and create lesson plans with videos and text.</p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <a href="course_content.php?course_id=<?php echo $course_id; ?>" class="btn btn-light text-primary fw-bold rounded-pill px-4 shadow">
                                <i data-lucide="layers" class="me-1" style="width:18px"></i> Edit Content
                            </a>
                        </div>
                    </div>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center">
                        <i data-lucide="check-circle" class="me-2"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <div class="card p-4 mb-4">
                    <h5 class="fw-bold mb-4">General Information</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Course Title</label>
                            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($course['title']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="Programming" <?php echo ($course['category'] == 'Programming') ? 'selected' : ''; ?>>Programming</option>
                                <option value="Design" <?php echo ($course['category'] == 'Design') ? 'selected' : ''; ?>>Design</option>
                                <option value="Marketing" <?php echo ($course['category'] == 'Marketing') ? 'selected' : ''; ?>>Marketing</option>
                                <option value="Business" <?php echo ($course['category'] == 'Business') ? 'selected' : ''; ?>>Business</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="6" required><?php echo htmlspecialchars($course['description']); ?></textarea>
                        </div>
                        <button type="submit" name="update_course" class="btn btn-primary">
                            Update Settings
                        </button>
                    </form>
                </div>

                <div class="card border-1 border-danger bg-danger bg-opacity-10 p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i data-lucide="alert-triangle" class="text-danger me-2"></i>
                        <h5 class="text-danger fw-bold mb-0">Danger Zone</h5>
                    </div>
                    <p class="text-muted small">Once you delete a course, there is no going back. All student progress, modules, and lessons will be permanently removed.</p>
                    <form method="POST" onsubmit="return confirm('WARNING: This will delete the entire course, all modules, and all lessons. Continue?');">
                        <button type="submit" name="delete_course" class="btn btn-danger rounded-pill px-4">
                            Delete Course Permanently
                        </button>
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