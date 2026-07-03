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

$stmt = $dbh->prepare("
    SELECT
        q.*,
        s.subject_name
    FROM questions q
    JOIN subjects s
        ON q.subject_id = s.id
    WHERE q.user_id = ?
    ORDER BY q.created_at DESC
");

$stmt->execute([
    $_SESSION['user_id']
]);

$questions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>自分の質問</title>

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="bg-light aura-app" >
    <?php $currentPage = 'profile'; ?>
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<div class="container py-4 aura-content" style="max-width:700px;">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <a href="profile.php" class="text-decoration-none text-muted">
                ←
            </a>

            <h4 class="fw-bold mb-0">
                <i class="bi bi-journal-text"></i> 自分の質問
            </h4>

            <span></span>
        </div>

        <?php if (empty($questions)): ?>

            <div class="alert alert-info rounded-4">
                投稿した質問がありません。
            </div>

        <?php else: ?>

            <?php foreach ($questions as $question): ?>

                <div class="card border-0 shadow-sm rounded-4 mb-3">

                    <div class="card-body">

                        <span class="badge bg-primary mb-2">
                            <?= htmlspecialchars($question['subject_name']) ?>
                        </span>

                        <h5 class="fw-bold">
                            <a href="question_detail.php?id=<?= $question['id'] ?>"
                                class="text-decoration-none text-dark">
                                <?= htmlspecialchars($question['title']) ?>
                            </a>
                        </h5>

                        <p class="text-muted">
                            <?= htmlspecialchars(
                                mb_strimwidth(
                                    $question['goal'],
                                    0,
                                    90,
                                    "..."
                                )
                            ) ?>
                        </p>

                        <div class="d-flex justify-content-between align-items-center">

                            <small class="text-muted">
                                <?= htmlspecialchars($question['created_at']) ?>
                            </small>

                            <a href="question_detail.php?id=<?= $question['id'] ?>"
                                class="btn btn-sm btn-primary rounded-pill">
                                詳細
                            </a>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>
</body>

</html>