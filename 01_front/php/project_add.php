<?php
require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$dbh = db_connect();

$active_stmt = $dbh->prepare("
    UPDATE users
    SET last_active_at = NOW()
    WHERE id = ?
");

$active_stmt->execute([
    $_SESSION['user_id']
]);

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $github_url = trim($_POST["github_url"] ?? "");
    $demo_url = trim($_POST["demo_url"] ?? "");

    if ($title === "") {
        $message = "タイトルを入力してください";
    } else {

        $image_path = "";

        if (!empty($_FILES["project_image"]["name"])) {

            $upload_dir = "uploads/projects/";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_name = time() . "_" . basename($_FILES["project_image"]["name"]);
            $image_path = $upload_dir . $file_name;

            move_uploaded_file(
                $_FILES["project_image"]["tmp_name"],
                $image_path
            );
        }

        $stmt = $dbh->prepare("
            INSERT INTO projects
            (
                user_id,
                title,
                description,
                image_path,
                github_url,
                demo_url
            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?
            )
        ");

        $stmt->execute([
            $_SESSION['user_id'],
            $title,
            $description,
            $image_path,
            $github_url,
            $demo_url
        ]);

        header("Location: profile.php");
        exit;
    }
}

$notice_stmt = $dbh->prepare("
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = ?
    AND is_read = 0
");

$notice_stmt->execute([
    $_SESSION['user_id']
]);

$notice_count = $notice_stmt->fetch()['total'];

$chat_stmt = $dbh->prepare("
    SELECT COUNT(*) AS total
    FROM direct_messages
    WHERE receiver_id = ?
    AND is_read = 0
");

$chat_stmt->execute([
    $_SESSION['user_id']
]);

$chat_count = $chat_stmt->fetch()['total'];
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>Add Project</title>

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="bg-light aura-app">
    <?php $currentPage = 'post'; ?>
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<div class="container py-4 aura-content" style="max-width:700px;">

    <a href="profile.php" class="text-decoration-none text-muted">
        ← 戻る
    </a>

    <div class="card border-0 shadow-sm rounded-4 mt-3">

        <div class="card-body p-4">

            <h3 class="fw-bold mb-4">
                <i class="bi bi-folder-plus"></i>
                Add Project
            </h3>

            <?php if (!empty($message)): ?>
                <div class="alert alert-danger rounded-4">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form action="project_add.php" method="POST" enctype="multipart/form-data">

                <div class="mb-3">
                    <label class="form-label fw-bold">
                        Project Title
                    </label>

                    <input
                        type="text"
                        name="title"
                        class="form-control rounded-3"
                        placeholder="AURA Study">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">
                        Description
                    </label>

                    <textarea
                        name="description"
                        class="form-control rounded-3"
                        rows="5"
                        placeholder="Project description"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">
                        Screenshot
                    </label>

                    <input
                        type="file"
                        name="project_image"
                        class="form-control rounded-3">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">
                        GitHub URL
                    </label>

                    <input
                        type="text"
                        name="github_url"
                        class="form-control rounded-3"
                        placeholder="https://github.com/...">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">
                        Demo URL
                    </label>

                    <input
                        type="text"
                        name="demo_url"
                        class="form-control rounded-3"
                        placeholder="https://...">
                </div>

                <button
                    type="submit"
                    class="btn btn-primary w-100 rounded-pill py-3 fw-bold">
                    <i class="bi bi-save"></i>
                    Save Project
                </button>

            </form>

        </div>

    </div>

</div>

</body>
</html>