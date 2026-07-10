<?php
$mode = $_GET['mode'] ?? '';
require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

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

    $login_message = "Student ID or Password is wrong";
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

        $register_message = "Password does not match";
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
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AURA Study</title>

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

                <h1>Sign In</h1>

                <span>Use your Student Account</span>

                <?php if (!empty($login_message)): ?>

                    <div class="alert alert-danger mt-3 mb-3">

                        <?= htmlspecialchars($login_message) ?>

                    </div>

                <?php endif; ?>


                <input
                    type="text"
                    name="students_id"
                    placeholder="Student ID"
                    value="<?= htmlspecialchars($last_student_id) ?>"
                    required>

                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    required>

                <button
                    type="submit"
                    name="login">

                    Login

                </button>

            </form>

        </div>





        <!-- ==========================
            REGISTER FORM
    =========================== -->

        <div class="form-container sign-up-container">

            <form method="POST">
            <h1>Create Account</h1>

            <span>Create your AURA Study account</span>

            <?php if (!empty($register_message)): ?>

                <div class="alert alert-danger mt-3 mb-3">

                    <?= htmlspecialchars($register_message) ?>

                </div>

            <?php endif; ?>

            <input
                type="text"
                name="students_id"
                placeholder="Student ID"
                required>

            <input
                type="text"
                name="name"
                placeholder="Name"
                required>

            <input
                type="email"
                name="email"
                placeholder="Email"
                required>

            <input
                type="password"
                name="password"
                placeholder="Password"
                required>

            <input
                type="password"
                name="confirm_password"
                placeholder="Confirm Password"
                required>

            <select
                name="class_id"
                required>

                <option value="">Select Class</option>

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

                Register

            </button>

            </form>

        </div>


        <!--==========================
            OVERLAY
    ===========================-->

        <div class="overlay-container">

            <div class="overlay">

                <div class="overlay-panel overlay-left">

                    <h1>Welcome Back!</h1>

                    <p>
                        Already have an account?
                        Login here and continue
                        your AURA Study journey.
                    </p>

                    <button
                        class="ghost"
                        id="signIn">

                        Login

                    </button>

                </div>


                <div class="overlay-panel overlay-right">

                    <h1>Hello Friend!</h1>

                    <p>
                        Enter your personal details and start your study with us.
                    </p>

                    <button
                        class="ghost"
                        id="signUp">

                        Register

                    </button>

                </div>

            </div>

        </div>

    </div>

    <script src="/AURA/01_front/php/auth.js"></script>

</body>

</html>