<?php
require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$following_id = $_GET['id'] ?? 0;

$dbh = db_connect();

$stmt = $dbh->prepare("
    DELETE FROM follows
    WHERE follower_id = ?
    AND following_id = ?
");

$stmt->execute([
    $_SESSION['user_id'],
    $following_id
]);

header("Location: profile.php?id=" . urlencode($following_id));
exit;