<?php
require_once(__DIR__ . '/../../02_backend/db/db_.php');
require_once __DIR__ . '/../../lang/language.php';



if (!isset($_SESSION['user_id'])) {
    header("Location: /AURA/auth");
    exit;
}

$dbh = db_connect();

$active_stmt = $dbh->prepare("
    UPDATE users
    SET last_active_at = NOW()
    WHERE id = ?
");

$notice_stmt = $dbh->prepare("
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = ?
    AND is_read = 0
");

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

$notice_stmt->execute([
    $_SESSION['user_id']
]);

$notice_count = $notice_stmt->fetch()['total'];

$active_stmt->execute([
    $_SESSION['user_id']
]);

$stmt = $dbh->query("
    SELECT
        id,
        name,
        students_id,
        points,
        level,
        profile_image
    FROM users
    ORDER BY points DESC
    LIMIT 10
");

$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8') ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $lang['ranking_title'] ?></title>

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

    <?php $currentPage = 'ranking'; ?>
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <div class="container-fluid py-4 px-4 aura-content">

        <!-- ==========================
             Page Header
        =========================== -->
        <div class="d-flex align-items-center justify-content-between mb-4">

            <h4 class="fw-bold mb-0">
                <i class="bi bi-trophy"></i>
                <?= $lang['ranking_title'] ?>
            </h4>

            <span></span>

        </div>

        <!-- ==========================
             No Users
        =========================== -->
        <?php if (empty($users)): ?>

            <div class="alert alert-info rounded-4">
                <?= $lang['no_ranking_users'] ?>
            </div>

        <?php else: ?>

            <!-- ==========================
                 Ranking List
            =========================== -->
            <?php foreach ($users as $index => $user): ?>

                <?php
                $rank = $index + 1;

                if ($rank === 1) {
                    $medal = '<i class="bi bi-award-fill text-warning"></i>';
                    $card_class = 'border-warning';
                } elseif ($rank === 2) {
                    $medal = '<i class="bi bi-award-fill text-secondary"></i>';
                    $card_class = 'border-secondary';
                } elseif ($rank === 3) {
                    $medal = '<i class="bi bi-award-fill text-danger"></i>';
                    $card_class = 'border-danger';
                } else {
                    $medal = $rank;
                    $card_class = 'border-0';
                }
                ?>

                <div class="card shadow-sm <?= $card_class ?> rounded-4 mb-3 ranking-card">

                    <div class="card-body d-flex align-items-center">

                        <!-- Rank -->
                        <div class="me-3 fs-2">
                            <?= $medal ?>
                        </div>

                        <!-- Profile Image -->
                        <?php if (!empty($user['profile_image'])): ?>

                            <img
                                src="/AURA/<?= htmlspecialchars(
                                                ltrim($user['profile_image'], '/'),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                alt="<?= htmlspecialchars(
                                            $user['name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                class="ranking-avatar me-3"
                                onerror="
                    this.onerror = null;
                    this.style.display = 'none';
                    this.nextElementSibling.style.display = 'flex';
                ">

                            <div
                                class="ranking-avatar default-avatar me-3"
                                style="display: none;"
                                aria-label="<?= htmlspecialchars(
                                                $lang['default_profile_image'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>">

                                <i class="bi bi-person-fill"></i>
                            </div>

                        <?php else: ?>

                            <div
                                class="ranking-avatar default-avatar me-3"
                                aria-label="<?= htmlspecialchars(
                                                $lang['default_profile_image'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>">

                                <i class="bi bi-person-fill"></i>
                            </div>

                        <?php endif; ?>

                        <!-- User Information -->
                        <div class="flex-grow-1">

                            <h5 class="mb-1 fw-bold">
                                <a
                                    href="/AURA/profile?id=<?= urlencode($user['id']) ?>"
                                    class="text-decoration-none text-dark">

                                    <?= htmlspecialchars(
                                        $user['name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </a>
                            </h5>

                            <small class="text-muted">
                                <?= htmlspecialchars(
                                    $lang['student_id_label'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>:

                                <?= htmlspecialchars(
                                    $user['students_id'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </small>

                            <div>
                                <span class="badge bg-primary">
                                    <?= htmlspecialchars(
                                        $user['level'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </span>
                            </div>

                        </div>

                        <!-- Points -->
                        <div class="text-end">

                            <h5 class="fw-bold mb-0">
                                <?= htmlspecialchars(
                                    $user['points'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </h5>

                            <small class="text-muted">
                                <?= htmlspecialchars(
                                    $lang['points_short'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </small>

                        </div>

                    </div>

                </div>
            <?php endforeach; ?>

        <?php endif; ?>

    </div>


    <script src="/AURA/pwa-register.js" defer></script>
</body>

</html>