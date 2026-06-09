<?php

require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!empty($_FILES['profile_image']['name'])) {

        $file_name = time() . "_" . $_FILES['profile_image']['name'];

        $upload_dir = "uploads/profile/";

        // Folder မရှိရင်ဆောက်
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_path = $upload_dir . $file_name;

        move_uploaded_file(
            $_FILES['profile_image']['tmp_name'],
            $file_path
        );

        $dbh = db_connect();

        $stmt = $dbh->prepare("
            UPDATE users
            SET profile_image = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $file_path,
            $_SESSION['user_id']
        ]);

        header("Location: profile.php");
        exit;
    }

    $message = "画像を選択してください";
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>Profile Image Upload</title>

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="../css/style.css">
</head>

<body class="bg-light aura-app">

<aside class="aura-sidebar">
    <a href="question_list.php" class="aura-brand">
        <span class="aura-brand-mark">A</span>
        <span>AURA Study</span>
    </a>

    <nav class="aura-menu">
        <a href="question_list.php" class="aura-menu-link">
            <i class="bi bi-house-door"></i>
            <span>Home</span>
        </a>
        <a href="question_post.php" class="aura-menu-link">
            <i class="bi bi-plus-square"></i>
            <span>Post</span>
        </a>
        <a href="notification.php" class="aura-menu-link">
            <i class="bi bi-bell"></i>
            <span>Notice</span>
        </a>
        <a href="ranking.php" class="aura-menu-link">
            <i class="bi bi-trophy"></i>
            <span>Ranking</span>
        </a>
        <a href="profile.php" class="aura-menu-link active">
            <i class="bi bi-person-circle"></i>
            <span>Profile</span>
        </a>
    </nav>

    <a href="logout.php" class="aura-logout">
        <i class="bi bi-box-arrow-right"></i>
        <span>Logout</span>
    </a>
</aside>


<div class="container mt-5 aura-content">

    <div class="card shadow-sm rounded-4">

        <div class="card-body">

            <h2 class="mb-4">
                プロフィール画像変更
            </h2>

            <?php if (!empty($message)): ?>

                <div class="alert alert-danger">
                    <?= htmlspecialchars($message) ?>
                </div>

            <?php endif; ?>

            <form
                action="profile_upload.php"
                method="POST"
                enctype="multipart/form-data">

                <div class="mb-3">

                    <label class="form-label">
                        画像を選択
                    </label>

                    <input
                        type="file"
                        name="profile_image"
                        class="form-control">

                </div>

                <button
                    type="submit"
                    class="btn btn-primary">

                    Upload

                </button>

                <a
                    href="profile.php"
                    class="btn btn-secondary">

                    戻る

                </a>

            </form>

        </div>

    </div>

</div>

</body>

</html>