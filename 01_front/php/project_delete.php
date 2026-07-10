<?php
require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /AURA/login");
    exit;
}

$project_id = $_GET['id'] ?? 0;

$dbh = db_connect();

$stmt = $dbh->prepare("
    SELECT *
    FROM projects
    WHERE id = ?
    AND user_id = ?
");

$stmt->execute([
    $project_id,
    $_SESSION['user_id']
]);

$project = $stmt->fetch();

if (!$project) {
    echo "Project Not Found";
    exit;
}

if (!empty($project['image_path']) && file_exists($project['image_path'])) {
    unlink($project['image_path']);
}

$delete_stmt = $dbh->prepare("
    DELETE FROM projects
    WHERE id = ?
    AND user_id = ?
");

$delete_stmt->execute([
    $project_id,
    $_SESSION['user_id']
]);

header("Location: profile.php");
exit;