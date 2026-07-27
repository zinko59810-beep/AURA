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

$stmt = $dbh->prepare("
    SELECT
        a.*,
        q.title
    FROM answers a
    JOIN questions q
        ON a.question_id = q.id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
");

$stmt->execute([
    $_SESSION['user_id']
]);

$answers = $stmt->fetchAll();
?>

<!DOCTYPE html>

<html lang="<?= htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8') ?>">

<head>
    <meta charset="UTF-8">

    <title><?= $lang['my_answers_title'] ?></title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link
        rel="stylesheet"
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

    <div class="container py-4 aura-content" style="max-width:700px;">

        <!-- ==========================
             Page Header
        =========================== -->
        <div class="d-flex align-items-center justify-content-between mb-4">

            <a
                href="/AURA/profile"
                class="text-decoration-none text-muted"
                aria-label="<?= $lang['back_to_profile'] ?>">
                ←
            </a>

            <h4 class="fw-bold mb-0">
                <i class="bi bi-chat-left-text"></i>
                <?= $lang['my_answers_title'] ?>
            </h4>

            <span></span>

        </div>

        <!-- ==========================
             No Answers Message
        =========================== -->
        <?php if (empty($answers)): ?>

            <div class="alert alert-info rounded-4">
                <?= $lang['no_posted_answers'] ?>
            </div>

        <?php else: ?>

            <!-- ==========================
                 Answer List
            =========================== -->
            <?php foreach ($answers as $answer): ?>

                <div class="card border-0 shadow-sm rounded-4 mb-3">

                    <div class="card-body">

                        <h5 class="fw-bold">
                            <?= htmlspecialchars(
                                $answer['title'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </h5>

                        <p class="text-muted">
                            <?= htmlspecialchars(
                                mb_strimwidth(
                                    $answer['content'],
                                    0,
                                    120,
                                    '...'
                                ),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </p>

                        <div class="d-flex justify-content-between align-items-center">

                            <small class="text-muted">
                                <?= htmlspecialchars(
                                    $answer['created_at'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </small>

                            <a
                                href="/AURA/question_detail?id=<?= urlencode($answer['question_id']) ?>"
                                class="btn btn-sm btn-primary rounded-pill">

                                <?= $lang['view_question'] ?>

                            </a>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>


    <script src="/AURA/pwa-register.js" defer></script>
</body>

</html>