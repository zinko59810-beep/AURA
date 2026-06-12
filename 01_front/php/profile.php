<?php

require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$dbh = db_connect();

$login_user_id = $_SESSION['user_id'];
$profile_user_id = $_GET['id'] ?? $login_user_id;
$is_own_profile = ((int)$profile_user_id === (int)$login_user_id);

// Profile user data
$stmt = $dbh->prepare("
    SELECT
    id,
    students_id,
    name,
    points,
    level,
    profile_image,
    last_active_at
FROM users
    WHERE id = ?
");

$stmt->execute([
    $profile_user_id
]);

$user = $stmt->fetch();
$is_online = false;

if (!empty($user['last_active_at'])) {

    $last_active = strtotime($user['last_active_at']);

    if ((time() - $last_active) < 300) {
        $is_online = true;
    }
}

if (!$user) {
    echo "User Not Found";
    exit;
}

// Questions Count
$q_stmt = $dbh->prepare("
    SELECT COUNT(*) AS total
    FROM questions
    WHERE user_id = ?
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

$project_stmt = $dbh->prepare("
    SELECT *
    FROM projects
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$project_stmt->execute([
    $profile_user_id
]);

$projects = $project_stmt->fetchAll();
$chat_stmt->execute([
    $_SESSION['user_id']
]);

$chat_count = $chat_stmt->fetch()['total'];

$q_stmt->execute([
    $profile_user_id
]);

$question_count = $q_stmt->fetch()['total'];

// Answers Count
$a_stmt = $dbh->prepare("
    SELECT COUNT(*) AS total
    FROM answers
    WHERE user_id = ?
");

$a_stmt->execute([
    $profile_user_id
]);

$answer_count = $a_stmt->fetch()['total'];

// Followers Count
$followers_stmt = $dbh->prepare("
    SELECT COUNT(*) AS total
    FROM follows
    WHERE following_id = ?
");

$followers_stmt->execute([
    $profile_user_id
]);

$followers_count = $followers_stmt->fetch()['total'];

// Following Count
$following_stmt = $dbh->prepare("
    SELECT COUNT(*) AS total
    FROM follows
    WHERE follower_id = ?
");

$following_stmt->execute([
    $profile_user_id
]);

$following_count = $following_stmt->fetch()['total'];

// Follow check
$is_following = false;

if (!$is_own_profile) {
    $follow_stmt = $dbh->prepare("
        SELECT *
        FROM follows
        WHERE follower_id = ?
        AND following_id = ?
    ");

    $follow_stmt->execute([
        $login_user_id,
        $profile_user_id
    ]);

    $is_following = $follow_stmt->fetch();
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="bg-light aura-app">

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

            <a href="profile.php" class="aura-menu-link active">
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

        <a href="javascript:history.back()" class="text-decoration-none text-muted">
            ← 戻る
        </a>

        <div class=" rounded-4 mt-3 mb-4">

            <div class="card-body text-center p-4">

                <?php if (!empty($user['profile_image'])): ?>

                    <img
                        src="<?= htmlspecialchars($user['profile_image']) ?>"
                        class="rounded-circle d-block mx-auto mb-3"
                        width="120"
                        height="120"
                        style="object-fit:cover;">


                    <!-- <p>
                        <?= htmlspecialchars($user['profile_image']) ?>
                    </p> -->

                <?php else: ?>

                    <div
                        class="rounded-circle bg-primary text-white mx-auto mb-3 d-flex align-items-center justify-content-center"
                        style="width:120px;height:120px;font-size:48px;">
                        <i class="bi bi-person-circle"></i>
                    </div>

                <?php endif; ?>

                <?php if ($is_own_profile): ?>

                    <a href="profile_upload.php"
                        class="btn btn-outline-primary btn-sm rounded-pill mb-3">
                        <i class="bi bi-camera"></i>
                        画像を変更
                    </a>

                <?php endif; ?>

                <h3 class="fw-bold">
                    <?= htmlspecialchars($user['name']) ?>
                </h3>
                <?php if ($is_online): ?>

                    <div class="text-success small mt-1">
                        🟢 Online
                    </div>

                <?php else: ?>

                    <div class="text-muted small mt-1">
                        ⚫ Offline
                    </div>

                <?php endif; ?>

                <p class="text-muted mb-2">
                    Student ID :
                    <?= htmlspecialchars($user['students_id']) ?>
                </p>

                <span class="badge bg-primary">
                    <?= htmlspecialchars($user['level']) ?>
                </span>

                <h2 class="mt-3 fw-bold">
                    <?= htmlspecialchars($user['points']) ?> pt
                </h2>

                <div class="row g-3 mt-4">

                    <div class="col-6 col-md-3">
                        <div class="card border-0 bg-light rounded-4">
                            <div class="card-body text-center">
                                <h4 class="fw-bold">
                                    <?= htmlspecialchars($question_count) ?>
                                </h4>
                                <small class="text-muted">
                                    Questions
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="card border-0 bg-light rounded-4">
                            <div class="card-body text-center">
                                <h4 class="fw-bold">
                                    <?= htmlspecialchars($answer_count) ?>
                                </h4>
                                <small class="text-muted">
                                    Answers
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="card border-0 bg-light rounded-4">
                            <div class="card-body text-center">
                                <h4 class="fw-bold">
                                    <?= htmlspecialchars($followers_count) ?>
                                </h4>
                                <small class="text-muted">
                                    Followers
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="card border-0 bg-light rounded-4">
                            <div class="card-body text-center">
                                <h4 class="fw-bold">
                                    <?= htmlspecialchars($following_count) ?>
                                </h4>
                                <small class="text-muted">
                                    Following
                                </small>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="mt-4">

                    <?php if ($is_own_profile): ?>

                        <div class="row g-3">

                            <div class="col-6">
                                <a href="my_questions.php"
                                    class="btn btn-outline-primary w-100 rounded-4 py-3">
                                    <i class="bi bi-journal-text"></i><br>
                                    自分の質問
                                </a>
                            </div>

                            <div class="col-6">
                                <a href="my_answers.php"
                                    class="btn btn-outline-dark w-100 rounded-4 py-3">
                                    <i class="bi bi-chat-left-text"></i><br>
                                    自分の回答
                                </a>
                            </div>

                        </div>

                        <div class="alert alert-light mt-3 rounded-4">
                            自分のプロフィールです
                        </div>
                        <a href="project_add.php"
                            class="btn btn-primary rounded-pill mt-3">
                            <i class="bi bi-folder-plus"></i>
                            Add Project
                        </a>

                    <?php else: ?>

                        <?php if (!$is_following): ?>

                            <a href="follow.php?id=<?= htmlspecialchars($user['id']) ?>"
                                class="btn btn-primary rounded-pill px-4">
                                <i class="bi bi-person-plus"></i>
                                フォローする
                            </a>

                        <?php else: ?>

                            <a href="unfollow.php?id=<?= htmlspecialchars($user['id']) ?>"
                                class="btn btn-outline-danger rounded-pill px-4">
                                <i class="bi bi-person-dash"></i>
                                フォロー解除
                            </a>

                        <?php endif; ?>

                        <a href="chat.php?id=<?= htmlspecialchars($user['id']) ?>"
                            class="btn btn-dark rounded-pill px-4 ms-2">
                            <i class="bi bi-chat-dots"></i>
                            Message
                        </a>


                    <?php endif; ?>

                </div>

            </div>


        </div>
        <h4 class="fw-bold mt-5 mb-3">
            Projects
        </h4>

        <?php if (empty($projects)): ?>

            <div class="alert alert-light rounded-4">
                No Projects Yet
            </div>

        <?php else: ?>

            <div class="row g-4">


                <?php foreach ($projects as $project): ?>

                    <div class="col-12 col-md-6">


                        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative">

                            <?php if ($is_own_profile): ?>
                                <div class="position-absolute top-0 end-0 p-2">
                                    <a href="project_edit.php?id=<?= $project['id'] ?>"
                                        class="text-primary me-2">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <a href="project_delete.php?id=<?= $project['id'] ?>"
                                        class="text-danger">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            <?php endif; ?>




                            <?php if (!empty($project['image_path'])): ?>


                                <img
                                    src="<?= htmlspecialchars($project['image_path']) ?>"
                                    class="card-img-top"
                                    style="height:180px; object-fit:cover;">
                                    <?= "/01_front/php/" . $project['image_path'] ?>

                            <?php endif; ?>

                            <div class="card-body d-flex flex-column">

                                <h5 class="fw-bold">
                                    <?= htmlspecialchars($project['title']) ?>
                                </h5>

                                <p class="text-muted small flex-grow-1">
                                    <?= nl2br(htmlspecialchars($project['description'])) ?>
                                </p>

                                <div class="d-flex gap-2">


                                    <?php if (!empty($project['github_url'])): ?>
                                        <a
                                            href="<?= htmlspecialchars($project['github_url']) ?>"
                                            target="_blank"
                                            class="btn btn-dark btn-sm rounded-pill">
                                            <i class="bi bi-github"></i>
                                            GitHub
                                        </a>
                                    <?php endif; ?>

                                    <?php if (!empty($project['demo_url'])): ?>
                                        <a
                                            href="<?= htmlspecialchars($project['demo_url']) ?>"
                                            target="_blank"
                                            class="btn btn-outline-primary btn-sm rounded-pill">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                            Demo
                                        </a>
                                    <?php endif; ?>



                                </div>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>
    </div>


</body>

</html>