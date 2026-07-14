<?php
require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

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

$stmt = $dbh->prepare("
SELECT
    u.id,
    u.name,
    u.students_id,
    u.profile_image,

    (
        SELECT dm.message
        FROM direct_messages dm
        WHERE
        (
            dm.sender_id = u.id
            AND dm.receiver_id = ?
        )
        OR
        (
            dm.sender_id = ?
            AND dm.receiver_id = u.id
        )
        ORDER BY dm.created_at DESC
        LIMIT 1
    ) AS last_message,

    (
        SELECT COUNT(*)
        FROM direct_messages dm2
        WHERE dm2.sender_id = u.id
        AND dm2.receiver_id = ?
        AND dm2.is_read = 0
    ) AS unread_count

FROM users u

WHERE u.id IN
(
    SELECT sender_id
    FROM direct_messages
    WHERE receiver_id = ?

    UNION

    SELECT receiver_id
    FROM direct_messages
    WHERE sender_id = ?
)

ORDER BY u.name
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
    $_SESSION['user_id'],
    $_SESSION['user_id'],
    $_SESSION['user_id'],
    $_SESSION['user_id'],
    $_SESSION['user_id']
]);

$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>Message</title>

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="/AURA/01_front/css/style.css">
</head>

<body class="bg-light aura-app">
    <?php $currentPage = 'chat'; ?>
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <div class="container py-4 aura-content">

        <h3 class="fw-bold mb-4">
            Chat
        </h3>

        <?php if (empty($users)): ?>

            <div class="alert alert-light rounded-4">
                まだメッセージがありません
            </div>

        <?php endif; ?>

        <?php foreach ($users as $user): ?>

            <a
                href="/AURA/chat_room?id=<?= urlencode($user['id']) ?>"
                class="text-decoration-none text-dark">

                <div class="card border-0 shadow-sm rounded-4 mb-3">

                    <div class="card-body d-flex align-items-center">

                        <?php if (!empty($user['profile_image'])): ?>

                            <img
                                src="/AURA/<?= htmlspecialchars(
                                                ltrim($user['profile_image'], '/'),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                alt="<?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?>"
                                class="chat-list-avatar me-3"
                                onerror="
            this.onerror=null;
            this.style.display='none';
            this.nextElementSibling.style.display='flex';
        ">

                            <div
                                class="chat-list-avatar chat-list-default-avatar me-3"
                                style="display:none;">
                                <i class="bi bi-person-fill"></i>
                            </div>

                        <?php else: ?>

                            <div class="chat-list-avatar chat-list-default-avatar me-3">
                                <i class="bi bi-person-fill"></i>
                            </div>

                        <?php endif; ?>

                        <div class="flex-grow-1">

                            <h6 class="fw-bold mb-1">
                                <?= htmlspecialchars($user['name']) ?>
                            </h6>

                            <small class="text-muted">
                                <?= htmlspecialchars(
                                    $user['last_message'] ?? 'メッセージはありません',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </small>

                        </div>

                        <?php if ($user['unread_count'] > 0): ?>

                            <span class="badge bg-danger rounded-pill">
                                <?= $user['unread_count'] ?>
                            </span>

                        <?php endif; ?>

                    </div>
                </div>

            </a>

        <?php endforeach; ?>

    </div>

</body>

</html>