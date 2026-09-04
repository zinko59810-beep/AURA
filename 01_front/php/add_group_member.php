<?php

require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

if(!isset($_SESSION['user_id'])){
    exit;
}


$group_id=(int)($_GET['id'] ?? 0);

$dbh=db_connect();


// Check admin

$stmt=$dbh->prepare("
SELECT *
FROM chat_groups
WHERE id=?
AND created_by=?
");


$stmt->execute([
    $group_id,
    $_SESSION['user_id']
]);


if(!$stmt->fetch()){
    exit("No Permission");
}


// Get users not in group

$user_stmt=$dbh->prepare("

SELECT id,name
FROM users
WHERE id NOT IN
(
    SELECT user_id
    FROM group_members
    WHERE group_id=?
)

AND id != ?

");


$user_stmt->execute([
    $group_id,
    $_SESSION['user_id']
]);


$users=$user_stmt->fetchAll();



if($_SERVER['REQUEST_METHOD']=="POST"){


    $user_id=$_POST['user_id'];


    $insert=$dbh->prepare("
    INSERT INTO group_members
    (
        group_id,
        user_id
    )
    VALUES
    (?,?)
    ");


    $insert->execute([
        $group_id,
        $user_id
    ]);


  header("Location: /AURA/group_chat?id=".$group_id);
exit;

}

?>
<div class="container py-4">

<h4>
Add Member
</h4>


<form method="POST">


<select name="user_id"
class="form-select mb-3">


<?php foreach($users as $user): ?>


<option value="<?= $user['id'] ?>">

<?= htmlspecialchars($user['name']) ?>

</option>


<?php endforeach; ?>


</select>


<button class="btn btn-primary">

Add

</button>


</form>

</div>