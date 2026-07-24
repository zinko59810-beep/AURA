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
        a.*,
        q.title
    FROM answers a
    JOIN questions q
        ON a.question_id = q.id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
");

$stmt->execute([
    $_SESSION['user_id']
]);

$answers = $stmt->fetchAll();
?>

<!DOCTYPE html>

<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>自分の回答</title>


    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/AURA/01_front/css/style.css">


</head>

<body class="bg-light aura-app" >
    <?php $currentPage = 'profile'; ?>
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<div class="container py-4 aura-content" style="max-width:700px;">


        <div class="d-flex align-items-center justify-content-between mb-4">
            <a href="/AURA/profile" class="text-decoration-none text-muted">
                ←
            </a>

            <h4 class="fw-bold mb-0">
                <i class="bi bi-chat-left-text"></i> 自分の回答
            </h4>

            <span></span>
        </div>

        <?php if (empty($answers)): ?>

            <div class="alert alert-info rounded-4">
                投稿した回答がありません。
            </div>

        <?php else: ?>

            <?php foreach ($answers as $answer): ?>

                <div class="card border-0 shadow-sm rounded-4 mb-3">

                    <div class="card-body">

                        <h5 class="fw-bold">
                            <?= htmlspecialchars($answer['title']) ?>
                        </h5>

                        <p class="text-muted">
                            <?= htmlspecialchars(
                                mb_strimwidth(
                                    $answer['content'],
                                    0,
                                    120,
                                    "..."
                                )
                            ) ?>
                        </p>

                        <div class="d-flex justify-content-between align-items-center">

                            <small class="text-muted">
                                <?= htmlspecialchars($answer['created_at']) ?>
                            </small>

                            <a href="/AURA/question_detail?id=<?= $answer['question_id'] ?>"
                                class="btn btn-sm btn-primary rounded-pill">
                                質問を見る
                            </a>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>


    </div>
</body>

</html>