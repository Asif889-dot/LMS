<?php
require_once 'User.php';
require_once 'Enrollment.php';
require_once 'config.php'; // Database connection

$user = new User();
$enrollment = new Enrollment();

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$enrolled_courses = $enrollment->getEnrollmentsByUser($user_id);

// --- DYNAMIC TOTAL STATS ---
// Total completed courses (where progress is 100%)
$completed_count = 0;
foreach ($enrolled_courses as $index => $course) {
    $progress = $enrollment->getCourseProgress($user_id, $course['id'], $conn);
    $enrolled_courses[$index]['progress_percent'] = $progress;
    if ($progress == 100) $completed_count++;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard | LMS-PRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .icon-box {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }

        .bg-soft-primary {
            background: #e0f2fe;
        }

        .bg-soft-green {
            background: #dcfce7;
        }

        .bg-soft-purple {
            background: #f3e8ff;
        }

        .avatar-sm {
            width: 32px;
            height: 32px;
            background: #2563eb;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 0.8rem;
        }
    </style>
</head>

<body class="bg-light">
    <main class="container py-5">
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card p-4 rounded-4 shadow-sm border-0">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted small fw-bold mb-1">ENROLLED</p>
                            <h2 class="fw-bold mb-0"><?php echo count($enrolled_courses); ?></h2>
                        </div>
                        <div class="icon-box bg-soft-primary"><i data-lucide="book" class="text-primary"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 rounded-4 shadow-sm border-0">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted small fw-bold mb-1">COMPLETED</p>
                            <h2 class="fw-bold mb-0"><?php echo $completed_count; ?></h2>
                        </div>
                        <div class="icon-box bg-soft-green"><i data-lucide="check-circle" class="text-success"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 rounded-4 shadow-sm border-0">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted small fw-bold mb-1">LEARNING TIME</p>
                            <h2 class="fw-bold mb-0">Active</h2>
                        </div>
                        <div class="icon-box bg-soft-purple"><i data-lucide="clock" class="text-secondary"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="fw-bold mb-4">Continue Learning</h3>

        <div class="row g-4">
            <?php foreach ($enrolled_courses as $course): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($course['title']); ?></h5>

                            <div class="mb-4 mt-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small text-muted fw-semibold">Progress</span>
                                    <span class="small text-muted fw-semibold"><?php echo $course['progress_percent']; ?>%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar"
                                        style="width: <?php echo $course['progress_percent']; ?>%"></div>
                                </div>
                            </div>

                            <a href="course_details.php?id=<?php echo $course['id']; ?>" class="btn btn-primary w-100 rounded-pill">
                                <?php echo ($course['progress_percent'] > 0) ? 'Resume Course' : 'Start Learning'; ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>