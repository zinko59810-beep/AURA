<?php
require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = "";
$is_success = false;

$dbh = db_connect();

$message = "";
$is_success = false;

$notice_stmt = $dbh->prepare("
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = ?
    AND is_read = 0
");
$notice_stmt->execute([$_SESSION['user_id']]);
$notice_count = $notice_stmt->fetch()['total'];

$chat_stmt = $dbh->prepare("
    SELECT COUNT(*) AS total
    FROM direct_messages
    WHERE receiver_id = ?
    AND is_read = 0
");
$chat_stmt->execute([$_SESSION['user_id']]);
$chat_count = $chat_stmt->fetch()['total'];



if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $user_id = $_SESSION['user_id'];

    $subject_id = $_POST["subject_id"] ?? "";
    $title = $_POST["title"] ?? "";
    $goal = $_POST["goal"] ?? "";
    $tried = $_POST["tried"] ?? "";
    $problem = $_POST["problem"] ?? "";

    if ($subject_id === "" || $title === "" || $goal === "" || $tried === "" || $problem === "") {
        $message = "全部入力してください";
    } else {

        $is_copy = 0;

        $new_text = $title . " " . $goal . " " . $tried . " " . $problem;

        $old_stmt = $dbh->query("
        SELECT title, goal, tried, problem
        FROM questions
    ");

        $old_questions = $old_stmt->fetchAll();

        foreach ($old_questions as $old_q) {

            $old_text =
                $old_q['title'] . " " .
                $old_q['goal'] . " " .
                $old_q['tried'] . " " .
                $old_q['problem'];

            similar_text(
                mb_strtolower($new_text),
                mb_strtolower($old_text),
                $percent
            );

            if ($percent >= 80) {
                $is_copy = 1;
                break;
            }
        }

        $stmt = $dbh->prepare("
        INSERT INTO questions
        (
            user_id,
            subject_id,
            title,
            goal,
            tried,
            problem,
            is_copy
        )
        VALUES
        (
            ?, ?, ?, ?, ?, ?, ?
        )
    ");

        $stmt->execute([
            $user_id,
            $subject_id,
            $title,
            $goal,
            $tried,
            $problem,
            $is_copy
        ]);

        if ($is_copy == 1) {
            $message = "⚠ 既存の投稿と似ています。投稿は完了しましたが、ポイント対象外になります。";
        } else {
            $message = "質問を投稿しました";
        }

        $is_success = true;
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>質問投稿</title>

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="bg-light aura-app">
    <?php $currentPage = 'post'; ?>
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<div class="container py-4 aura-content" style="max-width:700px;">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <!-- <a href="question_list.php" class="text-decoration-none text-muted">
                ←戻る
            </a> -->

            <h4 class="fw-bold mb-0">
                <i class="bi bi-question-circle"></i> 質問投稿
            </h4>

            <span></span>
        </div>

        <?php if (!empty($message)): ?>

            <div class="alert <?= $is_success ? 'alert-success' : 'alert-danger' ?> rounded-4">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>

        <div class="">

            <div class="card-body p-1">

                <form action="question_post.php" method="POST">

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-pencil-square"></i> タイトル
                        </label>

                        <input
                            type="text"
                            name="title"
                            class="form-control rounded-3"
                            placeholder="質問タイトルを入力してください">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-tags"></i> 科目
                        </label>

                        <select
                            name="subject_id"
                            class="form-select rounded-3">

                            <option value="">
                                科目を選択してください
                            </option>

                            <option value="1">PHP</option>
                            <option value="2">Java</option>
                            <option value="3">Database</option>
                            <option value="4">Network</option>
                            <option value="5">Laravel</option>
                            <option value="6">HTML/CSS</option>
                            <option value="7">Javascript</option>

                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-bullseye"></i> やりたいこと
                        </label>

                        <textarea
                            name="goal"
                            class="form-control rounded-3"
                            rows="3"
                            placeholder="何をしたいですか？"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-tools"></i> 試したこと
                        </label>

                        <textarea
                            name="tried"
                            class="form-control rounded-3"
                            rows="3"
                            placeholder="何を試しましたか？"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-question-circle"></i> 分からない点
                        </label>

                        <textarea
                            name="problem"
                            class="form-control rounded-3"
                            rows="3"
                            placeholder="どこが分かりませんか？"></textarea>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary w-100 rounded-pill py-3 fw-bold">
                        投稿する
                    </button>

                    <a
                        href="question_list.php"
                        class="btn btn-outline-secondary w-100 rounded-pill mt-2">
                        戻る
                    </a>

                </form>

            </div>

        </div>

    </div>
</body>

</html>