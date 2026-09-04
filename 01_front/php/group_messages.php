<?php

require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

if (!isset($_SESSION['user_id'])) {
    exit;
}

$group_id = $_GET['id'] ?? 0;

$dbh = db_connect();


$stmt = $dbh->prepare("
    SELECT
        gm.*,
        u.name

    FROM group_messages gm

    JOIN users u
    ON gm.sender_id = u.id

    WHERE gm.group_id = ?

    ORDER BY gm.created_at ASC
");


$stmt->execute([
    $group_id
]);


$messages = $stmt->fetchAll();


foreach ($messages as $msg):

    $is_me = $msg['sender_id'] == $_SESSION['user_id'];

?>

<div class="d-flex mb-3 
<?= $is_me ? 'justify-content-end' : 'justify-content-start' ?>">


<div>


<?php if(!$is_me): ?>

<div class="small text-primary fw-bold mb-1">

<?= htmlspecialchars($msg['name']) ?>

</div>

<?php endif; ?>



<div
class="<?= $is_me ? 'bg-primary text-white' : 'bg-light text-dark' ?>"
style="
max-width:70%;
padding:10px 14px;
border-radius:18px;
box-shadow:0 2px 8px rgba(0,0,0,.08);
">


<?= nl2br(
htmlspecialchars($msg['message'])
) ?>


<div 
class="<?= $is_me ? 'text-white-50' : 'text-muted' ?> small mt-1">

<?= htmlspecialchars($msg['created_at']) ?>

</div>


</div>


</div>


</div>


<?php endforeach; ?>