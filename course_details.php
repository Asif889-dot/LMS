<?php
require_once 'Course.php';
require_once 'Enrollment.php';
require_once 'User.php';
require_once 'config.php'; // Ensure database connection is available

$course = new Course();
$enrollment = new Enrollment();
$user = new User();

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: courses.php');
    exit;
}

$course_id = (int)$_GET['id'];
$course_data = $course->getCourseById($course_id);

if (!$course_data) {
    header('Location: courses.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$is_enrolled = $enrollment->isEnrolled($user_id, $course_id);

// --- DYNAMIC DATA FETCHING ---
$modules = [];
if ($is_enrolled) {
    // Fetch all modules for this course
    $mod_stmt = $conn->prepare("SELECT * FROM modules WHERE course_id = ? ORDER BY order_number ASC");
    $mod_stmt->bind_param("i", $course_id);
    $mod_stmt->execute();
    $mod_res = $mod_stmt->get_result();

    while ($module = $mod_res->fetch_assoc()) {
        // For each module, fetch its lessons
        $les_stmt = $conn->prepare("SELECT id, title FROM lessons WHERE module_id = ? ORDER BY order_number ASC");
        $les_stmt->bind_param("i", $module['id']);
        $les_stmt->execute();
        $module['lessons'] = $les_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $modules[] = $module;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course_data['title']); ?> | LMS-PRO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .icon-sm {
            width: 18px;
            height: 18px;
        }

        .content-card {
            background: #fff;
            border-radius: 1rem;
            border: 1px solid #eef2f7;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
        }

        .accordion-button:not(.collapsed) {
            background-color: #f8fbff;
            color: #0d6efd;
        }

        .lesson-link {
            text-decoration: none;
            color: #475569;
            padding: 8px 12px;
            display: block;
            border-radius: 8px;
            transition: 0.2s;
        }

        .lesson-link:hover {
            background: #f1f5f9;
            color: #000;
        }
    </style>
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i data-lucide="book-open" class="text-primary me-2"></i>
                <span class="fw-bold">LMS-PRO</span>
            </a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="courses.php">Explore</a></li>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-outline-danger btn-sm rounded-pill px-3" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="bg-white border-bottom py-5 mb-5">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="courses.php">Courses</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($course_data['title']); ?></li>
                </ol>
            </nav>
            <h1 class="fw-bold text-dark mb-3"><?php echo htmlspecialchars($course_data['title']); ?></h1>
            <div class="d-flex flex-wrap gap-4 text-secondary">
                <div class="d-flex align-items-center"><i data-lucide="user" class="icon-sm me-2"></i> Instructor: <strong><?php echo htmlspecialchars($course_data['instructor'] ?? 'Staff'); ?></strong></div>
                <div class="d-flex align-items-center"><i data-lucide="book" class="icon-sm me-2"></i> <?php echo count($modules); ?> Modules</div>
            </div>
        </div>
    </header>

    <main class="container mb-5">
        <div class="row g-4">
            <div class="col-lg-8">
                <section class="content-card p-4 mb-4">
                    <h4 class="fw-bold mb-4">Course Description</h4>
                    <p class="text-secondary lh-lg"><?php echo nl2br(htmlspecialchars($course_data['description'])); ?></p>
                </section>

                <?php if ($is_enrolled): ?>
                    <section class="content-card p-4 mb-4">
                        <h4 class="fw-bold mb-4">Curriculum</h4>
                        <div class="accordion accordion-flush" id="courseContent">
                            <?php if (empty($modules)): ?>
                                <p class="text-muted">No content has been added to this course yet.</p>
                            <?php else: ?>
                                <?php foreach ($modules as $index => $module): ?>
                                    <div class="accordion-item border rounded-3 mb-2">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button <?php echo $index !== 0 ? 'collapsed' : ''; ?> rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#m<?php echo $module['id']; ?>">
                                                <i data-lucide="folder" class="me-3 text-primary"></i>
                                                <span class="fw-semibold"><?php echo htmlspecialchars($module['title']); ?></span>
                                            </button>
                                        </h2>
                                        <div id="m<?php echo $module['id']; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" data-bs-parent="#courseContent">
                                            <div class="accordion-body p-2">
                                                <?php if (empty($module['lessons'])): ?>
                                                    <span class="text-muted ps-4 small">No lessons in this module.</span>
                                                <?php else: ?>
                                                    <?php foreach ($module['lessons'] as $lesson): ?>
                                                        <a href="view_lesson.php?id=<?php echo $lesson['id']; ?>" class="lesson-link d-flex align-items-center">
                                                            <i data-lucide="play-circle" class="icon-sm me-3 text-secondary"></i>
                                                            <?php echo htmlspecialchars($lesson['title']); ?>
                                                        </a>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>
                <?php else: ?>
                    <div class="p-5 text-center bg-white rounded-4 border">
                        <i data-lucide="lock" class="text-muted mb-3" style="width: 48px; height: 48px;"></i>
                        <h4 class="fw-bold">Course Content Locked</h4>
                        <p class="text-secondary mb-4">Enroll in this course to access modules and lessons.</p>
                        <button class="btn btn-primary btn-lg rounded-pill px-5 enroll-course" data-course-id="<?php echo $course_id; ?>">Enroll Now</button>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="sticky-top" style="top: 100px;">
                    <div class="content-card p-4">
                        <h5 class="fw-bold mb-3">Course Actions</h5>
                        <?php if ($is_enrolled): ?>
                            <a href="view_lesson.php?id=<?php echo $modules[0]['lessons'][0]['id'] ?? '#'; ?>" class="btn btn-primary w-100 rounded-pill mb-2 <?php echo empty($modules[0]['lessons']) ? 'disabled' : ''; ?>">Continue Learning</a>
                            <button class="btn btn-outline-danger w-100 rounded-pill drop-course" data-course-id="<?php echo $course_id; ?>">Drop Course</button>
                        <?php else: ?>
                            <button class="btn btn-primary btn-lg w-100 rounded-pill mb-3 enroll-course" data-course-id="<?php echo $course_id; ?>">Enroll Today</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>