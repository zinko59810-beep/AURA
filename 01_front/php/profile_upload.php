<?php

require_once(__DIR__ . '/../../02_backend/db/db_.php');
require_once __DIR__ . '/../../lang/language.php';



if (!isset($_SESSION['user_id'])) {
    header("Location: /AURA/login");
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

        if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $file_path)) {
            $message = "画像のアップロードに失敗しました。uploads/profile の権限を確認してください。";
        } else {

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

            // echo $file_path;
            header("Location: /AURA/profile");
            exit;
        }
    }

    $message = "画像を選択してください";
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8') ?>">

<head>
    <meta charset="UTF-8">

    <title><?= $lang['profile_image_upload_title'] ?></title>

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

    <?php $currentPage = 'profile'; ?>
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <div class="container mt-5 aura-content">

        <div class="card shadow-sm rounded-4">

            <div class="card-body">

                <!-- ==========================
                     Page Title
                =========================== -->
                <h2 class="mb-4">
                    <?= $lang['profile_image_upload_title'] ?>
                </h2>

                <!-- ==========================
                     Error Message
                =========================== -->
                <?php if (!empty($message)): ?>

                    <div class="alert alert-danger">
                        <?= htmlspecialchars($message) ?>
                    </div>

                <?php endif; ?>

                <!-- ==========================
                     Upload Form
                =========================== -->
                <form
                    action="/AURA/profile_upload"
                    method="POST"
                    enctype="multipart/form-data">

                    <div class="mb-3">

                        <label class="form-label">
                            <?= $lang['select_image'] ?>
                        </label>

                        <input
                            type="file"
                            name="profile_image"
                            class="form-control">

                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <?= $lang['upload'] ?>

                    </button>

                    <a
                        href="/AURA/profile"
                        class="btn btn-secondary">

                        <?= $lang['back'] ?>

                    </a>

                </form>

            </div>

        </div>

    </div>


    <script src="/AURA/pwa-register.js" defer></script>
</body>

</html>