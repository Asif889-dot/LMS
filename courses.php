<?php
require_once 'Course.php';
require_once 'Enrollment.php';
require_once 'User.php';

$course = new Course();
$enrollment = new Enrollment();
$user = new User();

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$all_courses = $course->getAllCourses();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Courses | LMS-PRO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="course.css">
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i data-lucide="book-open" class="text-primary me-2"></i>
                <span class="fw-bold tracking-tight">LMS-PRO</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="courses.php">Explore</a></li>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-outline-danger btn-sm rounded-pill px-3" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        <div class="row mb-5 align-items-center">
            <div class="col-lg-6">
                <h1 class="fw-bold mb-2">Explore <span class="text-primary">Courses</span></h1>
                <p class="text-secondary">Discover new skills from our expert-led library.</p>
            </div>
            <div class="col-lg-6">
                <div class="input-group bg-white shadow-sm rounded-pill overflow-hidden border">
                    <span class="input-group-text bg-white border-0 ps-4">
                        <i data-lucide="search" class="text-muted" style="width: 18px;"></i>
                    </span>
                    <input type="text" class="form-control border-0 py-3" id="courseSearch" placeholder="Search for courses, topics, or instructors...">
                </div>
            </div>
        </div>

        <div class="row g-4" id="courseGrid">
            <?php foreach ($all_courses as $c): ?>
                <div class="col-md-6 col-lg-4 course-item">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden course-card">
                        <div class="bg-soft-primary d-flex align-items-center justify-content-center" style="height: 160px;">
                            <i data-lucide="monitor" class="text-primary opacity-25" style="width: 64px; height: 64px;"></i>
                        </div>

                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-soft-purple text-purple px-3 py-2 rounded-pill small">Level: All</span>
                                <div class="text-warning d-flex align-items-center small fw-bold">
                                    <i data-lucide="star" class="me-1 fill-warning" style="width: 14px;"></i> 4.8
                                </div>
                            </div>

                            <h5 class="fw-bold mb-2 text-dark"><?php echo htmlspecialchars($c['title']); ?></h5>
                            <p class="text-secondary small mb-4">
                                <?php echo htmlspecialchars(substr($c['description'], 0, 110)) . '...'; ?>
                            </p>

                            <div class="d-flex align-items-center mb-4 p-2 bg-light rounded-3">
                                <div class="avatar-sm me-2 bg-primary text-white" style="width: 28px; height: 28px; font-size: 0.6rem;">
                                    <?php echo strtoupper(substr($c['instructor'], 0, 1)); ?>
                                </div>
                                <span class="small text-muted fw-semibold">Instructor: <?php echo htmlspecialchars($c['instructor']); ?></span>
                            </div>

                            <div class="d-flex gap-2 mt-auto">
                                <a href="course_details.php?id=<?php echo $c['id']; ?>" class="btn btn-light flex-grow-1 rounded-3 small fw-bold">
                                    Details
                                </a>
                                <?php if ($enrollment->isEnrolled($user_id, $c['id'])): ?>
                                    <button class="btn btn-soft-danger drop-course px-3" data-course-id="<?php echo $c['id']; ?>">
                                        <i data-lucide="minus-circle" class="icon-sm"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-primary enroll-course flex-grow-1 rounded-3 shadow-sm" data-course-id="<?php echo $c['id']; ?>">
                                        Enroll Now
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 1. Initialize Lucide Icons
        lucide.createIcons();

        // 2. Search Filter Logic
        document.getElementById('courseSearch').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let items = document.querySelectorAll('.course-item');

            items.forEach(item => {
                let text = item.textContent.toLowerCase();
                item.style.display = text.includes(filter) ? "" : "none";
            });
        });

        // 3. Event Delegation for Buttons
        // This catches clicks on buttons even if they contain icons
        document.addEventListener('click', function(e) {
            // Detect which button type was clicked
            const enrollBtn = e.target.closest('.enroll-course');
            const dropBtn = e.target.closest('.drop-course');

            if (enrollBtn) {
                const courseId = enrollBtn.getAttribute('data-course-id');
                processEnrollment(courseId, 'enroll');
            } else if (dropBtn) {
                const courseId = dropBtn.getAttribute('data-course-id');
                if (confirm('Are you sure you want to drop this course?')) {
                    processEnrollment(courseId, 'drop');
                }
            }
        });

        // 4. AJAX Process Logic
        function processEnrollment(courseId, action) {
            // Provide visual feedback by disabling the button
            const btn = document.querySelector(`[data-course-id="${courseId}"][class*="${action}"]`);
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            }

            const formData = new FormData();
            formData.append('course_id', courseId);
            formData.append('action', action);

            // Fetch matches the filename in your LMS folder
            fetch('enroll_process.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Refresh the page to update the UI buttons via PHP
                        location.reload();
                    } else {
                        alert(data.message || 'An error occurred');
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = (action === 'enroll') ? 'Enroll Now' : '<i data-lucide="minus-circle" class="icon-sm"></i>';
                            lucide.createIcons(); // Re-render icon if needed
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (btn) btn.disabled = false;
                });
        }
    </script>
</body>

</html>