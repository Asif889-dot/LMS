<?php
require_once 'User.php';
require_once 'config.php';
$user = new User();

// 1. Protect the page
if (!$user->isLoggedIn() || !$user->hasRole('teacher')) {
    header("Location: login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$course_count = 0;
$student_count = 0;
$courses_result = false; // Initialize as false

// 2. Fetch Stats & Courses (with error suppression to prevent crashes)
// Check Course Count
$stmt_courses = $conn->prepare("SELECT COUNT(*) as count FROM courses WHERE teacher_id = ?");
if ($stmt_courses) {
    $stmt_courses->bind_param("i", $teacher_id);
    $stmt_courses->execute();
    $course_count = $stmt_courses->get_result()->fetch_assoc()['count'];
    $stmt_courses->close();
}

// Check Student Count
$stmt_students = $conn->prepare("SELECT COUNT(DISTINCT student_id) as count FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE c.teacher_id = ?");
if ($stmt_students) {
    $stmt_students->bind_param("i", $teacher_id);
    $stmt_students->execute();
    $student_count = $stmt_students->get_result()->fetch_assoc()['count'];
    $stmt_students->close();
}

// Fetch Course List
$stmt_list = $conn->prepare("SELECT * FROM courses WHERE teacher_id = ? ORDER BY created_at DESC");
if ($stmt_list) {
    $stmt_list->bind_param("i", $teacher_id);
    $stmt_list->execute();
    $courses_result = $stmt_list->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard | LMS-PRO</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --teacher-gradient: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
        }

        body {
            background: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
        }

        .navbar {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .stat-card {
            border: none;
            border-radius: 1.25rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .course-card {
            border: none;
            border-radius: 1rem;
            background: white;
            transition: all 0.3s;
        }

        .course-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }

        .btn-teacher {
            background: var(--teacher-gradient);
            color: white;
            border: none;
        }

        .icon-box {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-white bg-white mb-4 py-3">
        <div class="container">
            <a class="navbar-brand text-primary fw-800 fs-4" href="#">LMS-PRO</a>
            <div class="ms-auto d-flex align-items-center">
                <div class="me-3 text-end d-none d-md-block">
                    <span class="small d-block fw-bold"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <span class="text-success small fw-medium">Instructor Access</span>
                </div>
                <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row mb-5 align-items-center">
            <div class="col-md-7">
                <h1 class="fw-bold">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>! 👋</h1>
                <p class="text-muted">You are currently managing <strong><?php echo $course_count; ?></strong> active courses.</p>
            </div>
            <div class="col-md-5 text-md-end">
                <a href="teacher_create_course.php" class="btn btn-teacher btn-lg rounded-pill px-4 shadow">
                    <i data-lucide="plus-circle" class="me-2"></i>Create New Course
                </a>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card card p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted small fw-bold mb-1">COURSES</p>
                            <h2 class="fw-bold mb-0"><?php echo $course_count; ?></h2>
                        </div>
                        <div class="icon-box bg-primary bg-opacity-10 text-primary"><i data-lucide="book-open"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card card p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted small fw-bold mb-1">STUDENTS</p>
                            <h2 class="fw-bold mb-0"><?php echo $student_count; ?></h2>
                        </div>
                        <div class="icon-box bg-success bg-opacity-10 text-success"><i data-lucide="users"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card card p-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted small fw-bold mb-1">TEACHER ID</p>
                            <h2 class="fw-bold mb-0">#<?php echo $teacher_id; ?></h2>
                        </div>
                        <div class="icon-box bg-warning bg-opacity-10 text-warning"><i data-lucide="award"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="fw-bold mb-4">My Course Catalog</h4>

        <div class="row g-4">
            <?php
            // Only run the loop if $courses_result is a valid object and has rows
            if ($courses_result && $courses_result->num_rows > 0):
                while ($course = $courses_result->fetch_assoc()):
            ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="course-card card h-100 shadow-sm">
                            <div class="p-4">
                                <span class="badge bg-light text-primary mb-3 px-3 py-2"><?php echo htmlspecialchars($course['category'] ?? 'General'); ?></span>
                                <h5 class="fw-bold"><?php echo htmlspecialchars($course['title']); ?></h5>
                                <p class="text-muted small"><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></p>
                                <hr class="my-3 opacity-50">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small"><i data-lucide="calendar" class="icon-sm me-1"></i><?php echo date('M d', strtotime($course['created_at'])); ?></span>
                                    <a href="manage_course.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Manage</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                endwhile;
            else:
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-2 border-dashed bg-transparent d-flex align-items-center justify-content-center p-5 text-center" style="border-style: dashed !important; border-color: #cbd5e1 !important;">
                        <div class="text-muted">
                            <i data-lucide="search" class="mb-3" style="width: 48px; height: 48px;"></i>
                            <h6 class="fw-bold">No courses found</h6>
                            <p class="small">Start by creating your first course.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="col-md-6 col-lg-4">
                <a href="teacher_create_course.php" class="text-decoration-none h-100">
                    <div class="card h-100 border-2 border-dashed bg-transparent d-flex align-items-center justify-content-center p-5 text-center" style="border-style: dashed !important; border-color: #cbd5e1 !important;">
                        <div class="text-muted">
                            <i data-lucide="plus-circle" class="mb-3" style="width: 40px; height: 40px;"></i>
                            <h6 class="fw-bold">Add New Course</h6>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>