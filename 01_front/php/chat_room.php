<?php
require_once(__DIR__ . '/../../02_backend/db/db_.php');
require_once __DIR__ . '/../../lang/language.php';



if (!isset($_SESSION['user_id'])) {
    header("Location: /AURA/login");
    exit;
}

$dbh = db_connect();

// Update online status
$active_stmt = $dbh->prepare("
    UPDATE users
    SET last_active_at = NOW()
    WHERE id = ?
");

$active_stmt->execute([
    $_SESSION['user_id']
]);

$other_user_id = (int)($_GET['id'] ?? 0);

if ($other_user_id == $_SESSION['user_id']) {
    header("Location: /AURA/profile");
    exit;
}

// Get chat partner
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

$stmt->execute([$other_user_id]);
$other_user = $stmt->fetch();

if (!$other_user) {
    exit("User Not Found");
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

    $message = trim($_POST['message'] ?? '');

    if (mb_strlen($message) > 2000) {
        $message = mb_substr($message, 0, 2000);
    }

    if ($message !== '') {

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

        header("Location: /AURA/chat_room?id=" . urlencode($other_user_id));
        exit;
    }
}

// Notification count
$notice_stmt = $dbh->prepare("
SELECT COUNT(*) total
FROM notifications
WHERE user_id=?
AND is_read=0
");

$notice_stmt->execute([
    $_SESSION['user_id']
]);

$notice_count = $notice_stmt->fetch()['total'];

// Read messages first
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

// Then count remaining unread messages
$chat_stmt = $dbh->prepare("
    SELECT COUNT(*) AS total
    FROM direct_messages
    WHERE receiver_id = ?
      AND is_read = 0
");

$chat_stmt->execute([
    $_SESSION['user_id']
]);

$chat_count = (int)$chat_stmt->fetch()['total'];
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8') ?>">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <title><?= $lang['chat_room_title'] ?></title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link
        rel="stylesheet"
        href="/AURA/01_front/css/style.css">

    <script
        type="module"
        src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js">
    </script>


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

<body class="aura-app chat-page">

    <?php
    $currentPage = 'chat';
    require_once __DIR__ . '/partials/sidebar.php';
    ?>

    <div class="container py-4 aura-content" style="max-width:700px;">

        <div
            class="card border-0 shadow-sm rounded-4 d-flex flex-column chat-shell">

            <!-- ==========================
                 Chat Header
            =========================== -->
            <div class="card-header bg-white d-flex align-items-center">

                <a
                    href="/AURA/chat"
                    class="text-decoration-none text-muted me-3"
                    aria-label="<?= $lang['back_to_chat_list'] ?>">
                    ←
                </a>

                <a
                    href="/AURA/profile?id=<?= urlencode($other_user['id']) ?>"
                    class="d-flex align-items-center text-decoration-none text-dark flex-grow-1">

                    <?php if (!empty($other_user['profile_image'])): ?>

    <img
        src="/AURA/uploads/profile/<?= rawurlencode(
            basename($other_user['profile_image'])
        ) ?>"
        alt="<?= htmlspecialchars($other_user['name'], ENT_QUOTES, 'UTF-8') ?>"
        class="chat-list-avatar me-3">

<?php else: ?>

    <div class="chat-list-avatar chat-list-default-avatar me-3">
        <i class="bi bi-person-fill"></i>
    </div>

<?php endif; ?>
                    <div>

                        <strong>
                            <?= htmlspecialchars(
                                $other_user['name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </strong>

                        <br>

                        <small class="text-muted">
                            <?= $lang['student_id_label'] ?> :
                            <?= htmlspecialchars(
                                $other_user['students_id'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </small>

                        <br>

                        <?php if ($is_online): ?>

                            <small class="text-success">
                                <i class="bi bi-circle-fill"></i>
                                <?= $lang['online'] ?>
                            </small>

                        <?php else: ?>

                            <small class="text-muted">
                                <i class="bi bi-circle-fill"></i>
                                <?= $lang['offline'] ?>
                            </small>

                        <?php endif; ?>

                    </div>

                </a>

            </div>

            <!-- ==========================
                 Chat Messages
            =========================== -->
            <div
                id="chat-box"
                class="card-body flex-grow-1 overflow-auto">

            </div>

            <!-- ==========================
                 Message Form
            =========================== -->
            <div class="card-footer bg-white position-relative">

                <form method="POST" class="d-flex align-items-center gap-2">

                    <!-- Emoji Button -->
                    <button
                        type="button"
                        id="emojiBtn"
                        class="btn btn-light rounded-circle"
                        aria-label="<?= $lang['open_emoji_picker'] ?>">
                        😊
                    </button>

                    <!-- Message Input -->
                    <input
                        type="text"
                        id="message"
                        name="message"
                        class="form-control rounded-pill"
                        placeholder="<?= $lang['message_placeholder'] ?>"
                        autocomplete="off">

                    <!-- Send Button -->
                    <button
                        type="submit"
                        class="btn btn-primary rounded-pill px-4"
                        aria-label="<?= $lang['send_message'] ?>">

                        <i class="bi bi-send-fill"></i>

                    </button>

                </form>

                <!-- Emoji Picker -->
                <emoji-picker id="emojiPicker"></emoji-picker>

            </div>

        </div>

    </div>

    <!-- ==========================
         Load Chat Messages
    =========================== -->
    <script>
        function loadMessages() {

            fetch("/AURA/chat_messages?id=<?= urlencode($other_user_id) ?>")
                .then(response => response.text())
                .then(data => {

                    const box =
                        document.getElementById("chat-box");

                    box.innerHTML = data;

                    box.scrollTop =
                        box.scrollHeight;

                })
                .catch(error => {
                    console.error("Chat message load error:", error);
                });

        }

        loadMessages();

        setInterval(loadMessages, 2000);
    </script>

    <!-- ==========================
         Emoji Picker
    =========================== -->
    <script>
        const emojiBtn =
            document.getElementById("emojiBtn");

        const emojiPicker =
            document.getElementById("emojiPicker");

        const messageInput =
            document.getElementById("message");

        emojiBtn.addEventListener("click", function(e) {

            e.stopPropagation();

            if (emojiPicker.style.display === "block") {

                emojiPicker.style.display = "none";

            } else {

                emojiPicker.style.display = "block";

            }

        });

        emojiPicker.addEventListener(
            "emoji-click",
            function(event) {

                messageInput.value +=
                    event.detail.unicode;

                messageInput.focus();

                emojiPicker.style.display = "none";

            }
        );

        document.addEventListener("click", function(e) {

            if (
                !emojiPicker.contains(e.target) &&
                e.target !== emojiBtn
            ) {

                emojiPicker.style.display = "none";

            }

        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const badge = document.getElementById('chatUnreadBadge');

            if (!badge) {
                return;
            }

            const unreadCount = <?= (int)$chat_count ?>;

            if (unreadCount > 0) {
                badge.textContent = unreadCount;
                badge.classList.remove('d-none');
            } else {
                badge.textContent = '0';
                badge.classList.add('d-none');
            }
        });
    </script>


    <script src="/AURA/pwa-register.js" defer></script>
</body>

</html>