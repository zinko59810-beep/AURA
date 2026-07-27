<?php

require_once(__DIR__ . '/../../02_backend/db/db_.php');
require_once __DIR__ . '/../../lang/language.php';



if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$following_id = $_GET['id'] ?? 0;

if ($following_id == $_SESSION['user_id']) {
    header("Location: /AURA/profile");
    exit;
}

$dbh = db_connect();

$check_stmt = $dbh->prepare("
    SELECT *
    FROM follows
    WHERE follower_id = ?
    AND following_id = ?
");

$check_stmt->execute([
    $_SESSION['user_id'],
    $following_id
]);

// まだフォローしていない場合だけ insert
if (!$check_stmt->fetch()) {

    $stmt = $dbh->prepare("
        INSERT INTO follows
        (
            follower_id,
            following_id
        )
        VALUES
        (
            ?, ?
        )
    ");

    $stmt->execute([
        $_SESSION['user_id'],
        $following_id
    ]);

    // Notification
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
        $following_id,
        $_SESSION['user_id'],
        'followed you'
    ]);
}

header("Location: /AURA/profile?id=" . urlencode($following_id));
exit;