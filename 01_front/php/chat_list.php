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
$group_stmt = $dbh->prepare("
    SELECT
        g.id,
        g.name,
        g.image,

        (
            SELECT gm.message
            FROM group_messages gm
            WHERE gm.group_id = g.id
            ORDER BY gm.created_at DESC
            LIMIT 1
        ) AS last_message

    FROM chat_groups g

    INNER JOIN group_members m
        ON g.id = m.group_id

    WHERE m.user_id = ?

    ORDER BY g.name
");

$group_stmt->execute([
    $_SESSION['user_id']
]);

$groups = $group_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="<?= $currentLang ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= $lang['message_title'] ?></title>

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="/AURA/01_front/css/style.css">

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
    <?php $currentPage = 'chat'; ?>
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <div class="container py-4 aura-content">

        <h3 class="fw-bold mb-4">
            <?= $lang['chat'] ?>
        </h3>

        <?php if (empty($users)): ?>

            <div class="alert alert-light rounded-4">
                <?= $lang['no_messages'] ?>
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
        src="/AURA/uploads/profile/<?= rawurlencode(
            basename($user['profile_image'])
        ) ?>"
        alt="<?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?>"
        class="chat-list-avatar me-3">

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
                                    $user['last_message'] ?? $lang['no_last_message'],
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


        <!-- =========================
             GROUP CHAT
        ========================= -->

        <div class="d-flex justify-content-between align-items-center mt-4 mb-3">

            <h4 class="fw-bold mb-0">
                Group Chat
            </h4>

            <a href="/AURA/01_front/php/create_group.php"
               class="btn btn-primary rounded-pill">

                <i class="bi bi-people-fill"></i>
                Create Group

            </a>

        </div>


        <?php if (empty($groups)): ?>

            <div class="alert alert-light rounded-4">
                No group chats yet.
            </div>

        <?php endif; ?>


        <?php foreach ($groups as $group): ?>

            <div class="card border-0 shadow-sm rounded-4 mb-3">

                <div class="card-body d-flex align-items-center">

                    <!-- Group Chat -->
                    <a
                        href="/AURA/01_front/php/group_chat.php?id=<?= urlencode($group['id']) ?>"
                        class="text-decoration-none text-dark flex-grow-1 d-flex align-items-center"
                    >

                        <?php if (!empty($group['image'])): ?>

                            <img
                                src="/AURA/<?= htmlspecialchars(
                                    $group['image'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                alt="<?= htmlspecialchars(
                                    $group['name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                class="chat-list-avatar me-3"
                                style="object-fit: cover;"
                            >

                        <?php else: ?>

                            <div class="chat-list-avatar chat-list-default-avatar me-3">
                                <i class="bi bi-people-fill"></i>
                            </div>

                        <?php endif; ?>


                        <div class="flex-grow-1">

                            <h6 class="fw-bold mb-1">
                                <?= htmlspecialchars(
                                    $group['name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </h6>

                            <small class="text-muted">

                                <?= htmlspecialchars(
                                    $group['last_message'] ?? 'No messages yet',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </small>

                        </div>

                    </a>


                    <!-- Delete Group -->
                    <form
                        action="/AURA/01_front/php/delete_group.php"
                        method="POST"
                        class="ms-2"
                    >

                        <input
                            type="hidden"
                            name="id"
                            value="<?= htmlspecialchars($group['id']) ?>"
                        >

                        <button
                            type="submit"
                            class="btn btn-link text-secondary p-1"
                            onclick="return confirm('Delete this group?')"
                            title="Delete Group"
                        >

                            <i class="bi bi-trash3 fs-5"></i>

                        </button>

                    </form>

                </div>

            </div>

        <?php endforeach; ?>

        

    </div>


    <script src="/AURA/pwa-register.js" defer></script>
</body>

</html>