<?php
require_once 'User.php';
require_once 'config.php';
$user = new User();

if (!$user->isLoggedIn() || !$user->hasRole('teacher')) exit;

$module_id = isset($_GET['module_id']) ? intval($_GET['module_id']) : 0;
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = htmlspecialchars($_POST['title']);
    // We do NOT use htmlspecialchars here so the HTML tags from the editor are saved
    $content = $_POST['content'];
    $video = htmlspecialchars($_POST['video_url']);

    $stmt = $conn->prepare("INSERT INTO lessons (module_id, title, content, video_url) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $module_id, $title, $content, $video);

    if ($stmt->execute()) {
        header("Location: course_content.php?course_id=$course_id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Lesson | LMS-PRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/kqnfchesxitplgjet2oxka4jdnfhm28d9eba5p8gvpitjp8b/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://cdn.jsdelivr.net/npm/@languagetool-org/tinymce-6-languagetool-plugin@1.0.12/dist/index.min.js"></script>
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="card p-5 border-0 shadow-sm">
            <h2 class="fw-bold mb-4">Create Lesson Plan</h2>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Lesson Title</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Setting up XAMPP" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Video URL (optional)</label>
                    <input type="url" name="video_url" class="form-control" placeholder="YouTube Link">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Lesson Content</label>
                    <textarea id="lessonEditor" name="content" class="form-control" rows="15"></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg px-5">Publish Lesson</button>
                <a href="course_content.php?course_id=<?php echo $course_id; ?>" class="btn btn-link text-muted">Cancel</a>
            </form>
        </div>
    </div>

    <script>
        tinymce.init({
            selector: '#lessonEditor',
            browser_spellcheck: true,
            contextmenu: false,

            // 1. Add 'languagetool' to plugins
            plugins: 'lists link image table code help wordcount languagetool',

            // 2. Add 'languagetool' to the toolbar
            toolbar: 'undo redo | blocks | fontfamily fontsize | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | languagetool | removeformat | help',

            // 3. Configure LanguageTool
            languagetool: {
                serverUrl: 'https://api.languagetool.org/v2/check',
                language: 'en-US'
            },

            font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',
            font_family_formats: 'Arial=arial,helvetica,sans-serif; Courier New=courier new,courier; Georgia=georgia,palatino; Tahoma=tahoma,arial,helvetica,sans-serif; Times New Roman=times new roman,times; Verdana=verdana,geneva',
            menubar: false,
            height: 400,
            branding: false,
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }'
        });
    </script>
</body>

</html>