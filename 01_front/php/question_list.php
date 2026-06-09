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

$keyword = trim($_GET['keyword'] ?? '');
$users = [];
$stmt = $dbh->query("
    SELECT
        q.*,
        u.name,
        u.students_id,
        s.subject_name
    FROM questions q
    JOIN users u ON q.user_id = u.id
    JOIN subjects s ON q.subject_id = s.id
    ORDER BY q.created_at DESC
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

$questions = $stmt->fetchAll();
if ($keyword !== '') {
    $user_stmt = $dbh->prepare("
        SELECT
            id,
            name,
            students_id,
            points,
            level,
            profile_image
        FROM users
        WHERE
            name LIKE ?
            OR students_id LIKE ?
        ORDER BY name ASC
    ");

    $user_stmt->execute([
        "%{$keyword}%",
        "%{$keyword}%"
    ]);

    $users = $user_stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AURA Study</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="bg-light aura-app">

    <aside class="aura-sidebar">
        <a href="question_list.php" class="aura-brand">
            <span class="aura-brand-mark">A</span>
            <span>AURA Study</span>
        </a>


        <nav class="aura-menu">
            <a href="question_list.php" class="aura-menu-link active">
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


    <div class="container py-4 aura-content">

        <h3 class="fw-bold mb-4">AURA Study</h3>

        <form method="GET" class="mb-4">

            <div class="input-group">

                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>

                <input
                    type="text"
                    name="keyword"
                    class="form-control"
                    placeholder="Search by Name / Student ID"
                    value="<?= htmlspecialchars($keyword) ?>">

                <button
                    type="submit"
                    class="btn btn-primary">
                    Search
                </button>

            </div>

        </form>

        <?php if ($keyword !== ''): ?>

            <h5 class="fw-bold mb-3">
                Search Results
            </h5>

            <?php if (empty($users)): ?>

                <div class="alert alert-light rounded-4">
                    ユーザーが見つかりません。
                </div>

            <?php else: ?>

                <?php foreach ($users as $search_user): ?>

                    <div class="card border-0 shadow-sm rounded-4 mb-3">
                        <div class="card-body d-flex align-items-center">

                            <?php if (!empty($search_user['profile_image'])): ?>
                                <img
                                    src="<?= htmlspecialchars($search_user['profile_image']) ?>"
                                    class="rounded-circle me-3"
                                    width="55"
                                    height="55"
                                    style="object-fit:cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center"
                                    style="width:55px;height:55px;">
                                    <i class="bi bi-person"></i>
                                </div>
                            <?php endif; ?>

                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">
                                    <?= htmlspecialchars($search_user['name']) ?>
                                </h6>

                                <small class="text-muted">
                                    ID: <?= htmlspecialchars($search_user['students_id']) ?>
                                </small>

                                <div>
                                    <span class="badge bg-primary">
                                        <?= htmlspecialchars($search_user['level']) ?>
                                    </span>

                                    <span class="text-muted small">
                                        <?= htmlspecialchars($search_user['points']) ?> pt
                                    </span>
                                </div>
                            </div>

                            <a href="profile.php?id=<?= $search_user['id'] ?>"
                                class="btn btn-sm btn-primary rounded-pill">
                                Profile
                            </a>

                        </div>
                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

            <hr class="my-4">

        <?php endif; ?>


        <?php foreach ($questions as $question): ?>

            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">

                    <span class="badge bg-primary mb-2">
                        <?= htmlspecialchars($question['subject_name']) ?>
                    </span>

                    <a href="profile.php?id=<?= $question['user_id'] ?>">
                        <?= htmlspecialchars($question['name']) ?>
                    </a>

                    <p class="text-muted">
                        <?= htmlspecialchars(mb_strimwidth($question['goal'], 0, 100, "...")) ?>
                    </p>

                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <?= htmlspecialchars($question['name']) ?>
                        </small>

                        <a href="question_detail.php?id=<?= $question['id'] ?>"
                            class="btn btn-sm btn-primary rounded-pill">
                            詳細
                        </a>
                    </div>

                </div>
            </div>

        <?php endforeach; ?>

    </div>
</body>

</html>