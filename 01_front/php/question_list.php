<?php
require_once(__DIR__ . '/../../02_backend/db/db_.php');
require_once __DIR__ . '/../../lang/language.php';



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
<html lang="<?= htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8') ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $lang['site_title'] ?></title>

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

    <?php $currentPage = 'home'; ?>
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <div class="container py-4 aura-content">

        <!-- ==========================
             Page Title
        =========================== -->
        <h3 class="fw-bold mb-4">
            <?= $lang['site_title'] ?>
        </h3>

        <!-- ==========================
             User Search Form
        =========================== -->
        <form method="GET" class="mb-4">

            <div class="input-group">

                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>

                <input
                    type="text"
                    name="keyword"
                    class="form-control"
                    placeholder="<?= $lang['search_user_placeholder'] ?>"
                    value="<?= htmlspecialchars(
                                $keyword ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>">

                <button
                    type="submit"
                    class="btn btn-primary">

                    <?= $lang['search'] ?>

                </button>

            </div>

        </form>

        <!-- ==========================
             User Search Results
        =========================== -->
        <?php if (($keyword ?? '') !== ''): ?>

            <h5 class="fw-bold mb-3">
                <?= $lang['search_results'] ?>
            </h5>

            <?php if (empty($users)): ?>

                <div class="alert alert-light rounded-4">
                    <?= $lang['user_not_found'] ?>
                </div>

            <?php else: ?>

                <?php foreach ($users as $search_user): ?>

                    <div class="card border-0 shadow-sm rounded-4 mb-3">

                        <div class="card-body d-flex align-items-center">

                            <!-- ==========================
                                 Profile Image
                            =========================== -->
                            <?php if (!empty($search_user['profile_image'])): ?>

                                <img
                                    src="<?= htmlspecialchars(
                                                $search_user['profile_image'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                    alt="<?= $lang['user_profile_image'] ?>"
                                    class="rounded-circle me-3"
                                    width="55"
                                    height="55"
                                    style="object-fit:cover;">

                            <?php else: ?>

                                <div
                                    class="rounded-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center"
                                    style="width:55px;height:55px;">

                                    <i class="bi bi-person"></i>

                                </div>

                            <?php endif; ?>

                            <!-- ==========================
                                 User Information
                            =========================== -->
                            <div class="flex-grow-1">

                                <h6 class="fw-bold mb-1">

                                    <?= htmlspecialchars(
                                        $search_user['name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </h6>

                                <small class="text-muted">

                                    <?= $lang['student_id_label'] ?>:

                                    <?= htmlspecialchars(
                                        $search_user['students_id'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </small>

                                <div>

                                    <span class="badge bg-primary">

                                        <?= htmlspecialchars(
                                            $search_user['level'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </span>

                                    <span class="text-muted small">

                                        <?= htmlspecialchars(
                                            $search_user['points'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                        <?= $lang['points_short'] ?>

                                    </span>

                                </div>

                            </div>

                            <a
                                href="/AURA/profile?id=<?= urlencode($search_user['id']) ?>"
                                class="btn btn-sm btn-primary rounded-pill">

                                <?= $lang['profile'] ?>

                            </a>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

            <hr class="my-4">

        <?php endif; ?>

        <!-- ==========================
             Question List
        =========================== -->
        <?php if (empty($questions)): ?>

            <div class="alert alert-info rounded-4">
                <?= $lang['no_questions'] ?>
            </div>

        <?php else: ?>

            <?php foreach ($questions as $question): ?>

                <div class="card border-0 shadow-sm rounded-4 mb-3 position-relative">

                    <div class="card-body">

                        <!-- ==========================
                             Delete Button
                        =========================== -->
                        <?php if (
                            isset($_SESSION['user_id'])
                            && $_SESSION['user_id'] == $question['user_id']
                        ): ?>

                            <form
                                action="/AURA/question_delete"
                                method="POST"
                                style="position:absolute; top:15px; right:15px;">

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= htmlspecialchars(
                                                $question['id'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>">

                                <button
                                    type="submit"
                                    class="delete-btn"
                                    aria-label="<?= htmlspecialchars($lang['delete_question']) ?>"
                                    onclick="return confirm('<?= htmlspecialchars($lang['confirm_delete_question'], ENT_QUOTES, 'UTF-8') ?>');">

                                    <i class="bi bi-trash"></i>

                                </button>

                            </form>

                        <?php endif; ?>

                        <span class="badge bg-primary mb-2">

                            <?= htmlspecialchars(
                                $question['subject_name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </span>

                        <h5 class="fw-bold">

                            <a
                                href="/AURA/question_detail?id=<?= urlencode($question['id']) ?>"
                                class="text-decoration-none text-dark">

                                <?= htmlspecialchars(
                                    $question['title'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </a>

                        </h5>

                        <a
                            href="/AURA/profile?id=<?= urlencode($question['user_id']) ?>"
                            class="fw-bold text-dark text-decoration-none">

                            <?= htmlspecialchars(
                                $question['name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </a>

                        <p class="text-muted mt-2">

                            <?= htmlspecialchars(
                                mb_strimwidth(
                                    $question['goal'],
                                    0,
                                    100,
                                    '...'
                                ),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </p>

                        <div class="d-flex justify-content-between align-items-center">

                            <small class="text-muted">

                                <?= htmlspecialchars(
                                    $question['created_at'] ?? $question['name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </small>

                            <a
                                href="/AURA/question_detail?id=<?= urlencode($question['id']) ?>"
                                class="btn btn-sm btn-primary rounded-pill">

                                <?= $lang['details'] ?>

                            </a>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</body>

</html>