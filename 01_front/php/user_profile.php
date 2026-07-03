<?php

require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_GET['id'] ?? 0;

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
    SELECT *
    FROM users
    WHERE id = ?
");

$stmt->execute([$user_id]);

$user = $stmt->fetch();

if (!$user) {
    echo "User Not Found";
    exit;
}

$follow_stmt = $dbh->prepare("
    SELECT *
    FROM follows
    WHERE follower_id = ?
    AND following_id = ?
");

$follow_stmt->execute([
    $_SESSION['user_id'],
    $user_id
]);

$is_following = $follow_stmt->fetch();
// Followers Count
$followers_stmt = $dbh->prepare("
    SELECT COUNT(*) AS total
    FROM follows
    WHERE following_id = ?
");

$followers_stmt->execute([
    $user_id
]);

$followers_count = $followers_stmt->fetch()['total'];


// Following Count
$following_stmt = $dbh->prepare("
    SELECT COUNT(*) AS total
    FROM follows
    WHERE follower_id = ?
");

$following_stmt->execute([
    $user_id
]);

$following_count = $following_stmt->fetch()['total'];

?>

<!DOCTYPE html>

<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>User Profile</title>


    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="../css/style.css">


</head>

<body class="aura-app">
    <?php $currentPage = 'profile'; ?>
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<div class="container py-4 aura-content" style="max-width:500px;">


        <a href="question_list.php"
            class="text-decoration-none text-muted">
            ← 戻る
        </a>

        <div class="card border-0 shadow-sm rounded-4 mt-3">

            <div class="card-body text-center">

                <?php if (!empty($user['profile_image'])): ?>

                    <img
                        src="<?= htmlspecialchars($user['profile_image']) ?>"
                        class="rounded-circle mb-3"
                        width="120"
                        height="120"
                        style="object-fit:cover;">

                <?php else: ?>

                    <div
                        class="rounded-circle bg-primary text-white mx-auto mb-3 d-flex align-items-center justify-content-center"
                        style="width:120px;height:120px;font-size:48px;">
                        <i class="bi bi-person-circle"></i>
                    </div>

                <?php endif; ?>

                <h3>
                    <?= htmlspecialchars($user['name']) ?>
                </h3>

                <p class="text-muted">
                    Student ID :
                    <?= htmlspecialchars($user['students_id']) ?>
                </p>

                <span class="badge bg-primary">
                    <?= htmlspecialchars($user['level']) ?>
                </span>

                <h2 class="mt-3">
                    <?= htmlspecialchars($user['points']) ?> pt
                </h2>
                <div class="row mt-4">

                    <div class="col-6">
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

                    <div class="col-6">
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
                <?php if ($user['id'] != $_SESSION['user_id']): ?>

                    <a href="chat.php?id=<?= $user['id'] ?>"
                        class="btn btn-dark rounded-pill mt-3">
                        <i class="bi bi-chat-dots"></i>
                        Message
                    </a>

                <?php endif; ?>


                <?php if ($user['id'] != $_SESSION['user_id']): ?>

                    <?php if (!$is_following): ?>
                        <a href="follow.php?id=<?= $user['id'] ?>"
                            class="btn btn-primary rounded-pill mt-3">
                            フォローする
                        </a>
                    <?php else: ?>
                        <button class="btn btn-success rounded-pill mt-3" disabled>
                            フォロー中
                        </button>
                    <?php endif; ?>
                    <a href="unfollow.php?id=<?= $user['id'] ?>"
                        class="btn btn-outline-danger rounded-pill mt-3">
                        フォロー解除
                    </a>

                <?php else: ?>

                    <div class="alert alert-light mt-3 rounded-4">
                        自分のプロフィールです
                    </div>

                <?php endif; ?>

            </div>

        </div>


    </div>

</body>

</html>