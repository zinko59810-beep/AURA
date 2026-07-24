<?php
require_once(__DIR__ . '/../../02_backend/db/db_.php');
require_once __DIR__ . '/../../lang/language.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$answer_id = $_GET['answer_id'] ?? 0;

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
        a.id AS answer_id,
        a.question_id,
        a.user_id AS answer_user_id,
        a.is_best_hint,
        q.user_id AS question_user_id
    FROM answers a
    JOIN questions q
        ON a.question_id = q.id
    WHERE a.id = ?
");

$stmt->execute([$answer_id]);
$data = $stmt->fetch();

if (!$data) {
    echo "Answer Not Found";
    exit;
}

$question_id = $data['question_id'];

if ((int)$data['question_user_id'] !== (int)$_SESSION['user_id']) {
    echo "権限がありません";
    exit;
}

if ((int)$data['is_best_hint'] === 1) {
    header("Location: /AURA/question_detail?id=" . urlencode($question_id));
    exit;
}

$dbh->beginTransaction();

try {
    $prev_stmt = $dbh->prepare("
        SELECT user_id
        FROM answers
        WHERE question_id = ?
          AND is_best_hint = 1
        LIMIT 1
    ");
    $prev_stmt->execute([$question_id]);
    $previous_best = $prev_stmt->fetch();

    if ($previous_best) {
        $minus_stmt = $dbh->prepare("
            UPDATE users
            SET points = GREATEST(points - 20, 0)
            WHERE id = ?
        ");
        $minus_stmt->execute([$previous_best['user_id']]);
    }
    if ($previous_best) {
        $prev_level_stmt = $dbh->prepare("
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

        $prev_level_stmt->execute([
            $previous_best['user_id']
        ]);
    }

    $reset_stmt = $dbh->prepare("
        UPDATE answers
        SET is_best_hint = 0
        WHERE question_id = ?
    ");
    $reset_stmt->execute([$question_id]);

    $best_stmt = $dbh->prepare("
        UPDATE answers
        SET is_best_hint = 1
        WHERE id = ?
    ");
    $best_stmt->execute([$answer_id]);

    $answer_user_stmt = $dbh->prepare("
    SELECT
        user_id,
        is_copy
    FROM answers
    WHERE id = ?
");

    $answer_user_stmt->execute([
        $answer_id
    ]);

    $answer_user = $answer_user_stmt->fetch();





    if ((int)$answer_user['is_copy'] === 0) {

    $point_stmt = $dbh->prepare("
        UPDATE users
        SET points = points + 20
        WHERE id = ?
    ");

    $point_stmt->execute([
        $answer_user['user_id']
    ]);
}
    // Level auto update for answer owner
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
        $answer_user['user_id']
    ]);

    $notify_stmt = $dbh->prepare("
    INSERT INTO notifications
    (
        user_id,
        message
    )
    VALUES
    (
        ?, ?
    )
");


    $notify_stmt->execute([
        $data['answer_user_id'],
        'あなたの回答がベスト回答に選ばれました'
    ]);

    $dbh->commit();
} catch (Exception $e) {
    $dbh->rollBack();
    echo "Best Answerの設定に失敗しました";
    exit;
}


header("Location: question_detail.php?id=" . urlencode($question_id));
exit;
