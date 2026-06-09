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

$active_stmt->execute([
    $_SESSION['user_id']
]);

$other_user_id = $_GET['id'] ?? 0;

if ($other_user_id == $_SESSION['user_id']) {
    header("Location: profile.php");
    exit;
}

// Chat partner info
$stmt = $dbh->prepare("
    SELECT
    id,
    name,
    students_id,
    profile_image,
    last_active_at
FROM users
WHERE id = ?
");

$stmt->execute([
    $other_user_id
]);

$other_user = $stmt->fetch();

if (!$other_user) {
    echo "User Not Found";
    exit;
}
$is_online = false;

if (!empty($other_user['last_active_at'])) {
    $last_active = strtotime($other_user['last_active_at']);

    if ((time() - $last_active) < 300) {
        $is_online = true;
    }
}

// Send message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $message = trim($_POST['message'] ?? "");

    if ($message !== "") {

        $send_stmt = $dbh->prepare("
            INSERT INTO direct_messages
            (
                sender_id,
                receiver_id,
                message
            )
            VALUES
            (
                ?, ?, ?
            )
        ");

        $send_stmt->execute([
            $_SESSION['user_id'],
            $other_user_id,
            $message
        ]);

        header("Location: chat.php?id=" . urlencode($other_user_id));
        exit;
    }
}

// Read messages
$msg_stmt = $dbh->prepare("
    SELECT *
    FROM direct_messages
    WHERE
        (
            sender_id = ?
            AND receiver_id = ?
        )
        OR
        (
            sender_id = ?
            AND receiver_id = ?
        )
    ORDER BY created_at ASC
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

$msg_stmt->execute([
    $_SESSION['user_id'],
    $other_user_id,
    $other_user_id,
    $_SESSION['user_id']
]);

$messages = $msg_stmt->fetchAll();

// Mark as read
$read_stmt = $dbh->prepare("
    UPDATE direct_messages
    SET is_read = 1
    WHERE sender_id = ?
    AND receiver_id = ?
");

$read_stmt->execute([
    $other_user_id,
    $_SESSION['user_id']
]);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>Chat</title>

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="aura-app">

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
            <a href="chat_list.php" class="aura-menu-link active">
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

            <a href="ranking.php" class="aura-menu-link">
                <i class="bi bi-trophy"></i>
                <span>Ranking</span>
            </a>

            <a href="profile.php" class="aura-menu-link">
                <i class="bi bi-person-circle"></i>
                <span>Profile</span>
            </a>
        </nav>

        <a href="logout.php" class="aura-logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>
    </aside>

    <div class="container py-4 aura-content" style="max-width:700px;">

        <div class="card border-0 shadow-sm rounded-4">

            <div class="card-header bg-white d-flex align-items-center">

                <a href="profile.php?id=<?= htmlspecialchars($other_user['id']) ?>"
                    class="text-decoration-none text-muted me-3">
                    ←
                </a>

                <?php if (!empty($other_user['profile_image'])): ?>

                    <img
                        src="<?= htmlspecialchars($other_user['profile_image']) ?>"
                        class="rounded-circle me-3"
                        width="45"
                        height="45"
                        style="object-fit:cover;">



                <?php else: ?>

                    <div class="rounded-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center"
                        style="width:45px;height:45px;">
                        <i class="bi bi-person"></i>
                    </div>


                <?php endif; ?>

                <div>
                    <strong>
                        <?= htmlspecialchars($other_user['name']) ?>
                    </strong>

                    <br>

                    <small class="text-muted">
                        ID: <?= htmlspecialchars($other_user['students_id']) ?>
                    </small>
                    <?php if ($is_online): ?>
                        <small class="text-success">
                            <i class="bi bi-circle-fill"></i> Online
                        </small>
                    <?php else: ?>
                        <small class="text-muted">
                            <i class="bi bi-circle-fill"></i> Offline
                        </small>
                    <?php endif; ?>
                </div>


            </div>

            <div class="card-body" style="height:520px; overflow-y:auto;">

                <?php if (empty($messages)): ?>

                    <div class="text-center text-muted mt-5">
                        <i class="bi bi-chat-dots fs-1"></i>
                        <p class="mt-3">
                            メッセージを送って会話を始めましょう
                        </p>
                    </div>

                <?php else: ?>

                    <?php foreach ($messages as $msg): ?>

                        <?php $is_me = $msg['sender_id'] == $_SESSION['user_id']; ?>

                        <div class="d-flex mb-3 <?= $is_me ? 'justify-content-end' : 'justify-content-start' ?>">

                            <div
                                class="<?= $is_me ? 'bg-primary text-white' : 'bg-light text-dark' ?>"
                                style="
                max-width:70%;
                padding:10px 14px;
                border-radius:18px;
                box-shadow:0 2px 8px rgba(0,0,0,.08);
            ">

                                <?= nl2br(htmlspecialchars($msg['message'])) ?>

                                <div class="<?= $is_me ? 'text-white-50' : 'text-muted' ?> small mt-1">
                                    <?= htmlspecialchars($msg['created_at']) ?>
                                </div>

                                <?php if ($is_me): ?>
                                    <div class="small text-white-50">
                                        <?= $msg['is_read'] == 1 ? '✓✓ Seen' : '✓ Sent' ?>
                                    </div>
                                <?php endif; ?>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

            <div class="card-footer bg-white">

                <form method="POST" class="d-flex gap-2">

                    <input
                        type="text"
                        name="message"
                        class="form-control rounded-pill"
                        placeholder="メッセージを入力">

                    <button
                        type="submit"
                        class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-send-fill"></i>
                    </button>

                </form>

            </div>

        </div>

    </div>

</body>

</html>