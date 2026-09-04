
<?php

require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

if (!isset($_SESSION['user_id'])) {
    exit;
}

$dbh = db_connect();

$group_id = $_GET['id'] ?? 0;

if (!$group_id) {
    exit("Group ID not found");
}


/*
|--------------------------------------------------------------------------
| Group Info
|--------------------------------------------------------------------------
*/

$stmt = $dbh->prepare("
    SELECT *
    FROM chat_groups
    WHERE id = ?
");

$stmt->execute([$group_id]);

$group = $stmt->fetch();

if (!$group) {
    exit("Group not found");
}


/*
|--------------------------------------------------------------------------
| Check Member / Admin
|--------------------------------------------------------------------------
*/

$role_stmt = $dbh->prepare("
    SELECT role
    FROM group_members
    WHERE group_id = ?
    AND user_id = ?
");

$role_stmt->execute([
    $group_id,
    $_SESSION['user_id']
]);

$user_role = $role_stmt->fetchColumn();

if ($user_role === false) {
    exit("Access Denied");
}

$is_admin = ($user_role === 'admin');


/*
|--------------------------------------------------------------------------
| Messages
|--------------------------------------------------------------------------
*/

$msg_stmt = $dbh->prepare("
    SELECT
        gm.*,
        u.name,
        u.profile_image
    FROM group_messages gm
    JOIN users u
        ON gm.sender_id = u.id
    WHERE gm.group_id = ?
    ORDER BY gm.created_at ASC
");

$msg_stmt->execute([$group_id]);

$messages = $msg_stmt->fetchAll();

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8') ?>
    </title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <script
        type="module"
        src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"
    ></script>

</head>


<body class="bg-light">


<div
    class="container py-4 aura-content"
    style="max-width:700px;"
>

    <div
        class="card shadow-sm rounded-4 d-flex flex-column"
        style="height:100vh;"
    >


        <!-- HEADER -->

        <div class="card-header bg-white d-flex align-items-center">

            <a
                href="/AURA/chat"
                class="text-decoration-none text-muted me-3"
            >
                <i class="bi bi-arrow-left fs-4"></i>
            </a>


            <!-- Group Icon -->

            <div
                class="rounded-circle bg-primary text-white
                       d-flex align-items-center justify-content-center me-3"
                style="width:45px;height:45px;"
            >

                <i class="bi bi-people-fill"></i>

            </div>


            <!-- Group Name -->

            <div class="flex-grow-1">

                <strong>
                    <?= htmlspecialchars(
                        $group['name'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </strong>

                <br>

                <small class="text-muted">
                    Group Chat
                </small>

            </div>


            <!-- Members -->

            <a
                href="/AURA/group_members?id=<?= $group_id ?>"
                class="btn btn-light rounded-circle"
                title="Members"
            >
                <i class="bi bi-people-fill"></i>
            </a>


            <!-- Add Member -->

            <?php if ($is_admin): ?>

                <a
                    href="/AURA/add_member?id=<?= $group_id ?>"
                    class="btn btn-light rounded-circle ms-2"
                    title="Add Member"
                >
                    <i class="bi bi-person-plus-fill"></i>
                </a>

            <?php endif; ?>


            <!-- Leave Group -->

            <?php if (!$is_admin): ?>

                <a
                    href="/AURA/leave_group?id=<?= $group_id ?>"
                    class="btn btn-light rounded-circle ms-2"
                    title="Leave Group"
                    onclick="return confirm('Leave this group?')"
                >
                    <i class="bi bi-box-arrow-right"></i>
                </a>

            <?php endif; ?>


        </div>


        <!-- MESSAGE AREA -->

        <div
            id="group-chat-box"
            class="card-body overflow-auto"
        >

            <?php foreach ($messages as $msg): ?>

                <?php
                $is_me =
                    $msg['sender_id'] == $_SESSION['user_id'];
                ?>


                <div
                    class="d-flex mb-3
                    <?= $is_me
                        ? 'justify-content-end'
                        : 'justify-content-start'
                    ?>"
                >

                    <div
                        class="d-flex align-items-end gap-2
                        <?= $is_me ? 'flex-row-reverse' : '' ?>"
                    >


                        <!-- Profile Image -->

                        <?php if (!empty($msg['profile_image'])): ?>

                            <img
                                src="/AURA/<?= htmlspecialchars(
                                    $msg['profile_image'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                class="rounded-circle"
                                width="42"
                                height="42"
                                style="object-fit:cover;"
                            >

                        <?php else: ?>

                            <div
                                class="rounded-circle bg-secondary text-white
                                d-flex align-items-center justify-content-center"
                                style="width:42px;height:42px;"
                            >

                                <i class="bi bi-person"></i>

                            </div>

                        <?php endif; ?>


                        <!-- Message -->

                        <div>

                            <div
                                class="small fw-bold text-muted mb-1"
                            >

                                <?= htmlspecialchars(
                                    $msg['name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </div>


                            <div
                                style="
                                    max-width:300px;
                                    padding:12px 16px;
                                    border-radius:18px;
                                    <?= $is_me
                                        ? 'background:#0d6efd;color:white;'
                                        : 'background:#f1f1f1;color:#333;'
                                    ?>
                                    box-shadow:0 2px 6px rgba(0,0,0,.15);
                                "
                            >

                                <?= nl2br(
                                    htmlspecialchars(
                                        $msg['message'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    )
                                ) ?>

                            </div>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>


        <!-- SEND AREA -->

        <div class="card-footer bg-white">

            <form
                method="POST"
                action="/AURA/send_group_message"
                class="d-flex gap-2 position-relative"
            >

                <input
                    type="hidden"
                    name="group_id"
                    value="<?= $group_id ?>"
                >


                <!-- Emoji -->

                <button
                    type="button"
                    id="emojiBtn"
                    class="btn btn-light rounded-circle"
                >
                    😊
                </button>


                <!-- Message -->

                <input
                    type="text"
                    id="message"
                    name="message"
                    class="form-control rounded-pill"
                    placeholder="Write a message..."
                    autocomplete="off"
                    required
                >


                <!-- Send -->

                <button
                    type="submit"
                    class="btn btn-primary rounded-circle"
                    style="width:45px;height:45px;"
                >

                    <i class="bi bi-send-fill"></i>

                </button>


                <!-- Emoji Picker -->

                <emoji-picker
                    id="emojiPicker"
                    style="
                        display:none;
                        position:absolute;
                        bottom:70px;
                        right:20px;
                        z-index:1000;
                    "
                ></emoji-picker>

            </form>


            <script>

                const emojiBtn =
                    document.getElementById("emojiBtn");

                const emojiPicker =
                    document.getElementById("emojiPicker");

                const messageInput =
                    document.getElementById("message");


                emojiBtn.onclick = function (e) {

                    e.preventDefault();

                    if (emojiPicker.style.display === "block") {

                        emojiPicker.style.display = "none";

                    } else {

                        emojiPicker.style.display = "block";

                    }

                };


                emojiPicker.addEventListener(
                    "emoji-click",
                    function (event) {

                        messageInput.value +=
                            event.detail.unicode;

                        messageInput.focus();

                        emojiPicker.style.display = "none";

                    }
                );


                document.addEventListener(
                    "click",
                    function (e) {

                        if (
                            !emojiPicker.contains(e.target) &&
                            e.target !== emojiBtn
                        ) {

                            emojiPicker.style.display = "none";

                        }

                    }
                );

            </script>

        </div>

    </div>

</div>


<!-- Auto Load Messages -->

<script>

function loadGroupMessages() {

    fetch(
        "/AURA/group_messages?id=<?= $group_id ?>"
    )

    .then(res => res.text())

    .then(data => {

        const box =
            document.getElementById(
                "group-chat-box"
            );

        box.innerHTML = data;

        box.scrollTop =
            box.scrollHeight;

    })

    .catch(error => {

        console.error(
            "Message loading error:",
            error
        );

    });

}


loadGroupMessages();

setInterval(
    loadGroupMessages,
    2000
);

</script>


</body>

</html>
```
