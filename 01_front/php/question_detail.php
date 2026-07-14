<?php
require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? 0;

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
        u.name,
        u.students_id,
        s.subject_name
    FROM questions q
    JOIN users u ON q.user_id = u.id
    JOIN subjects s ON q.subject_id = s.id
    WHERE q.id = ?
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

$stmt->execute([$id]);
$question = $stmt->fetch();

if (!$question) {
    echo "Question Not Found";
    exit;
}
$helpful_stmt = $dbh->prepare("
    SELECT COUNT(*) AS total
    FROM helpfuls
    WHERE answer_id = ?
");

$answer_stmt = $dbh->prepare("
    SELECT
        a.*,
        u.name
    FROM answers a
    JOIN users u ON a.user_id = u.id
    WHERE a.question_id = ?
    ORDER BY a.created_at ASC
");



$answer_stmt->execute([$id]);
$answers = $answer_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>質問詳細</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/AURA/01_front/css/style.css">
</head>

<body class="bg-light aura-app">
    <?php $currentPage = 'home'; ?>
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<div class="container py-4 aura-content">
        <?php if (!empty($_SESSION['warning'])): ?>

            <div class="alert alert-warning rounded-4">
                <?= htmlspecialchars($_SESSION['warning']) ?>
            </div>

            <?php unset($_SESSION['warning']); ?>

        <?php endif; ?>

        <a href="/AURA/profile" class="text-decoration-none text-muted">
            ← 戻る
        </a>

        <div class="card border-0 shadow-sm rounded-4 mt-3 mb-4">
            <div class="card-body">

                <span class="badge bg-primary mb-2">
                    <?= htmlspecialchars($question['subject_name']) ?>
                </span>

                <h4 class="fw-bold">
                    <?= htmlspecialchars($question['title']) ?>
                </h4>

                <small class="text-muted">
                    <?= htmlspecialchars($question['name']) ?>
                    ・<?= htmlspecialchars($question['created_at']) ?>
                </small>

                <hr>

                <h6 class="fw-bold">やりたいこと</h6>
                <p><?= nl2br(htmlspecialchars($question['goal'])) ?></p>

                <h6 class="fw-bold mt-3">試したこと</h6>
                <p><?= nl2br(htmlspecialchars($question['tried'])) ?></p>

                <h6 class="fw-bold mt-3">分からない点</h6>
                <p><?= nl2br(htmlspecialchars($question['problem'])) ?></p>

            </div>
        </div>

        <div class="d-grid mb-4">
            <a href="/AURA/answer_post?question_id=<?= $question['id'] ?>"
                class="btn btn-primary rounded-pill">
                回答する
            </a>
        </div>

        <h5 class="fw-bold mb-3">回答一覧</h5>

        <?php if (empty($answers)): ?>
            

            <div class="alert alert-info rounded-4">
                
                まだ回答がありません。
            </div>

        <?php else: ?>

            <?php foreach ($answers as $answer): ?>

                <?php
                $helpful_stmt->execute([
                    $answer['id']
                ]);

                $helpful_count = $helpful_stmt->fetch()['total'];
                ?>

                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-body">

                        <div class="d-flex justify-content-between">
                            <strong><?= htmlspecialchars($answer['name']) ?></strong>
                            <small class="text-muted">
                                <?= htmlspecialchars($answer['created_at']) ?>
                            </small>
                        </div>
                        <?php if ($answer['user_id'] != $_SESSION['user_id']): ?>

                            <a href="/AURA/helpful?answer_id=<?= $answer['id'] ?>"
                                class="btn btn-sm btn-outline-success rounded-pill mt-2">
                                👍 役に立った
                                (<?= $helpful_count ?>)
                            </a>

                        <?php endif; ?>

                        <?php if ($answer['is_best_hint'] == 1): ?>
                            <span class="badge bg-warning text-dark mt-2">
                                <i class="bi bi-star-fill"></i> ベスト回答
                            </span>
                        <?php endif; ?>

                        <p class="mt-3 mb-0">
                            <?= nl2br(htmlspecialchars($answer['content'])) ?>
                        </p>
                        <?php if ($answer['user_id'] != $_SESSION['user_id']): ?>
                            <a href="/AURA/report?answer_id=<?= $answer['id'] ?>&user_id=<?= $answer['user_id'] ?>"
                                class="btn btn-sm btn-outline-danger rounded-pill mt-2">
                                <i class="bi bi-flag"></i> Report
                            </a>
                        <?php endif; ?>

                        <?php if ($question['user_id'] == $_SESSION['user_id'] && $answer['is_best_hint'] == 0): ?>
                            <a href="/AURA/best_answer?answer_id=<?= $answer['id'] ?>&question_id=<?= $question['id'] ?>"
                                class="btn btn-sm btn-outline-warning rounded-pill mt-3">
                                ベスト回答にする
                            </a>
                        <?php endif; ?>

                    </div>
                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>
</body>

</html>