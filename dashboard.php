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
$user_data = $_SESSION['user_name'] ?? 'Student';
$enrolled_courses = $enrollment->getEnrollmentsByUser($user_id);

// --- DYNAMIC TOTAL STATS ---
$completed_count = 0;
foreach ($enrolled_courses as $index => $course) {
    $progress = $enrollment->getCourseProgress($user_id, $course['id'], $conn);
    $enrolled_courses[$index]['progress_percent'] = $progress;
    if ($progress == 100) $completed_count++;
}

// Fetching user details for the dropdown profile picture
$user_details = $user->getUserById($user_id);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | Smart Choice Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --sca-primary: #2563eb;
            --sca-bg: #f8fafc;
        }

        body {
            background-color: var(--sca-bg);
            font-family: 'Inter', sans-serif;
            color: #1e293b;
        }

        /* Navbar & Dropdown Styles */
        .navbar {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.8rem 0;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 10px;
            margin-top: 10px;
        }

        .dropdown-item {
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: 0.2s;
        }

        .dropdown-item:hover {
            background-color: #f1f5f9;
            color: var(--sca-primary);
        }

        .dropdown-item.text-danger:hover {
            background-color: #fef2f2;
        }

        .student-badge {
            background-color: #e0f2fe;
            color: #0369a1;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 50px;
            text-transform: uppercase;
            border: 1px solid #bae6fd;
        }

        .profile-trigger {
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .profile-trigger:hover {
            opacity: 0.8;
        }

        .avatar-box {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            object-fit: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* Dashboard Content Styles */
        .stat-card {
            border: none;
            border-radius: 16px;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .icon-box {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }

        .bg-soft-primary {
            background: #eff6ff;
            color: #2563eb;
        }

        .bg-soft-success {
            background: #f0fdf4;
            color: #16a34a;
        }

        .bg-soft-warning {
            background: #fffbeb;
            color: #d97706;
        }

        .course-card {
            border: none;
            border-radius: 20px;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .footer-text {
            font-size: 0.85rem;
            color: #64748b;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light sticky-top mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary d-flex align-items-center" href="dashboard.php">
                <i data-lucide="graduation-cap" class="me-2"></i>
                <span>Smart Choice Academy</span>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link active fw-semibold text-primary px-3" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium px-3" href="courses.php">Explore</a></li>

                    <li class="nav-item dropdown ms-lg-3">
                        <div class="d-flex align-items-center profile-trigger" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="text-end me-3 d-none d-lg-block">
                                <span class="d-block fw-bold text-dark lh-1 small"><?php echo htmlspecialchars($user_data); ?></span>
                                <span class="student-badge">Student</span>
                            </div>
                            <?php if (isset($user_details['profile_picture']) && $user_details['profile_picture']): ?>
                                <img src="<?php echo htmlspecialchars($user_details['profile_picture']); ?>" alt="Profile" class="avatar-box shadow-sm border">
                            <?php else: ?>
                                <div class="bg-primary text-white avatar-box shadow-sm">
                                    <?php echo strtoupper(substr($user_data, 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="profileDropdown">
                            <li class="px-3 py-2 d-lg-none">
                                <span class="fw-bold d-block"><?php echo htmlspecialchars($user_data); ?></span>
                                <span class="text-muted small">Student Account</span>
                            </li>
                            <li>
                                <hr class="dropdown-divider d-lg-none">
                            </li>
                            <li><a class="dropdown-item d-flex align-items-center" href="profile_update.php">
                                    <i data-lucide="user-cog" class="me-2" style="width:18px"></i> Update Profile</a>
                            </li>
                            <li><a class="dropdown-item d-flex align-items-center" href="my_certificates.php">
                                    <i data-lucide="award" class="me-2" style="width:18px"></i> Certificates</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger d-flex align-items-center" href="logout.php">
                                    <i data-lucide="log-out" class="me-2" style="width:18px"></i> Logout</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-3">
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="fw-bold mb-1">My Workspace</h2>
                <p class="text-muted">You are currently logged in to the **Smart Choice Academy** portal.</p>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card stat-card p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-secondary small fw-bold mb-1">ENROLLED</p>
                            <h2 class="fw-bold mb-0"><?php echo count($enrolled_courses); ?></h2>
                        </div>
                        <div class="icon-box bg-soft-primary"><i data-lucide="book-open"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-secondary small fw-bold mb-1">COMPLETED</p>
                            <h2 class="fw-bold mb-0"><?php echo $completed_count; ?></h2>
                        </div>
                        <div class="icon-box bg-soft-success"><i data-lucide="check-circle"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-secondary small fw-bold mb-1">LOCATION</p>
                            <h2 class="fw-bold mb-0" style="font-size: 1.2rem;">Gujranwala Campus</h2>
                        </div>
                        <div class="icon-box bg-soft-warning"><i data-lucide="map-pin"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="fw-bold mb-4">Continue Learning</h4>
        <div class="row g-4">
            <?php if (empty($enrolled_courses)): ?>
                <div class="col-12 text-center py-5 bg-white rounded-4 shadow-sm border">
                    <i data-lucide="layout" size="48" class="text-muted mb-3"></i>
                    <h5>No active courses</h5>
                    <p class="text-muted">Ready to start? Explore our professional tech courses.</p>
                    <a href="courses.php" class="btn btn-primary rounded-pill px-4">Browse Catalog</a>
                </div>
            <?php else: ?>
                <?php foreach ($enrolled_courses as $course): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 course-card">
                            <div class="card-body p-4 d-flex flex-column">
                                <h5 class="fw-bold mb-4"><?php echo htmlspecialchars($course['title']); ?></h5>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between mb-2 small">
                                        <span class="text-muted">Course Progress</span>
                                        <span class="fw-bold text-primary"><?php echo $course['progress_percent']; ?>%</span>
                                    </div>
                                    <div class="progress mb-4" style="height: 7px;">
                                        <div class="progress-bar bg-primary rounded" role="progressbar" style="width: <?php echo $course['progress_percent']; ?>%"></div>
                                    </div>
                                    <a href="course_details.php?id=<?php echo $course['id']; ?>" class="btn btn-primary w-100 rounded-pill shadow-sm">
                                        <?php echo ($course['progress_percent'] > 0) ? 'Resume Learning' : 'Start Course'; ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <footer class="mt-5 pt-5 border-top pb-4 text-center">
            <p class="mb-1 fw-bold">Smart Choice Academy</p>
            <p class="footer-text mb-0">Main Sialkot Bypass, Gujranwala | 03041768210</p>
            <p class="footer-text">Project of <a href="https://softzila.com" class="text-primary text-decoration-none fw-semibold">Softzila.com</a></p>
        </footer>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>