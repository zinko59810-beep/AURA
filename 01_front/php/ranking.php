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
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ランキング</title>

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/AURA/01_front/css/style.css">
</head>

<body class="bg-light aura-app">
    <?php $currentPage = 'ranking'; ?>
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <div class="container-fluid py-4 px-4 aura-content">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <!-- <a href="profile.php" class="text-decoration-none text-muted">
                ←戻る
            </a> -->

            <h4 class="fw-bold mb-0">
                <i class="bi bi-trophy"></i> ランキング
            </h4>

            <span></span>
        </div>

        <?php if (empty($users)): ?>

            <div class="alert alert-info rounded-4">
                ユーザーがいません。
            </div>

        <?php else: ?>

            <?php foreach ($users as $index => $user): ?>

                <?php
                $rank = $index + 1;

                if ($rank === 1) {
                    $medal = '<i class="bi bi-award-fill text-warning"></i>';
                    $card_class = "border-warning";
                } elseif ($rank === 2) {
                    $medal = '<i class="bi bi-award-fill text-secondary"></i>';
                    $card_class = "border-secondary";
                } elseif ($rank === 3) {
                    $medal = '<i class="bi bi-award-fill text-danger"></i>';
                    $card_class = "border-danger";
                } else {
                    $medal = $rank;
                    $card_class = "border-0";
                }
                ?>
                <a href="/AURA/profile?id=<?= $user['id'] ?>"
                class="text-decoration-none text-dark">
                <div class="card shadow-sm border-0 rounded-4 mb-3 ranking-card">
                    <div class="card-body d-flex align-items-center">

                        <div class="me-3 fs-2">
                            <?= $medal ?>
                        </div>

                        <a
                            href="/AURA/profile?id=<?= urlencode($user['id']) ?>"
                            class="d-flex align-items-center flex-grow-1 text-decoration-none text-dark">

                            <?php if (!empty($user['profile_image'])): ?>

                                <img
                                    src="/AURA/<?= htmlspecialchars(
                                                    ltrim($user['profile_image'], '/'),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                    alt="<?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?>"
                                    class="ranking-avatar me-3"
                                    onerror="
                this.onerror=null;
                this.style.display='none';
                this.nextElementSibling.style.display='flex';
            ">

                                <div
                                    class="ranking-avatar default-avatar me-3"
                                    style="display:none;">
                                    <i class="bi bi-person-fill"></i>
                                </div>

                            <?php else: ?>

                                <div class="ranking-avatar default-avatar me-3">
                                    <i class="bi bi-person-fill"></i>
                                </div>

                            <?php endif; ?>

                            <div class="flex-grow-1">

                                <h5 class="mb-1 fw-bold">
                                    <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?>
                                </h5>

                                <small class="text-muted">
                                    ID: <?= htmlspecialchars($user['students_id'], ENT_QUOTES, 'UTF-8') ?>
                                </small>

                                <div>
                                    <span class="badge bg-primary">
                                        <?= htmlspecialchars($user['level'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </div>

<<<<<<< HEAD
=======
                        <div class="flex-grow-1">
                            <h5 class="mb-1 fw-bold">
                                <a href="/AURA/profile?id=<?= $user['id'] ?>"
                                    class="text-decoration-none text-dark">
                                    <?= htmlspecialchars($user['name']) ?>
                                </a>
                            </h5>
                            <small class="text-muted">ID: <?= htmlspecialchars($user['students_id']) ?></small>
                            <div>
                                <span class="badge bg-primary"><?= htmlspecialchars($user['level']) ?></span>
>>>>>>> 1a1560090502bdf858600fe3a98c20d6375aea55
                            </div>

                        </a>

                        <div class="text-end">
                            <h5 class="fw-bold mb-0"><?= htmlspecialchars($user['points']) ?></h5>
                            <small class="text-muted">pt</small>
                        </div>

                    </div>
                </div>
                </a>
            <?php endforeach; ?>

        <?php endif; ?>

    </div>
</body>

</html>