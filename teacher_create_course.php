<?php
require_once 'User.php';
require_once 'config.php';
$user = new User();

if (!$user->isLoggedIn() || !$user->hasRole('teacher')) {
    header("Location: login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = htmlspecialchars(strip_tags(trim($_POST['title'])));
    $category = htmlspecialchars(strip_tags(trim($_POST['category'])));
    $description = htmlspecialchars(strip_tags(trim($_POST['description'])));

    if (!empty($title) && !empty($description)) {
        // The prepare() might fail if the table structure is wrong
        $stmt = $conn->prepare("INSERT INTO courses (teacher_id, title, category, description) VALUES (?, ?, ?, ?)");

        if ($stmt) {
            $stmt->bind_param("isss", $teacher_id, $title, $category, $description);
            if ($stmt->execute()) {
                $success = "Course created successfully!";
                header("refresh:2;url=teacher_dashboard.php");
            } else {
                $error = "Execution Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            // This catches the error that caused your crash
            $error = "Database Error: " . $conn->error;
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Course | LMS-PRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            background: #f8fafc;
            font-family: sans-serif;
        }

        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <a href="teacher_dashboard.php" class="text-decoration-none small mb-3 d-block">← Back to Dashboard</a>
                <div class="card p-4">
                    <h3 class="fw-bold mb-4">New Course</h3>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Category</label>
                            <select name="category" class="form-select">
                                <option value="Programming">Programming</option>
                                <option value="Design">Design</option>
                                <option value="Marketing">Marketing</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">Create Course</button>
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