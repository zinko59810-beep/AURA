<?php
require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = $_POST['id'];
    $dbh = db_connect();

    // ✅ Check ownership
    $stmt = $dbh->prepare("SELECT user_id FROM questions WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();

    if (!$post || $post['user_id'] != $_SESSION['user_id']) {
        exit("You cannot delete this post");
    }

    // ✅ Delete post
    $stmt = $dbh->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: question_list.php");
    exit;
}