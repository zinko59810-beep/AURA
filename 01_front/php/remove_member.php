<?php

require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();


$dbh = db_connect();


$group_id = $_GET['group'];

$user_id = $_GET['user'];



// Check Admin

$stmt = $dbh->prepare("
SELECT role
FROM group_members
WHERE group_id=?
AND user_id=?
");


$stmt->execute([
    $group_id,
    $_SESSION['user_id']
]);


$role = $stmt->fetchColumn();


if($role != 'admin'){
    exit("No Permission");
}


// Remove

$delete = $dbh->prepare("
DELETE FROM group_members

WHERE group_id=?

AND user_id=?

AND role!='admin'
");


$delete->execute([
    $group_id,
    $user_id
]);


header(
"Location: group_members.php?id=".$group_id
);

exit;