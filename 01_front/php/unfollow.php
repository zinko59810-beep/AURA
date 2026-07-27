<?php
require_once(__DIR__ . '/../../02_backend/db/db_.php');
require_once __DIR__ . '/../../lang/language.php';



if (!isset($_SESSION['user_id'])) {
    header("Location: /AURA/login");
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

header("Location: /AURA/profile?id=" . urlencode($following_id));
exit;