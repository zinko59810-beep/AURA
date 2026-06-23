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
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="bg-light aura-app">

    <aside class="aura-sidebar">
        <a href="question_list.php" class="aura-brand">
            <span class="aura-brand-mark">
                <i class="bi bi-fire"></i>
            </span>
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
            <a href="chat_list.php" class="aura-menu-link">
                <i class="bi bi-chat-dots"></i>
                <span>
                    Chat
                    <?php if ($chat_count > 0): ?>
                        <span class="badge bg-danger rounded-pill">
                            <?= $chat_count ?>
                        </span>
                    <?php endif; ?>
                </span>
            </a>
            <a href="notification.php" class="aura-menu-link">
                <i class="bi bi-bell"></i>
                <span>
                    Notice
                    <?php if ($notice_count > 0): ?>
                        <span class="badge bg-danger rounded-pill">
                            <?= $notice_count ?>
                        </span>
                    <?php endif; ?>
                </span>
            </a>
            <a href="ranking.php" class="aura-menu-link active">
                <i class="bi bi-trophy"></i>
                <span>Ranking</span>
            </a>
            <a href="profile.php" class="aura-menu-link">
                <i class="bi bi-person-circle"></i>
                <span>Profile</span>
            </a>

        <a href="game.html" class="aura-menu-link">
                <i class="bi bi-controller"></i>
                <span>Arcade</span>
            </a>
        </nav>

        <a href="logout.php" class="aura-logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>
    </aside>


    <div class="container-fluid py-4 px-4 aura-content">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <a href="profile.php" class="text-decoration-none text-muted">
                ←
            </a>

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

                <div class="card shadow-sm rounded-4 mb-2 <?= $card_class ?>"
                    style="width: 100%; max-width:100%;">

                    <div class="card-body d-flex align-items-center">

                        <div class="me-3 fs-2">
                            <?= $medal ?>
                        </div>

                        <?php if (!empty($user['profile_image'])): ?>

                            <img
                                src="<?= htmlspecialchars($user['profile_image']) ?>"
                                class="rounded-circle me-3"
                                width="55"
                                height="55"
                                style="object-fit:cover;">

                        <?php else: ?>

                            <div
                                class="rounded-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center"
                                style="width:55px;height:55px;font-size:24px;">
                                <i class="bi bi-person-circle"></i>
                            </div>

                        <?php endif; ?>

                        <div class="flex-grow-1">

                            <h5 class="mb-1 fw-bold">
                                <?= htmlspecialchars($user['name']) ?>
                            </h5>

                            <small class="text-muted">
                                ID: <?= htmlspecialchars($user['students_id']) ?>
                            </small>

                            <div>
                                <span class="badge bg-primary">
                                    <?= htmlspecialchars($user['level']) ?>
                                </span>
                            </div>

                        </div>

                        <div class="text-end">

                            <h5 class="fw-bold mb-0">
                                <?= htmlspecialchars($user['points']) ?>
                            </h5>

                            <small class="text-muted">
                                pt
                            </small>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>
</body>

</html>