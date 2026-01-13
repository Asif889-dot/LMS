<?php
require_once 'User.php';
require_once 'Enrollment.php';
require_once 'config.php';

$user = new User();
$enrollment = new Enrollment();

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_data = $_SESSION['user_name'] ?? 'Student';
$enrolled_courses = $enrollment->getEnrollmentsByUser($user_id);
$user_details = $user->getUserById($user_id);

// Filter only completed courses
$completed_courses = [];
foreach ($enrolled_courses as $course) {
    $progress = $enrollment->getCourseProgress($user_id, $course['id'], $conn);
    if ($progress == 100) {
        $course['completed_date'] = date("M d, Y"); // Example date, ideally fetch from DB
        $completed_courses[] = $course;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Certificates | Smart Choice Academy</title>
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

        .navbar {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.8rem 0;
        }

        /* Certificate Card Style */
        .cert-card {
            border: none;
            border-radius: 20px;
            background: white;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .cert-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.1);
            border-color: var(--sca-primary);
        }

        .cert-icon-bg {
            width: 60px;
            height: 60px;
            background: #f0fdf4;
            color: #16a34a;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .stamp-watermark {
            position: absolute;
            right: -10px;
            bottom: -10px;
            opacity: 0.05;
            transform: rotate(-15deg);
        }

        .btn-download {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-download:hover {
            background-color: var(--sca-primary);
            color: white;
            border-color: var(--sca-primary);
        }

        .empty-state {
            padding: 80px 20px;
            background: white;
            border-radius: 30px;
            border: 2px dashed #e2e8f0;
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
            <div class="ms-auto d-flex align-items-center">
                <a href="dashboard.php" class="btn btn-light btn-sm rounded-pill px-3 me-2">Back to Dashboard</a>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        <div class="row mb-5">
            <div class="col-md-8">
                <h2 class="fw-bold mb-1">My Certificates</h2>
                <p class="text-muted">High-quality professional certifications from Smart Choice Academy & Softzila.</p>
            </div>
        </div>

        <div class="row g-4">
            <?php if (empty($completed_courses)): ?>
                <div class="col-12">
                    <div class="empty-state text-center">
                        <div class="mb-4 text-muted">
                            <i data-lucide="award" size="64" style="stroke-width: 1px;"></i>
                        </div>
                        <h4 class="fw-bold">No Certificates Yet</h4>
                        <p class="text-muted mb-4">Complete 100% of a course to unlock your professional certificate.</p>
                        <a href="dashboard.php" class="btn btn-primary rounded-pill px-4">Continue Learning</a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($completed_courses as $course): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 cert-card p-4">
                            <i data-lucide="award" class="stamp-watermark" size="120"></i>

                            <div class="cert-icon-bg">
                                <i data-lucide="file-check-2" size="32"></i>
                            </div>

                            <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($course['title']); ?></h5>
                            <p class="small text-muted mb-4">Completed on <?php echo $course['completed_date']; ?></p>

                            <div class="mt-auto pt-3 border-top">
                                <div class="d-grid gap-2">
                                    <a href="generate_pdf.php?course_id=<?php echo $course['id']; ?>" class="btn btn-download rounded-pill py-2 d-flex align-items-center justify-content-center">
                                        <i data-lucide="download" class="me-2" style="width:16px"></i> Download PDF
                                    </a>
                                    <button class="btn btn-link btn-sm text-decoration-none text-muted" onclick="alert('Verification ID: SCA-<?php echo strtoupper(substr(md5($course['id'] . $user_id), 0, 8)); ?>')">
                                        Verify Certificate
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="mt-5 p-4 rounded-4 bg-primary text-white shadow-lg">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="fw-bold mb-1">Boost Your LinkedIn Profile!</h5>
                    <p class="mb-0 opacity-75 small">Did you know? Adding your certificates to LinkedIn increases profile views by up to 6x.</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <button class="btn btn-light rounded-pill px-4 fw-bold">Share Progress</button>
                </div>
            </div>
        </div>
    </main>

    <footer class="mt-5 py-4 text-center text-muted border-top">
        <p class="small mb-0">© 2026 Smart Choice Academy. All certificates are verified and protected by Softzila.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>