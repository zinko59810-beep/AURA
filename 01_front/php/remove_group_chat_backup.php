<?php

require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

if(!isset($_SESSION['user_id'])){
    exit;
}

$dbh = db_connect();

$group_id = $_POST['id'] ?? 0;


// Remove only myself from group

$stmt = $dbh->prepare("
DELETE FROM group_members
WHERE group_id = ?
AND user_id = ?
");

$stmt->execute([
    $group_id,
    $_SESSION['user_id']
]);


header("Location: chat_list.php");
exit;