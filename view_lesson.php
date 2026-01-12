<?php
require_once 'User.php';
require_once 'config.php';
$user = new User();

// 1. Session Protection
if (!$user->isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$lesson_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

// 2. Fetch Lesson Data and Module/Course Info
$sql = "SELECT l.*, m.course_id, m.title as module_title 
        FROM lessons l 
        JOIN modules m ON l.module_id = m.id 
        WHERE l.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$lesson = $stmt->get_result()->fetch_assoc();

if (!$lesson) {
    die("Lesson not found.");
}

$course_id = $lesson['course_id'];

// 3. Security: Check if student is enrolled
$enroll_check = $conn->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
$enroll_check->bind_param("ii", $user_id, $course_id);
$enroll_check->execute();
if ($enroll_check->get_result()->num_rows === 0) {
    die("You must be enrolled to view this content.");
}

// 4. Progress Logic: Check if current lesson is completed
$comp_stmt = $conn->prepare("SELECT id FROM lesson_completions WHERE user_id = ? AND lesson_id = ?");
$comp_stmt->bind_param("ii", $user_id, $lesson_id);
$comp_stmt->execute();
$is_current_completed = $comp_stmt->get_result()->num_rows > 0;

// 5. Fetch Sidebar Navigation & Find Next Lesson
$sidebar_modules = [];
$next_lesson_id = null;
$found_current = false;

$mod_res = $conn->query("SELECT * FROM modules WHERE course_id = $course_id ORDER BY order_number ASC");
while ($mod = $mod_res->fetch_assoc()) {
    $les_res = $conn->query("SELECT id, title FROM lessons WHERE module_id = {$mod['id']} ORDER BY order_number ASC");
    $lessons = [];
    while ($les = $les_res->fetch_assoc()) {
        // Check completion for each lesson in sidebar
        $check = $conn->query("SELECT id FROM lesson_completions WHERE user_id = $user_id AND lesson_id = {$les['id']}");
        $les['is_done'] = ($check->num_rows > 0);

        // Find next lesson logic
        if ($found_current && $next_lesson_id === null) {
            $next_lesson_id = $les['id'];
        }
        if ($les['id'] == $lesson_id) {
            $found_current = true;
        }

        $lessons[] = $les;
    }
    $mod['lessons'] = $lessons;
    $sidebar_modules[] = $mod;
}

// Helper: YouTube Embed Converter
function getEmbedUrl($url)
{
    if (strpos($url, 'youtube.com/watch?v=') !== false) {
        return str_replace('watch?v=', 'embed/', $url);
    } elseif (strpos($url, 'youtu.be/') !== false) {
        return str_replace('youtu.be/', 'youtube.com/embed/', $url);
    }
    return $url;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($lesson['title']); ?> | LMS-PRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            background: #f8fafc;
            font-family: 'Inter', sans-serif;
        }

        .sidebar {
            height: 100vh;
            overflow-y: auto;
            background: white;
            border-right: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
        }

        .lesson-content {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .active-lesson {
            background: #eff6ff;
            color: #2563eb !important;
            font-weight: 600;
            border-right: 4px solid #2563eb;
        }

        .nav-link-custom {
            color: #64748b;
            padding: 10px 20px;
            text-decoration: none;
            display: block;
            font-size: 0.9rem;
            transition: 0.2s;
            border-bottom: 1px solid #f1f5f9;
        }

        .nav-link-custom:hover {
            background: #f1f5f9;
        }

        .lesson-body img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .lesson-body iframe {
            width: 100%;
            border-radius: 12px;
            margin: 20px 0;
            height: 450px;
        }

        .completion-status {
            font-size: 0.8rem;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar p-0 d-none d-md-block">
                <div class="p-4 border-bottom bg-light">
                    <a href="course_details.php?id=<?php echo $course_id; ?>" class="text-decoration-none text-dark fw-bold">
                        <i data-lucide="arrow-left" class="me-2" style="width:16px"></i> Back to Dashboard
                    </a>
                </div>
                <div class="mt-2">
                    <?php foreach ($sidebar_modules as $mod): ?>
                        <div class="px-3 py-3 small fw-bold text-uppercase text-muted bg-light border-bottom" style="letter-spacing: 1px;">
                            <?php echo htmlspecialchars($mod['title']); ?>
                        </div>
                        <?php foreach ($mod['lessons'] as $les): ?>
                            <a href="view_lesson.php?id=<?php echo $les['id']; ?>"
                                class="nav-link-custom d-flex justify-content-between align-items-center <?php echo ($les['id'] == $lesson_id) ? 'active-lesson' : ''; ?>">
                                <span>
                                    <i data-lucide="<?php echo $les['is_done'] ? 'check-circle' : 'play-circle'; ?>"
                                        class="me-2 <?php echo $les['is_done'] ? 'text-success' : ''; ?>" style="width:16px"></i>
                                    <?php echo htmlspecialchars($les['title']); ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-5 py-5">
                <div style="max-width: 900px; margin: 0 auto;">

                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item text-muted"><?php echo htmlspecialchars($lesson['module_title']); ?></li>
                            <li class="breadcrumb-item active fw-bold text-primary"><?php echo htmlspecialchars($lesson['title']); ?></li>
                        </ol>
                    </nav>

                    <div class="lesson-content">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h1 class="fw-bold m-0"><?php echo htmlspecialchars($lesson['title']); ?></h1>
                            <?php if ($is_current_completed): ?>
                                <span class="badge bg-success rounded-pill px-3 py-2">
                                    <i data-lucide="check-check" class="me-1" style="width:14px"></i> Completed
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($lesson['video_url'])): ?>
                            <div class="ratio ratio-16x9 mb-5 shadow-sm rounded-3">
                                <iframe src="<?php echo getEmbedUrl($lesson['video_url']); ?>" allowfullscreen></iframe>
                            </div>
                        <?php endif; ?>

                        <div class="lesson-body lh-lg mb-5" style="font-size: 1.1rem; color: #334155;">
                            <?php echo $lesson['content']; ?>
                        </div>

                        <hr class="my-5">

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                <i data-lucide="calendar" class="me-1" style="width:14px"></i> Last Updated: <?php echo date('M d, Y', strtotime($lesson['created_at'])); ?>
                            </div>

                            <div class="d-flex gap-3">
                                <?php if (!$is_current_completed): ?>
                                    <form action="complete_lesson.php" method="POST">
                                        <input type="hidden" name="lesson_id" value="<?php echo $lesson_id; ?>">
                                        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                                        <input type="hidden" name="next_id" value="<?php echo $next_lesson_id; ?>">
                                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">
                                            Mark as Completed
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($next_lesson_id): ?>
                                    <a href="view_lesson.php?id=<?php echo $next_lesson_id; ?>" class="btn btn-primary rounded-pill px-4 fw-bold">
                                        Next Lesson <i data-lucide="chevron-right" class="ms-1" style="width:16px"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="course_details.php?id=<?php echo $course_id; ?>" class="btn btn-outline-primary rounded-pill px-4 fw-bold">
                                        Finish Course
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>