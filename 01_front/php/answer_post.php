<?php
require_once(__DIR__ . '/../../02_backend/db/db_.php');
require_once __DIR__ . '/../../lang/language.php';



if (!isset($_SESSION['user_id'])) {
    header("Location: /AURA/auth");
    exit;
}

$question_id = $_GET['question_id'] ?? 0;

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
    FROM questions
    WHERE id = ?
");

$stmt->execute([$question_id]);
$question = $stmt->fetch();

if (!$question) {
    echo "Question Not Found";
    exit;
}

$is_own_question =
    (int)$question['user_id'] === (int)$_SESSION['user_id'];

$message = "";





if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $content = trim($_POST["content"] ?? "");

        /*
     * Question copy detection
     * 問題文・コードの丸ごとコピーを防止
     */
    $normalize_text = function ($text) {

        $text = (string)$text;

        // PowerShell の PS ...> / >> を除去
        $text = preg_replace(
            '/^\s*(?:PS\s+[^\r\n>]*>\s*|>>\s*)/mi',
            '',
            $text
        );

        $text = mb_strtolower(
            trim($text),
            'UTF-8'
        );

        // 空白・改行を除去
        $text = preg_replace(
            '/\s+/u',
            '',
            $text
        );

        return $text;
    };

    $answer_normalized =
        $normalize_text($content);

    $question_parts = [
        $question['goal'] ?? '',
        $question['tried'] ?? '',
        $question['problem'] ?? ''
    ];

    $full_question =
        $normalize_text(
            implode('', $question_parts)
        );

    $question_copy_detected = false;

    $answer_length =
        mb_strlen(
            $answer_normalized,
            'UTF-8'
        );

    $question_length =
        mb_strlen(
            $full_question,
            'UTF-8'
        );

    /*
     * ① 回答そのものが問題文の中に含まれている
     * 40文字以上ならコピーと判断
     */
    if (
        $answer_length >= 40 &&
        mb_strpos(
            $full_question,
            $answer_normalized,
            0,
            'UTF-8'
        ) !== false
    ) {
        $question_copy_detected = true;
    }

    /*
     * ② 問題文の40文字ブロックを複数コピーしていないか
     */
    if (
        !$question_copy_detected &&
        $answer_length >= 40 &&
        $question_length >= 40
    ) {

        $chunk_size = 40;
        $step = 20;
        $matched_chunks = 0;

        for (
            $i = 0;
            $i <= $question_length - $chunk_size;
            $i += $step
        ) {

            $chunk =
                mb_substr(
                    $full_question,
                    $i,
                    $chunk_size,
                    'UTF-8'
                );

            if (
                mb_strpos(
                    $answer_normalized,
                    $chunk,
                    0,
                    'UTF-8'
                ) !== false
            ) {

                $matched_chunks++;

                if ($matched_chunks >= 2) {
                    $question_copy_detected = true;
                    break;
                }
            }
        }
    }

    /*
     * ③ 各項目をそのままコピーしていないか
     */
    if (!$question_copy_detected) {

        foreach ($question_parts as $part) {

            $part_normalized =
                $normalize_text($part);

            if (
                mb_strlen(
                    $part_normalized,
                    'UTF-8'
                ) >= 40 &&
                mb_strpos(
                    $answer_normalized,
                    $part_normalized,
                    0,
                    'UTF-8'
                ) !== false
            ) {

                $question_copy_detected = true;
                break;
            }
        }
    }


    if ($is_own_question) {

        http_response_code(403);

        $message =
            "自分の質問には回答できません。";

    } elseif ($content === "") {

        $message =
            "回答を入力してください";

    } elseif ($question_copy_detected) {

        $message =
            "問題文をそのままコピーして回答することはできません。自分の言葉で回答してください。";

    } else {
        $dbh->beginTransaction();

        try {
            $is_copy = 0;

            $old_stmt = $dbh->query("
                SELECT content
                FROM answers
            ");

            $old_answers = $old_stmt->fetchAll();

            foreach ($old_answers as $old) {

                similar_text(
                    mb_strtolower($content),
                    mb_strtolower($old['content']),
                    $percent
                );

                if ($percent >= 90 && mb_strlen($content) >= 50) {
                    $is_copy = 1;
                    break;
                }
            }

            if ($is_copy == 1) {

                $dbh->rollBack();

                $message = "⚠ コピー回答は禁止されています。自分の言葉で回答してください。";
            } else {

                $stmt = $dbh->prepare("
            INSERT INTO answers
            (
                question_id,
                user_id,
                content,
                is_copy
            )
            VALUES
            (
                ?, ?, ?, ?
            )
        ");

                $stmt->execute([
                    $question_id,
                    $_SESSION['user_id'],
                    $content,
                    0
                ]);

                $point_stmt = $dbh->prepare("
            UPDATE users
            SET points = points + 5
            WHERE id = ?
        ");

                $point_stmt->execute([
                    $_SESSION['user_id']
                ]);

                $level_stmt = $dbh->prepare("
            UPDATE users
            SET level =
                CASE
                    WHEN points >= 1500 THEN 'Master'
                    WHEN points >= 700 THEN 'Gold'
                    WHEN points >= 300 THEN 'Silver'
                    WHEN points >= 100 THEN 'Bronze'
                    ELSE 'Beginner'
                END
            WHERE id = ?
        ");

                $level_stmt->execute([
                    $_SESSION['user_id']
                ]);

                $dbh->commit();

                header("Location: /AURA/question_detail?id=" . urlencode($question_id));
                exit;
            }
        } catch (Exception $e) {
            if ($dbh->inTransaction()) {
                $dbh->rollBack();
            }

            $message = "回答の投稿に失敗しました";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $currentLang ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang['answer_post'] ?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/AURA/01_front/css/style.css">

    <!-- AURA PWA -->
    <link rel="manifest" href="/AURA/manifest.webmanifest">
    <meta name="theme-color" content="#2563eb">
    <meta name="application-name" content="AURA Study">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="icon" type="image/png" sizes="32x32" href="/AURA/01_front/assets/icons/favicon-32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/AURA/01_front/assets/icons/favicon-16.png">
    <link rel="apple-touch-icon" href="/AURA/01_front/assets/icons/apple-touch-icon.png">
</head>

<body class="aura-app">
    <?php $currentPage = 'home'; ?>
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <div class="container py-5 aura-content" style="max-width:650px;">

        <?php if ($is_own_question): ?>

            <div class="card border-0 shadow-sm rounded-4 text-center">
                <div class="card-body p-5">

                    <div class="own-question-icon mx-auto mb-4">
                        <i class="bi bi-chat-square-text-fill"></i>
                    </div>

                    <h3 class="fw-bold mb-3">
                        <?= $lang['cannot_answer_own_question'] ?>
                    </h3>

                    <p class="text-muted mb-4">
                        <?= $lang['wait_for_answers'] ?>
                    </p>

                    <a
                        href="/AURA/question_detail?id=<?= urlencode($question_id) ?>"
                        class="btn btn-primary rounded-pill px-5 py-2">
                        <i class="bi bi-arrow-left me-1"></i>
                        <?= $lang['back_to_question'] ?>
                    </a>

                </div>
            </div>

        <?php else: ?>

            <div class="d-flex align-items-center justify-content-between mb-4">
                <a
                    href="/AURA/question_detail?id=<?= urlencode($question_id) ?>"
                    class="text-decoration-none text-muted">
                    ←
                </a>

                <h4 class="fw-bold mb-0">
                    <i class="bi bi-chat-left-text"></i>
                    <?= $lang['answer_post'] ?>
                </h4>

                <span></span>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body question-copy-zone">
                    <h5 class="fw-bold">
                        <?= htmlspecialchars($question['title']) ?>
                    </h5>

                    <p class="text-muted mb-0">
                        <?= nl2br(htmlspecialchars($question['problem'])) ?>
                    </p>
                </div>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-danger rounded-4">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">

                    <form
                        action="/AURA/answer_post?question_id=<?= urlencode($question_id) ?>"
                        method="POST">

                        <div class="mb-4">

                            <label class="form-label fw-bold">
                                <?= $lang['answer_content'] ?>
                            </label>

                            <textarea
                                name="content"
                                class="form-control rounded-3"
                                rows="6"
                                placeholder="<?= $lang['answer_placeholder'] ?>"></textarea>

                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary w-100 rounded-pill py-3 fw-bold">

                            <?= $lang['submit_answer'] ?>

                        </button>

                        <a
                            href="/AURA/question_detail?id=<?= urlencode($question_id) ?>"
                            class="btn btn-outline-secondary w-100 rounded-pill mt-2">

                            <?= $lang['back'] ?>

                        </a>

                    </form>

                </div>
            </div>

        <?php endif; ?>

    </div>

    <script src="/AURA/01_front/js/question-copy-guard.js" defer></script>
    <script src="/AURA/pwa-register.js" defer></script>
</body>

</html>