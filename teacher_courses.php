<?php
require_once 'User.php';
require_once 'Course.php'; // Assuming you have a Course class
require_once 'config.php';

$user = new User();
if (!$user->isLoggedIn() || $_SESSION['role'] !== 'teacher') {
    header("Location: dashboard.php");
    exit();
}

$teacher_name = $_SESSION['username'];
// Fetch only courses where the instructor matches the logged-in teacher
$stmt = $conn->prepare("SELECT * FROM courses WHERE instructor = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $teacher_name);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Teacher Panel | LMS-PRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="style.css">
</head>

<body class="bg-light">

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark">My Courses</h2>
                <p class="text-secondary">Manage your curriculum and students</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                <i data-lucide="plus-circle" class="icon-sm me-1"></i> Create New Course
            </button>
        </div>

        <div class="row g-4">
            <?php while ($course = $result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="content-card p-0 overflow-hidden">
                        <div class="bg-primary p-4 text-white text-center">
                            <i data-lucide="layout" style="width: 48px; height: 48px;" class="opacity-50"></i>
                        </div>
                        <div class="p-4">
                            <h5 class="fw-bold"><?php echo htmlspecialchars($course['title']); ?></h5>
                            <p class="text-secondary small text-truncate"><?php echo htmlspecialchars($course['description']); ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="badge bg-soft-primary text-primary">Active</span>
                                <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-outline-secondary rounded-pill">Edit</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="modal fade" id="addCourseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header">
                    <h5 class="fw-bold">New Course Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="process_course.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Course Title</label>
                            <input type="text" name="title" class="form-control rounded-3" placeholder="e.g. Master Python 2026" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control rounded-3" rows="4" required></textarea>
                        </div>
                        <input type="hidden" name="instructor" value="<?php echo $_SESSION['username']; ?>">
                    </div>
                    <div class="modal-footer border-0">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill">Publish Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>