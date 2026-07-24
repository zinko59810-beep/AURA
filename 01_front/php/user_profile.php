<?php

require_once(__DIR__ . '/../../02_backend/db/db_.php');
require_once __DIR__ . '/../../lang/language.php';



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
<html lang="<?= htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8') ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $lang['user_profile_title'] ?></title>

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet"
        href="/AURA/01_front/css/style.css">
</head>

<body class="aura-app">

    <?php $currentPage = 'profile'; ?>
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <div class="container py-4 aura-content" style="max-width:500px;">

        <!-- ==========================
             Back Button
        =========================== -->
        <a
            href="/AURA/home"
            class="text-decoration-none text-muted">

            ← <?= $lang['back'] ?>

        </a>

        <div class="card border-0 shadow-sm rounded-4 mt-3">

            <div class="card-body text-center">

                <!-- ==========================
                     Profile Image
                =========================== -->
                <?php if (!empty($user['profile_image'])): ?>

                    <img
                        src="<?= htmlspecialchars(
                                    $user['profile_image'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                        alt="<?= $lang['user_profile_image'] ?>"
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

                <!-- ==========================
                     User Information
                =========================== -->
                <h3>
                    <?= htmlspecialchars(
                        $user['name'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </h3>

                <p class="text-muted">

                    <?= $lang['student_id_label'] ?>:

                    <?= htmlspecialchars(
                        $user['students_id'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </p>

                <span class="badge bg-primary">
                    <?= htmlspecialchars(
                        $user['level'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </span>

                <h2 class="mt-3">

                    <?= htmlspecialchars(
                        $user['points'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                    <?= $lang['points_short'] ?>

                </h2>

                <!-- ==========================
                     Follow Information
                =========================== -->
                <div class="row mt-4">

                    <div class="col-6">

                        <div class="card border-0 bg-light rounded-4">

                            <div class="card-body text-center">

                                <h4 class="fw-bold">
                                    <?= htmlspecialchars(
                                        $followers_count,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </h4>

                                <small class="text-muted">
                                    <?= $lang['followers'] ?>
                                </small>

                            </div>

                        </div>

                    </div>

                    <div class="col-6">

                        <div class="card border-0 bg-light rounded-4">

                            <div class="card-body text-center">

                                <h4 class="fw-bold">
                                    <?= htmlspecialchars(
                                        $following_count,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </h4>

                                <small class="text-muted">
                                    <?= $lang['following'] ?>
                                </small>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- ==========================
                     Other User Actions
                =========================== -->
                <?php if ($user['id'] != $_SESSION['user_id']): ?>

                    <a
                        href="/AURA/chat?id=<?= urlencode($user['id']) ?>"
                        class="btn btn-dark rounded-pill mt-3">

                        <i class="bi bi-chat-dots"></i>
                        <?= $lang['message_button'] ?>

                    </a>

                    <?php if (!$is_following): ?>

                        <a
                            href="/AURA/follow?id=<?= urlencode($user['id']) ?>"
                            class="btn btn-primary rounded-pill mt-3">

                            <?= $lang['follow'] ?>

                        </a>

                    <?php else: ?>

                        <button
                            type="button"
                            class="btn btn-success rounded-pill mt-3"
                            disabled>

                            <?= $lang['following_status'] ?>

                        </button>

                    <?php endif; ?>

                    <?php if ($is_following): ?>

                        <a
                            href="/AURA/unfollow?id=<?= urlencode($user['id']) ?>"
                            class="btn btn-outline-danger rounded-pill mt-3">

                            <?= $lang['unfollow'] ?>

                        </a>

                    <?php endif; ?>

                <?php else: ?>

                    <div class="alert alert-light mt-3 rounded-4">
                        <?= $lang['this_is_your_profile'] ?>
                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

</body>

</html>