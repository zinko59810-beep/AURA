<?php
require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

if (!isset($_SESSION['user_id'])) {
    exit;
}

$dbh = db_connect();

$group_id = $_GET['id'] ?? $_POST['group_id'] ?? 0;

if (!$group_id) {
    exit("Group ID not found");
}

/*
|--------------------------------------------------------------------------
| Check group access
|--------------------------------------------------------------------------
*/
$access = $dbh->prepare("
    SELECT 1
    FROM group_members
    WHERE group_id = ?
    AND user_id = ?
");
$access->execute([
    $group_id,
    $_SESSION['user_id']
]);

if (!$access->fetch()) {
    exit("Access Denied");
}


/*
|--------------------------------------------------------------------------
| Search User
|--------------------------------------------------------------------------
*/
$user = null;
$is_member = false;

if (isset($_POST['search'])) {

    $students_id = trim($_POST['students_id']);

    $stmt = $dbh->prepare("
        SELECT
            id,
            name,
            students_id,
            profile_image
        FROM users
        WHERE students_id = ?
    ");

    $stmt->execute([
        $students_id
    ]);

    $user = $stmt->fetch();

    if ($user) {

        $check = $dbh->prepare("
            SELECT 1
            FROM group_members
            WHERE group_id = ?
            AND user_id = ?
        ");

        $check->execute([
            $group_id,
            $user['id']
        ]);

        $is_member = (bool) $check->fetch();
    }
}


/*
|--------------------------------------------------------------------------
| Add Member
|--------------------------------------------------------------------------
*/
if (isset($_POST['add'])) {

    $member_id = $_POST['user_id'] ?? 0;

    if (!$member_id) {
        exit("User not found");
    }

    // Check duplicate member
    $check = $dbh->prepare("
        SELECT 1
        FROM group_members
        WHERE group_id = ?
        AND user_id = ?
    ");

    $check->execute([
        $group_id,
        $member_id
    ]);

    if ($check->fetch()) {
        exit("This user is already a member");
    }

    // Add member
    $stmt = $dbh->prepare("
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
            'member'
        )
    ");

    $stmt->execute([
        $group_id,
        $member_id
    ]);

    // Back to group chat
    header(
        "Location: /AURA/?url=group_chat&id=" . $group_id
    );
    exit;
}

?>


<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Add Member</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

</head>


<body class="container py-5">


<div class="container py-4">


    <!-- Back Button -->
    <div class="mb-3">

        <a
            href="/AURA/?url=group_chat&id=<?= $group_id ?>"
            class="btn btn-light rounded-pill">

            <i class="bi bi-arrow-left"></i>

            Back

        </a>

    </div>


    <h3 class="mb-4">
        Add Member
    </h3>


    <!-- Search Form -->
    <form method="post">

        <input
            type="hidden"
            name="group_id"
            value="<?= $group_id ?>">

        <input
            type="text"
            name="students_id"
            class="form-control mb-3"
            placeholder="Student ID"
            required>

        <button
            type="submit"
            name="search"
            class="btn btn-primary">

            <i class="bi bi-search"></i>

            Search

        </button>

    </form>


    <!-- Search Result -->
    <?php if ($user): ?>


        <div class="card mt-4">

            <div class="card-body d-flex align-items-center">


                <!-- Profile Image -->
                <?php if (!empty($user['profile_image'])): ?>

                    <img
                        src="/AURA/<?= htmlspecialchars(
                            $user['profile_image'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        width="60"
                        height="60"
                        class="rounded-circle me-3"
                        style="object-fit:cover;"
                        onerror="this.style.display='none';">

                <?php else: ?>

                    <div
                        class="rounded-circle bg-secondary text-white
                               d-flex align-items-center justify-content-center me-3"
                        style="width:60px;height:60px;">

                        <i class="bi bi-person fs-4"></i>

                    </div>

                <?php endif; ?>


                <!-- User Information -->
                <div>

                    <h5 class="mb-1">

                        <?= htmlspecialchars(
                            $user['name'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </h5>

                    <small class="text-muted">

                        Student ID :
                        <?= htmlspecialchars(
                            $user['students_id'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </small>

                </div>


                <!-- Add Button -->
                <form
                    method="post"
                    class="ms-auto">

                    <input
                        type="hidden"
                        name="group_id"
                        value="<?= $group_id ?>">

                    <input
                        type="hidden"
                        name="user_id"
                        value="<?= $user['id'] ?>">


                    <?php if ($is_member): ?>

                        <button
                            type="button"
                            class="btn btn-secondary"
                            disabled>

                            Already Member

                        </button>

                    <?php else: ?>

                        <button
                            type="submit"
                            name="add"
                            class="btn btn-success">

                            <i class="bi bi-person-plus"></i>

                            Add

                        </button>

                    <?php endif; ?>

                </form>


            </div>

        </div>


    <?php elseif (isset($_POST['search'])): ?>


        <div class="alert alert-danger mt-3">

            <i class="bi bi-exclamation-circle"></i>

            User not found.

        </div>


    <?php endif; ?>


</div>


</body>

</html>