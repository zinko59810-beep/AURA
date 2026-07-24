<?php

require_once(__DIR__ . '/../../02_backend/db/db_.php');
require_once __DIR__ . '/../../lang/language.php';



if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$dbh = db_connect();

$stmt = $dbh->prepare("
    SELECT
        n.*,
        u.name,
        u.students_id,
        u.profile_image
    FROM notifications n
    LEFT JOIN users u
        ON n.sender_id = u.id
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
");

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

$stmt->execute([
    $_SESSION['user_id']
]);

$notifications = $stmt->fetchAll();

$read_stmt = $dbh->prepare("
    UPDATE notifications
    SET is_read = 1
    WHERE user_id = ?
");

$read_stmt->execute([
    $_SESSION['user_id']
]);

?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8') ?>">

<head>
    <meta charset="UTF-8">

    <title><?= $lang['notifications_title'] ?></title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link
        rel="stylesheet"
        href="/AURA/01_front/css/style.css">
</head>

<body class="bg-light aura-app">

    <?php $currentPage = 'notice'; ?>
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <div class="container py-4 aura-content">

        <!-- ==========================
             Page Title
        =========================== -->
        <h3 class="fw-bold mb-4">
            <i class="bi bi-bell"></i>
            <?= $lang['notifications_title'] ?>
        </h3>

        <!-- ==========================
             No Notifications
        =========================== -->
        <?php if (empty($notifications)): ?>

            <div class="alert alert-light rounded-4">
                <?= $lang['no_notifications'] ?>
            </div>

        <?php else: ?>

            <!-- ==========================
                 Notification List
            =========================== -->
            <?php foreach ($notifications as $notification): ?>

                <a
                    href="<?= !empty($notification['sender_id'])
                                ? '/AURA/profile?id=' . urlencode($notification['sender_id'])
                                : '#' ?>"
                    class="text-decoration-none text-dark">

                    <div class="card border-0 shadow-sm rounded-4 mb-3">

                        <div class="card-body d-flex align-items-center">

                            <?php if (!empty($notification['profile_image'])): ?>

                                <img
                                    src="<?= htmlspecialchars(
                                                $notification['profile_image'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                    alt="<?= $lang['user_profile_image'] ?>"
                                    class="rounded-circle me-3"
                                    width="50"
                                    height="50"
                                    style="object-fit:cover;">

                            <?php else: ?>

                                <div
                                    class="rounded-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center"
                                    style="width:50px;height:50px;">

                                    <i class="bi bi-person"></i>

                                </div>

                            <?php endif; ?>

                            <div>

                                <strong>
                                    <?= htmlspecialchars(
                                        $notification['students_id'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </strong>

                                <?= htmlspecialchars(
                                    $notification['name'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                                <br>

                                <?= htmlspecialchars(
                                    $notification['message'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                                <br>

                                <small class="text-muted">
                                    <?= htmlspecialchars(
                                        $notification['created_at'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </small>

                            </div>

                        </div>

                    </div>

                </a>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</body>

</html>