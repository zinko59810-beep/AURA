<?php
$mode = $_GET['mode'] ?? '';

require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

/* Language */
if (
    isset($_GET['lang']) &&
    in_array($_GET['lang'], ['ja', 'en'], true)
) {
    $_SESSION['lang'] = $_GET['lang'];
}

$currentLang = $_SESSION['lang'] ?? 'ja';

$lang = require __DIR__ . '/../../lang/' . $currentLang . '.php';

$dbh = db_connect();

/* -----------------------------
   LOGIN
------------------------------*/

$login_message = "";
$register_message = "";

$last_student_id = $_SESSION['last_student_id'] ?? "";
unset($_SESSION['last_student_id']);

if (isset($_SESSION['user_id'])) {
    header("Location: /AURA/profile");
    exit;
}

/* -----------------------------
   LOGIN FORM
------------------------------*/

if (isset($_POST['login'])) {

    $students_id = trim($_POST['students_id'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $dbh->prepare("
        SELECT *
        FROM users
        WHERE students_id = ?
    ");

    $stmt->execute([$students_id]);

    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['students_id'] = $user['students_id'];
        $_SESSION['name'] = $user['name'];

        $active_stmt = $dbh->prepare("
            UPDATE users
            SET last_active_at = NOW()
            WHERE id = ?
        ");

        $active_stmt->execute([
            $_SESSION['user_id']
        ]);

        header("Location: /AURA/profile");
        exit;
    }

    $login_message = "学生IDまたはパスワードが間違っています。";
}


/* -----------------------------
   REGISTER FORM
------------------------------*/

if (isset($_POST['register'])) {

    $students_id = trim($_POST["students_id"] ?? "");
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
    $class_id = $_POST["class_id"] ?? "";

    if (
        $students_id == "" ||
        $name == "" ||
        $email == "" ||
        $password == "" ||
        $class_id == ""
    ) {

        $register_message = "全部入力してください";
    } elseif ($password != $confirm_password) {

        $register_message = "パスワードが一致していませ";
    } else {

        $check_stmt = $dbh->prepare("
            SELECT id
            FROM users
            WHERE students_id = ?
            OR email = ?
            LIMIT 1
        ");

        $check_stmt->execute([
            $students_id,
            $email
        ]);

        if ($check_stmt->fetch()) {

            $register_message = "この学生IDまたはメールは既に登録されています";
        } else {

            $password_hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $dbh->prepare("
                INSERT INTO users
                (
                    students_id,
                    name,
                    email,
                    password_hash,
                    class_id
                )
                VALUES
                (
                    ?,?,?,?,?
                )
            ");

            $stmt->execute([
                $students_id,
                $name,
                $email,
                $password_hash,
                $class_id
            ]);

            $_SESSION['last_student_id'] = $students_id;

            header("Location: /AURA/auth");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $currentLang ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $lang['site_title'] ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="/AURA/01_front/php/auth.css">
</head>

<body>
    <div
        class="auth-container <?= ($mode == 'register') ? 'right-panel-active' : '' ?>"
        id="container">

        <!-- ==========================
            LOGIN FORM
        =========================== -->

        <div class="form-container sign-in-container">

            <form method="POST">

                <h1><?= $lang['login'] ?></h1>

                <span><?= $lang['login_subtitle'] ?></span>

                <?php if (!empty($login_message)): ?>

                    <div class="alert alert-danger mt-3 mb-3">

                        <?= htmlspecialchars($login_message) ?>

                    </div>

                <?php endif; ?>

                <input
                    type="text"
                    name="students_id"
                    placeholder="<?= $lang['student_id'] ?>"
                    value="<?= htmlspecialchars($last_student_id) ?>"
                    required>

                <input
                    type="password"
                    name="password"
                    placeholder="<?= $lang['password'] ?>"
                    required>

                <button
                    type="submit"
                    name="login">

                    <?= $lang['login'] ?>

                </button>

            </form>

        </div>

        <!-- ==========================
            REGISTER FORM
    =========================== -->

<div class="form-container sign-up-container">

    <form method="POST">

        <h1><?= $lang['register_title'] ?></h1>

        <span><?= $lang['register_subtitle'] ?></span>

        <?php if (!empty($register_message)): ?>

            <div class="alert alert-danger mt-3 mb-3">

                <?= htmlspecialchars($register_message) ?>

            </div>

        <?php endif; ?>

        <input
            type="text"
            name="students_id"
            placeholder="<?= $lang['student_id'] ?>"
            required>

        <input
            type="text"
            name="name"
            placeholder="<?= $lang['name'] ?>"
            required>

        <input
            type="email"
            name="email"
            placeholder="<?= $lang['email'] ?>"
            required>

        <input
            type="password"
            name="password"
            placeholder="<?= $lang['password'] ?>"
            required>

        <input
            type="password"
            name="confirm_password"
            placeholder="<?= $lang['confirm_password'] ?>"
            required>

        <select
            name="class_id"
            required>

            <option value="">
                <?= $lang['select_class'] ?>
            </option>

            <option value="1">SK1A</option>
            <option value="2">SK2A</option>
            <option value="3">SK3A</option>
            <option value="4">IE1A</option>
            <option value="5">IE2A</option>
            <option value="6">IE3A</option>
            <option value="7">IE4A</option>
            <option value="8">SE1A</option>
            <option value="9">SE2A</option>

        </select>

        <button
            type="submit"
            name="register">

            <?= $lang['register_button'] ?>

        </button>

    </form>

</div>


<!--==========================
            OVERLAY
===========================-->

<div class="overlay-container">

    <div class="overlay">

        <div class="overlay-panel overlay-left">

            <h1><?= $lang['welcome_back'] ?></h1>

            <p>
                <?= $lang['welcome_back_text'] ?>
            </p>

            <button
                class="ghost"
                id="signIn">

                <?= $lang['login'] ?>

            </button>

        </div>


        <div class="overlay-panel overlay-right">

            <h1><?= $lang['welcome'] ?></h1>

            <p>
                <?= $lang['welcome_text'] ?>
            </p>

            <button
                class="ghost"
                id="signUp">

                <?= $lang['register_title'] ?>

            </button>

        </div>

    </div>

</div>

</div>

<script src="/AURA/01_front/php/auth.js"></script>

</body>

</html>