<?php

require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /AURA/login");
    exit;
}

$dbh = db_connect();

$user_id = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Get Group ID
|--------------------------------------------------------------------------
*/
$group_id = $_POST['id'] ?? $_POST['group_id'] ?? $_GET['id'] ?? 0;

if (!$group_id) {
    header("Location: /AURA/chat");
    exit;
}

/*
|--------------------------------------------------------------------------
| Check Group
|--------------------------------------------------------------------------
*/
$stmt = $dbh->prepare("
    SELECT id, created_by
    FROM chat_groups
    WHERE id = ?
");

$stmt->execute([$group_id]);

$group = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$group) {
    header("Location: /AURA/chat");
    exit;
}

/*
|--------------------------------------------------------------------------
| Check Admin
|--------------------------------------------------------------------------
|
| Group creator is also treated as admin.
|
*/
$is_admin = false;

/* Check created_by */
if ((int)$group['created_by'] === (int)$user_id) {
    $is_admin = true;
}

/* Check group_members role */
if (!$is_admin) {

    $stmt = $dbh->prepare("
        SELECT role
        FROM group_members
        WHERE group_id = ?
        AND user_id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $group_id,
        $user_id
    ]);

    $role = $stmt->fetchColumn();

    if ($role === 'admin') {
        $is_admin = true;
    }
}

/*
|--------------------------------------------------------------------------
| Permission
|--------------------------------------------------------------------------
*/
if (!$is_admin) {
    header("Location: /AURA/chat");
    exit;
}

/*
|--------------------------------------------------------------------------
| Delete Group Messages
|--------------------------------------------------------------------------
*/
$stmt = $dbh->prepare("
    DELETE FROM group_messages
    WHERE group_id = ?
");

$stmt->execute([$group_id]);

/*
|--------------------------------------------------------------------------
| Delete Group Members
|--------------------------------------------------------------------------
*/
$stmt = $dbh->prepare("
    DELETE FROM group_members
    WHERE group_id = ?
");

$stmt->execute([$group_id]);

/*
|--------------------------------------------------------------------------
| Delete Group
|--------------------------------------------------------------------------
*/
$stmt = $dbh->prepare("
    DELETE FROM chat_groups
    WHERE id = ?
");

$stmt->execute([$group_id]);

/*
|--------------------------------------------------------------------------
| Back to Chat
|--------------------------------------------------------------------------
*/
header("Location: /AURA/chat");
exit;