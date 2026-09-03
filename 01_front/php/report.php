<?php

require_once(__DIR__ . '/../../02_backend/db/db_.php');
require_once __DIR__ . '/../../lang/language.php';



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

$answer_id =
    filter_input(INPUT_GET, 'answer_id', FILTER_VALIDATE_INT);

$answer_id = $answer_id ?: 0;

$message = "";
$is_success = false;
$can_report = false;

$target_stmt = $dbh->prepare("
    SELECT
        id,
        user_id,
        question_id
    FROM answers
    WHERE id = ?
");

$target_stmt->execute([$answer_id]);

$target_answer = $target_stmt->fetch();

if (!$target_answer) {
    http_response_code(404);
    exit("Answer Not Found");
}

$reported_user_id =
    (int)$target_answer['user_id'];

if (
    $reported_user_id ===
    (int)$_SESSION['user_id']
) {
    $message = "自分の回答は通報できません。";
} else {
    $can_report = true;
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    $can_report
) {

    $reason =
        trim($_POST['reason'] ?? "");

    if ($reason === "") {

        $message =
            "通報理由を入力してください。";

    } elseif (mb_strlen($reason) > 255) {

        $message =
            "通報理由は255文字以内で入力してください。";

    } else {

        $check_stmt = $dbh->prepare("
            SELECT id
            FROM reports
            WHERE reporter_id = ?
              AND answer_id = ?
              AND status = 'pending'
            LIMIT 1
        ");

        $check_stmt->execute([
            $_SESSION['user_id'],
            $answer_id
        ]);

        if ($check_stmt->fetch()) {

            $message =
                "この回答はすでに通報済みです。";

        } else {

            $stmt = $dbh->prepare("
                INSERT INTO reports
                (
                    reporter_id,
                    reported_user_id,
                    answer_id,
                    reason,
                    status
                )
                VALUES
                (
                    ?, ?, ?, ?, 'pending'
                )
            ");

            $stmt->execute([
                $_SESSION['user_id'],
                $reported_user_id,
                $answer_id,
                $reason
            ]);

            $message = "通報しました。";
            $is_success = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8') ?>">

<head>
    <meta charset="UTF-8">

    <title><?= $lang['report_title'] ?></title>

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

<body class="aura-app">

    <?php $currentPage = 'home'; ?>
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <div class="container py-4 aura-content" style="max-width:500px;">

        <!-- ==========================
             Back Button
        =========================== -->
        <a
            href="javascript:history.back()"
            class="text-decoration-none text-muted">

            ← <?= $lang['back'] ?>

        </a>

        <div class="card border-0 shadow-sm rounded-4 mt-3">

            <div class="card-body p-4">

                <!-- ==========================
                     Page Title
                =========================== -->
                <h4 class="fw-bold mb-4">

                    <i class="bi bi-flag"></i>

                    <?= $lang['report_title'] ?>

                </h4>

                <!-- ==========================
                     Success Message
                =========================== -->
                <?php if (!empty($message)): ?>

                    <div class="alert <?= $is_success ? 'alert-success' : 'alert-danger' ?> rounded-4">

                        <?= htmlspecialchars(
                            $message,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </div>

                <?php endif; ?>

                <!-- ==========================
                     Report Form
                =========================== -->
                <?php if ($can_report): ?><form method="POST">

                    <div class="mb-3">

                        <label class="form-label fw-bold">

                            <?= $lang['report_reason'] ?>

                        </label>

                        <textarea
                            name="reason"
                            class="form-control rounded-3"
                            rows="5"
                            placeholder="<?= $lang['report_reason_placeholder'] ?>"
                            required></textarea>

                    </div>

                    <button
                        type="submit"
                        class="btn btn-danger w-100 rounded-pill py-3">

                        <?= $lang['submit_report'] ?>

                    </button>

                </form><?php endif; ?>

            </div>

        </div>

    </div>


    <script src="/AURA/pwa-register.js" defer></script>
</body>

</html>