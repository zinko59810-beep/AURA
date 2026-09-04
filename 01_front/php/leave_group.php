<?php

require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();


$dbh = db_connect();


$group_id = $_GET['id'];


$stmt = $dbh->prepare("
DELETE FROM group_members
WHERE group_id = ?
AND user_id = ?
AND role != 'admin'
");

$stmt->execute([
    $group_id,
    $_SESSION['user_id']
]);

header("Location: /AURA/chat");
exit;