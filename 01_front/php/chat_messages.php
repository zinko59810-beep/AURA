<?php

require_once(__DIR__ . '/../../02_backend/db/db_.php');
require_once __DIR__ . '/../../lang/language.php';


if (!isset($_SESSION['user_id'])) {
    exit;
}

$other_user_id = $_GET['id'] ?? 0;

$dbh = db_connect();

$stmt = $dbh->prepare("
    SELECT *
    FROM direct_messages
    WHERE
        (
            sender_id = ?
            AND receiver_id = ?
        )
        OR
        (
            sender_id = ?
            AND receiver_id = ?
        )
    ORDER BY created_at ASC
");

$stmt->execute([
    $_SESSION['user_id'],
    $other_user_id,
    $other_user_id,
    $_SESSION['user_id']
]);

$messages = $stmt->fetchAll();

foreach ($messages as $msg):

    $is_me = $msg['sender_id'] == $_SESSION['user_id'];
?>

<div class="d-flex mb-3 <?= $is_me ? 'justify-content-end' : 'justify-content-start' ?>">

    <div
        class="<?= $is_me ? 'bg-primary text-white' : 'bg-light text-dark' ?>"
        style="
        max-width:70%;
        padding:10px 14px;
        border-radius:18px;
        box-shadow:0 2px 8px rgba(0,0,0,.08);
    ">

        <?= nl2br(htmlspecialchars($msg['message'])) ?>

        <div class="<?= $is_me ? 'text-white-50' : 'text-muted' ?> small mt-1">
            <?= htmlspecialchars($msg['created_at']) ?>
        </div>

        <?php if ($is_me): ?>
            <div class="small text-white-50">
                <?= $msg['is_read'] == 1 ? '✓✓ Seen' : '✓ Sent' ?>
            </div>
        <?php endif; ?>

    </div>

</div>

<?php endforeach; ?>