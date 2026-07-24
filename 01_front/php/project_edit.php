<?php
require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$dbh = db_connect();

$project_id = $_GET['id'] ?? 0;
$message = "";

$stmt = $dbh->prepare("
    SELECT *
    FROM projects
    WHERE id = ?
    AND user_id = ?
");

$stmt->execute([
    $project_id,
    $_SESSION['user_id']
]);

$project = $stmt->fetch();

if (!$project) {
    echo "Project Not Found";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $github_url = trim($_POST["github_url"] ?? "");
    $demo_url = trim($_POST["demo_url"] ?? "");

    if ($title === "") {
        $message = "タイトルを入力してください";
    } else {

        $image_path = $project['image_path'];

        if (!empty($_FILES["project_image"]["name"])) {

            $upload_dir = "uploads/projects/";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if (!empty($project['image_path']) && file_exists($project['image_path'])) {
                unlink($project['image_path']);
            }

            $file_name = time() . "_" . basename($_FILES["project_image"]["name"]);
            $image_path = $upload_dir . $file_name;

            move_uploaded_file(
                $_FILES["project_image"]["tmp_name"],
                $image_path
            );
        }

        $update_stmt = $dbh->prepare("
            UPDATE projects
            SET
                title = ?,
                description = ?,
                image_path = ?,
                github_url = ?,
                demo_url = ?
            WHERE id = ?
            AND user_id = ?
        ");

        $update_stmt->execute([
            $title,
            $description,
            $image_path,
            $github_url,
            $demo_url,
            $project_id,
            $_SESSION['user_id']
        ]);

        header("Location: profile.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Edit Project</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/AURA/01_front/css/style.css">
</head>

<body class="bg-light">

<div class="container py-5" style="max-width:700px;">

    <a href="/AURA/profile" class="text-decoration-none text-muted">
        ← 戻る
    </a>

    <div class="card border-0 shadow-sm rounded-4 mt-3">
        <div class="card-body p-4">

            <h3 class="fw-bold mb-4">
                <i class="bi bi-pencil"></i>
                Edit Project
            </h3>

            <?php if (!empty($message)): ?>
                <div class="alert alert-danger rounded-4">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="mb-3">
                    <label class="form-label fw-bold">Project Title</label>
                    <input
                        type="text"
                        name="title"
                        class="form-control rounded-3"
                        value="<?= htmlspecialchars($project['title']) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Description</label>
                    <textarea
                        name="description"
                        class="form-control rounded-3"
                        rows="5"><?= htmlspecialchars($project['description']) ?></textarea>
                </div>

                <?php if (!empty($project['image_path'])): ?>
                    <div class="mb-3">
                        <img
                            src="<?= htmlspecialchars($project['image_path']) ?>"
                            class="img-fluid rounded-4">
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label fw-bold">New Screenshot</label>
                    <input
                        type="file"
                        name="project_image"
                        class="form-control rounded-3">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">GitHub URL</label>
                    <input
                        type="text"
                        name="github_url"
                        class="form-control rounded-3"
                        value="<?= htmlspecialchars($project['github_url']) ?>">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Demo URL</label>
                    <input
                        type="text"
                        name="demo_url"
                        class="form-control rounded-3"
                        value="<?= htmlspecialchars($project['demo_url']) ?>">
                </div>

                <button
                    type="submit"
                    class="btn btn-primary w-100 rounded-pill py-3 fw-bold">
                    <i class="bi bi-save"></i>
                    Update Project
                </button>

            </form>

        </div>
    </div>

</div>

</body>
</html>