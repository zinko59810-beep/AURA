<?php
require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$answer_id = $_GET['answer_id'] ?? 0;

$dbh = db_connect();

$answer_stmt = $dbh->prepare("
    SELECT
        user_id,
        is_copy
    FROM answers
    WHERE id = ?
");

$answer_stmt->execute([
    $answer_id
]);

$answer = $answer_stmt->fetch();

if (!$answer) {
    echo "Answer Not Found";
    exit;
}

if ((int)$answer['user_id'] === (int)$_SESSION['user_id']) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

$check_stmt = $dbh->prepare("
    SELECT *
    FROM helpfuls
    WHERE answer_id = ?
    AND user_id = ?
");

$check_stmt->execute([
    $answer_id,
    $_SESSION['user_id']
]);

if (!$check_stmt->fetch()) {

    $insert_stmt = $dbh->prepare("
        INSERT INTO helpfuls
        (
            answer_id,
            user_id
        )
        VALUES
        (
            ?, ?
        )
    ");

    $notify_stmt = $dbh->prepare("
    INSERT INTO notifications
    (
        user_id,
        sender_id,
        message
    )
    VALUES
    (
        ?, ?, ?
    )
");

    $notify_stmt->execute([
        $answer['user_id'],
        $_SESSION['user_id'],
        'あなたの回答が役に立ったと評価されました'
    ]);

    $insert_stmt->execute([
        $answer_id,
        $_SESSION['user_id']
    ]);

    if ((int)$answer['is_copy'] === 0) {

        $point_stmt = $dbh->prepare("
        UPDATE users
        SET points = points + 2
        WHERE id = ?
    ");

        $point_stmt->execute([
            $answer['user_id']
        ]);
    }
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
    $answer['user_id']
]);
}


header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
