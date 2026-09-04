<?php

require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

if(!isset($_SESSION['user_id'])){
    exit;
}

$dbh = db_connect();

$group_id = $_GET['id'] ?? 0;


$stmt = $dbh->prepare("
SELECT
    gm.id,
    gm.role,
    u.id AS user_id,
    u.name,
    u.students_id,
    u.profile_image

FROM group_members gm

JOIN users u
ON gm.user_id = u.id

WHERE gm.group_id = ?
");

$stmt->execute([
    $group_id
]);

$members = $stmt->fetchAll();

?>


<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>Group Members</title>


<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">


<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


</head>


<body class="bg-light">


<div class="container py-4">
    <div class="mb-3">
    <a href="/AURA/group_chat?id=<?= $group_id ?>"
       class="btn btn-primary rounded-pill mb-3">
    <i class="bi bi-arrow-left"></i>
        Back
    </a>
</div>



<h3 class="fw-bold mb-4">

<i class="bi bi-people-fill"></i>

Group Members

</h3>



<?php foreach($members as $member): ?>


<div class="card border-0 shadow-sm rounded-4 mb-3">


<div class="card-body d-flex align-items-center">


<?php if(!empty($member['profile_image'])): ?>

<img
src="/AURA/<?= htmlspecialchars($member['profile_image']) ?>"
width="50"
height="50"
class="rounded-circle"
style="object-fit:cover;">

<?php else: ?>

<div class="rounded-circle bg-secondary"
style="width:50px;height:50px;">
</div>

<?php endif; ?>



<div>

<h6 class="fw-bold mb-1">

<?= htmlspecialchars($member['name']) ?>


<?php if($member['role']=='admin'): ?>

<span class="badge bg-primary">

Admin

</span>

<?php endif; ?>


</h6>


<small class="text-muted">

Student ID:
<?= htmlspecialchars($member['students_id']) ?>

</small>


</div>


</div>


</div>



<?php endforeach; ?>


</div>


</body>

</html>