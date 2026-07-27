<?php
require_once(__DIR__ . '/../../02_backend/db/db_.php');
require_once __DIR__ . '/../../lang/language.php';



if (!isset($_SESSION['user_id'])) {
    header("Location: /AURA/login");
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

        header("Location: /AURA/profile");
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
<html lang="<?= htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8') ?>">

<head>
    <meta charset="UTF-8">

    <title><?= $lang['add_project_title'] ?></title>

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet"
        href="/AURA/01_front/css/style.css">

    <!-- AURA PWA -->
    <link rel="manifest" href="/AURA/manifest.webmanifest">
    <meta name="theme-color" content="#2563eb">
    <meta name="application-name" content="AURA Study">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="icon" type="image/png" sizes="32x32" href="/AURA/01_front/assets/icons/favicon-32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/AURA/01_front/assets/icons/favicon-16.png">
    <link rel="apple-touch-icon" href="/AURA/01_front/assets/icons/apple-touch-icon.png">
</head>

<body class="bg-light aura-app">

    <?php $currentPage = 'post'; ?>
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <div class="container py-4 aura-content" style="max-width:700px;">

        <a
            href="/AURA/profile"
            class="text-decoration-none text-muted">
            ← <?= $lang['back'] ?>
        </a>

        <div class="card border-0 shadow-sm rounded-4 mt-3">

            <div class="card-body p-4">

                <!-- ==========================
                     Page Title
                =========================== -->
                <h3 class="fw-bold mb-4">
                    <i class="bi bi-folder-plus"></i>
                    <?= $lang['add_project_title'] ?>
                </h3>

                <?php if (!empty($message)): ?>

                    <div class="alert alert-danger rounded-4">
                        <?= htmlspecialchars($message) ?>
                    </div>

                <?php endif; ?>

                <!-- ==========================
                     Project Form
                =========================== -->
                <form
                    action="/AURA/project_add"
                    method="POST"
                    enctype="multipart/form-data">

                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            <?= $lang['project_title'] ?>
                        </label>

                        <input
                            type="text"
                            name="title"
                            class="form-control rounded-3"
                            placeholder="<?= $lang['project_title_placeholder'] ?>">

                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            <?= $lang['description'] ?>
                        </label>

                        <textarea
                            name="description"
                            class="form-control rounded-3"
                            rows="5"
                            placeholder="<?= $lang['project_description_placeholder'] ?>"></textarea>

                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            <?= $lang['screenshot'] ?>
                        </label>

                        <input
                            type="file"
                            name="project_image"
                            class="form-control rounded-3">

                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            <?= $lang['github_url'] ?>
                        </label>

                        <input
                            type="text"
                            name="github_url"
                            class="form-control rounded-3"
                            placeholder="https://github.com/...">

                    </div>

                    <div class="mb-4">

                        <label class="form-label fw-bold">
                            <?= $lang['demo_url'] ?>
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

                        <?= $lang['save_project'] ?>

                    </button>

                </form>

            </div>

        </div>

    </div>


    <script src="/AURA/pwa-register.js" defer></script>
</body>

</html>