<?php
require_once 'User.php';
$user = new User();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - Transform Your Skills</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i data-lucide="book-open" class="text-primary me-2"></i>
                <span class="fw-bold tracking-tight">LMS-PRO</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if ($user->isLoggedIn()): ?>
                        <li class="nav-item"><a class="nav-link mx-2" href="dashboard.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link mx-2" href="courses.php">My Courses</a></li>
                        <li class="nav-item ms-lg-3">
                            <a class="btn btn-outline-danger btn-sm rounded-pill px-4" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link mx-2" href="login.php">Login</a></li>
                        <li class="nav-item ms-lg-3">
                            <a class="btn btn-primary rounded-pill px-4" href="register.php">Join for Free</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <header class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 py-5">
                    <span class="badge bg-soft-primary text-primary mb-3 px-3 py-2 rounded-pill">Start Learning Today</span>
                    <h1 class="display-4 fw-bold mb-4">Master New Skills With <span class="text-gradient">Expert Guidance</span></h1>
                    <p class="lead text-secondary mb-5">Your gateway to a premium online learning experience. Join 10,000+ students tracking their progress and achieving career goals.</p>

                    <div class="d-flex gap-3">
                        <?php if (!$user->isLoggedIn()): ?>
                            <a class="btn btn-primary btn-lg rounded-3 shadow-lg px-5" href="register.php">Get Started</a>
                            <a class="btn btn-light btn-lg rounded-3 px-4" href="#features">Learn More</a>
                        <?php else: ?>
                            <a class="btn btn-primary btn-lg rounded-3 shadow-lg px-5" href="courses.php">Go to Courses</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <img src="https://illustrations.popsy.co/amber/student-going-to-school.svg" alt="Education" class="img-fluid floating">
                </div>
            </div>
        </div>
    </header>

    <section id="features" class="py-5 bg-white">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Why Choose Our Platform?</h2>
                <p class="text-muted">Designed for the modern learner who seeks flexibility and quality.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card p-4 rounded-4 h-100">
                        <div class="icon-box mb-4 bg-soft-blue">
                            <i data-lucide="layers" class="text-primary"></i>
                        </div>
                        <h5 class="fw-bold">Diverse Courses</h5>
                        <p class="text-secondary">Access a wide range of specialized subjects taught by industry veterans and experts.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card p-4 rounded-4 h-100">
                        <div class="icon-box mb-4 bg-soft-purple">
                            <i data-lucide="clock" class="text-purple"></i>
                        </div>
                        <h5 class="fw-bold">Flexible Learning</h5>
                        <p class="text-secondary">Learn on your schedule. No deadlines, no pressure—just pure knowledge growth.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card p-4 rounded-4 h-100">
                        <div class="icon-box mb-4 bg-soft-green">
                            <i data-lucide="trending-up" class="text-success"></i>
                        </div>
                        <h5 class="fw-bold">Track Progress</h5>
                        <p class="text-secondary">Visualize your achievements with our interactive dashboard and analytics.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize Lucide Icons
        lucide.createIcons();
    </script>
</body>

</html>