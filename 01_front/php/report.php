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

$answer_id = $_GET['answer_id'] ?? 0;
$user_id = $_GET['user_id'] ?? 0;

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $reason = trim($_POST['reason'] ?? "");

    if ($reason === "") {
        $message = "理由を入力してください";
    } else {
        $stmt = $dbh->prepare("
            INSERT INTO reports
            (
                reporter_id,
                reported_user_id,
                answer_id,
                reason,
                status
            )
            VALUES
            (
                ?, ?, ?, ?, 'pending'
            )
        ");

        $stmt->execute([
            $_SESSION['user_id'],
            $user_id,
            $answer_id,
            $reason
        ]);

        $message = "通報しました";
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>Report</title>

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="/AURA/01_front/css/style.css">
</head>

<body class="aura-app">
    <?php $currentPage = 'home'; ?>
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<div class="container py-4 aura-content" style="max-width:500px;">

        <a href="javascript:history.back()" class="text-decoration-none text-muted">
            ← 戻る
        </a>

        <div class="card border-0 shadow-sm rounded-4 mt-3">

            <div class="card-body p-4">

                <h4 class="fw-bold mb-4">
                    <i class="bi bi-flag"></i> Report
                </h4>

                <?php if (!empty($message)): ?>

                    <div class="alert alert-success rounded-4">
                        <?= htmlspecialchars($message) ?>
                    </div>

                <?php endif; ?>

                <form method="POST">

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            通報理由
                        </label>

                        <textarea
                            name="reason"
                            class="form-control rounded-3"
                            rows="5"
                            placeholder="理由を入力してください"
                            required></textarea>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-danger w-100 rounded-pill py-3">
                        通報する
                    </button>

                </form>

            </div>

        </div>

    </div>

</body>

</html>