<?php

require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

if (!isset($_SESSION['user_id'])) {
    exit;
}


$group_id = (int)($_GET['id'] ?? 0);


$dbh = db_connect();


// Group info

$stmt = $dbh->prepare("
    SELECT *
    FROM chat_groups
    WHERE id = ?
");

$stmt->execute([
    $group_id
]);


$group = $stmt->fetch();
$is_admin = $group['created_by'] == $_SESSION['user_id'];


if (!$group){
    exit("Group Not Found");
}


// Members

$member_stmt = $dbh->prepare("

SELECT
    u.id,
    u.name,
    u.profile_image

FROM group_members gm

JOIN users u
ON gm.user_id = u.id

WHERE gm.group_id = ?

ORDER BY u.name

");


$member_stmt->execute([
    $group_id
]);


$members = $member_stmt->fetchAll();

?>
<div class="container py-4"
style="max-width:700px;">


<div class="card shadow rounded-4">

<div class="card-body">


<h3 class="fw-bold">

👥 <?= htmlspecialchars($group['name']) ?>

</h3>


<hr>


<h5>
Members
</h5>


<?php foreach($members as $member): ?>


<div class="d-flex align-items-center mb-3">


<?php if($member['profile_image']): ?>

<img
src="<?= htmlspecialchars($member['profile_image']) ?>"
width="45"
height="45"
class="rounded-circle me-3">


<?php else: ?>


<div class="rounded-circle bg-primary text-white me-3
d-flex align-items-center justify-content-center"
style="width:45px;height:45px;">

<i class="bi bi-person"></i>

</div>


<?php endif; ?>



<strong>

<?= htmlspecialchars($member['name']) ?>

</strong>


</div>


<?php endforeach; ?>


</div>

</div>


</div>