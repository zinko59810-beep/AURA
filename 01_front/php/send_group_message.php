
<?php

require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

if (!isset($_SESSION['user_id'])) {
    exit;
}

$dbh = db_connect();

$group_id = (int)($_POST['group_id'] ?? 0);
$message  = trim($_POST['message'] ?? '');

if ($group_id <= 0 || $message === '') {
    header("Location: /AURA/group_chat?id=" . $group_id);
    exit;
}


// Check group member

$check = $dbh->prepare("
    SELECT COUNT(*)
    FROM group_members
    WHERE group_id = ?
    AND user_id = ?
");

$check->execute([
    $group_id,
    $_SESSION['user_id']
]);

if ($check->fetchColumn() == 0) {
    exit("No Permission");
}


// Insert message

$stmt = $dbh->prepare("
    INSERT INTO group_messages
    (
        group_id,
        sender_id,
        message
    )
    VALUES
    (
        ?,
        ?,
        ?
    )
");

$stmt->execute([
    $group_id,
    $_SESSION['user_id'],
    $message
]);


// Back to group chat

header(
    "Location: /AURA/group_chat?id=" . $group_id
);

exit;

