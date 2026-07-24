<?php

require_once(__DIR__ . '/../../02_backend/db/db_.php');
require_once __DIR__ . '/../../lang/language.php';



if (!isset($_SESSION['user_id'])) {
    header("Location: /AURA/auth");
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
<html lang="<?= htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8') ?>">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        <?= $lang['profile_title'] ?>
    </title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link
        rel="stylesheet"
        href="/AURA/01_front/css/style.css">
</head>

<body class="bg-light aura-app">

    <?php $currentPage = 'profile'; ?>

    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <div
        class="container py-4 aura-content"
        style="max-width:700px;">

        <!-- ==========================
             Profile Main Area
        =========================== -->
        <div class="rounded-4 mt-3 mb-4">

            <div class="card-body text-center p-4">

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
                        class="rounded-circle d-block mx-auto mb-3"
                        width="120"
                        height="120"
                        style="object-fit:cover;">

                <?php else: ?>

                    <div
                        class="rounded-circle bg-primary text-white mx-auto mb-3 d-flex align-items-center justify-content-center"
                        style="width:120px;height:120px;font-size:48px;"
                        aria-label="<?= $lang['default_profile_image'] ?>">

                        <i class="bi bi-person-circle"></i>

                    </div>

                <?php endif; ?>

                <!-- ==========================
                     Change Profile Image
                =========================== -->
                <?php if ($is_own_profile): ?>

                    <a
                        href="/AURA/profile_upload"
                        class="btn btn-outline-primary btn-sm rounded-pill mb-3">

                        <i class="bi bi-camera"></i>

                        <?= $lang['change_profile_image'] ?>

                    </a>

                <?php endif; ?>

                <!-- ==========================
                     User Name
                =========================== -->
                <h3 class="fw-bold">

                    <?= htmlspecialchars(
                        $user['name'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </h3>

                <!-- ==========================
                     Online Status
                =========================== -->
                <?php if ($is_online): ?>

                    <div class="text-success small mt-1">

                        <?= $lang['online'] ?>

                    </div>

                <?php else: ?>

                    <div class="text-muted small mt-1">

                        <?= $lang['offline'] ?>

                    </div>

                <?php endif; ?>

                <!-- ==========================
                     Student ID
                =========================== -->
                <p class="text-muted mb-2">

                    <?= $lang['student_id_label'] ?>:

                    <?= htmlspecialchars(
                        $user['students_id'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </p>

                <!-- ==========================
                     User Level
                =========================== -->
                <span class="badge bg-primary">

                    <?= htmlspecialchars(
                        $user['level'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </span>

                <!-- ==========================
                     User Points
                =========================== -->
                <h2 class="mt-3 fw-bold">

                    <?= htmlspecialchars(
                        $user['points'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                    <?= $lang['points_short'] ?>

                </h2>

                <!-- ==========================
                     User Statistics
                =========================== -->
                <div class="row g-3 mt-4">

                    <!-- Questions -->
                    <div class="col-6 col-md-3">

                        <div class="card border-0 bg-light rounded-4">

                            <div class="card-body text-center">

                                <h4 class="fw-bold">

                                    <?= htmlspecialchars(
                                        $question_count,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </h4>

                                <small class="text-muted">

                                    <?= $lang['questions'] ?>

                                </small>

                            </div>

                        </div>

                    </div>

                    <!-- Answers -->
                    <div class="col-6 col-md-3">

                        <div class="card border-0 bg-light rounded-4">

                            <div class="card-body text-center">

                                <h4 class="fw-bold">

                                    <?= htmlspecialchars(
                                        $answer_count,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </h4>

                                <small class="text-muted">

                                    <?= $lang['answers'] ?>

                                </small>

                            </div>

                        </div>

                    </div>

                    <!-- Followers -->
                    <div class="col-6 col-md-3">

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

                    <!-- Following -->
                    <div class="col-6 col-md-3">

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

                <div class="mt-4">

                    <!-- ==========================
                     Action Buttons
                =========================== -->
                    <div class="mt-4">

                        <?php if ($is_own_profile): ?>

                            <div class="row g-3">

                                <!-- My Questions -->
                                <div class="col-6">

                                    <a
                                        href="/AURA/my_questions"
                                        class="btn btn-outline-primary w-100 rounded-4 py-3">

                                        <i class="bi bi-journal-text"></i>

                                        <br>

                                        <?= $lang['my_questions'] ?>

                                    </a>

                                </div>

                                <!-- My Answers -->
                                <div class="col-6">

                                    <a
                                        href="/AURA/my_answers"
                                        class="btn btn-outline-dark w-100 rounded-4 py-3">

                                        <i class="bi bi-chat-left-text"></i>

                                        <br>

                                        <?= $lang['my_answers'] ?>

                                    </a>

                                </div>

                            </div>

                            <!-- Own Profile -->
                            <div class="alert alert-light mt-3 rounded-4">

                                <?= $lang['this_is_your_profile'] ?>

                            </div>

                            <!-- Add Project -->
                            <a
                                href="/AURA/project_add"
                                class="btn btn-primary rounded-pill mt-3">

                                <i class="bi bi-folder-plus"></i>

                                <?= $lang['add_project'] ?>

                            </a>

                        <?php else: ?>

                            <!-- Follow Button -->
                            <?php if (!$is_following): ?>

                                <a
                                    href="/AURA/follow?id=<?= urlencode($user['id']) ?>"
                                    class="btn btn-primary rounded-pill px-4">

                                    <i class="bi bi-person-plus"></i>

                                    <?= $lang['follow'] ?>

                                </a>

                            <?php else: ?>

                                <a
                                    href="/AURA/unfollow?id=<?= urlencode($user['id']) ?>"
                                    class="btn btn-outline-danger rounded-pill px-4">

                                    <i class="bi bi-person-dash"></i>

                                    <?= $lang['unfollow'] ?>

                                </a>

                            <?php endif; ?>

                            <!-- Message Button -->
                            <a
                                href="/AURA/chat_room?id=<?= urlencode($user['id']) ?>"
                                class="btn btn-dark rounded-pill px-4 ms-2">

                                <i class="bi bi-chat-dots"></i>

                                <?= $lang['message_button'] ?>

                            </a>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

            <!-- ==========================
             Projects
        =========================== -->

            <h4 class="fw-bold mt-5 mb-3">

                <?= $lang['projects'] ?>

            </h4>

            <?php if (empty($projects)): ?>

                <div class="alert alert-light rounded-4">

                    <?= $lang['no_projects'] ?>

                </div>

            <?php else: ?>

                <div class="row g-4">


                    <?php foreach ($projects as $project): ?>

                        <div class="col-12 col-md-6">

                            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative">

                                <!-- ==========================
                                 Edit / Delete
                            =========================== -->
                                <?php if ($is_own_profile): ?>

                                    <div class="position-absolute top-0 end-0 p-2">

                                        <a
                                            href="/AURA/project_edit?id=<?= urlencode($project['id']) ?>"
                                            class="text-primary me-2">

                                            <i class="bi bi-pencil"></i>

                                        </a>

                                        <a
                                            href="/AURA/project_delete?id=<?= urlencode($project['id']) ?>"
                                            class="text-danger">

                                            <i class="bi bi-trash"></i>

                                        </a>

                                    </div>

                                <?php endif; ?>

                                <!-- ==========================
                                 Project Image
                            =========================== -->
                                <?php if (!empty($project['image_path'])): ?>

                                    <img
                                        src="<?= htmlspecialchars(
                                                    $project['image_path'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                        alt="<?= htmlspecialchars(
                                                    $project['title'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                        class="card-img-top"
                                        style="height:180px; object-fit:cover;">

                                <?php endif; ?>

                                <!-- ==========================
                                 Project Body
                            =========================== -->
                                <div class="card-body d-flex flex-column">

                                    <h5 class="fw-bold">

                                        <?= htmlspecialchars(
                                            $project['title'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </h5>

                                    <p class="text-muted small flex-grow-1">

                                        <?= nl2br(
                                            htmlspecialchars(
                                                $project['description'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            )
                                        ) ?>

                                    </p>

                                    <!-- ==========================
                                     Buttons
                                =========================== -->
                                    <div class="d-flex gap-2">

                                        <?php if (!empty($project['github_url'])): ?>

                                            <a
                                                href="<?= htmlspecialchars(
                                                            $project['github_url'],
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>"
                                                target="_blank"
                                                class="btn btn-dark btn-sm rounded-pill">

                                                <i class="bi bi-github"></i>

                                                <?= $lang['github'] ?>

                                            </a>

                                        <?php endif; ?>

                                        <?php if (!empty($project['demo_url'])): ?>

                                            <a
                                                href="<?= htmlspecialchars(
                                                            $project['demo_url'],
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>"
                                                target="_blank"
                                                class="btn btn-outline-primary btn-sm rounded-pill">

                                                <i class="bi bi-box-arrow-up-right"></i>

                                                <?= $lang['demo'] ?>

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