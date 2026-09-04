<?php

require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /AURA/login");
    exit;
}

$dbh = db_connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $group_name = trim($_POST['group_name'] ?? '');

    if ($group_name === '') {
        exit("Group name is required");
    }

    /*
    |--------------------------------------------------------------------------
    | Create Group
    |--------------------------------------------------------------------------
    */

    $stmt = $dbh->prepare("
        INSERT INTO chat_groups
        (
            name,
            created_by
        )
        VALUES
        (
            ?,
            ?
        )
    ");

    $stmt->execute([
        $group_name,
        $_SESSION['user_id']
    ]);

    $group_id = $dbh->lastInsertId();


    /*
    |--------------------------------------------------------------------------
    | Add Creator as Admin
    |--------------------------------------------------------------------------
    */

    $member = $dbh->prepare("
        INSERT INTO group_members
        (
            group_id,
            user_id,
            role
        )
        VALUES
        (
            ?,
            ?,
            'admin'
        )
    ");

    $member->execute([
        $group_id,
        $_SESSION['user_id']
    ]);


    /*
    |--------------------------------------------------------------------------
    | Go to Group Chat
    |--------------------------------------------------------------------------
    */

    header(
        "Location: /AURA/group_chat?id=" . $group_id
    );

    exit;
}

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Create Group</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="container py-5">

    <div class="container">

        <div class="mb-3">

            <a
                href="/AURA/chat"
                class="btn btn-light rounded-pill"
            >
                ← Back
            </a>

        </div>

        <h3 class="mb-4">
            Create Group
        </h3>


        <form
            method="post"
            action="/AURA/create_group"
        >

            <input
                type="text"
                name="group_name"
                class="form-control mb-3"
                placeholder="Group Name"
                required
            >

            <button
                type="submit"
                class="btn btn-primary"
            >
                Create
            </button>

        </form>

    </div>

</body>

</html>