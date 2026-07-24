<?php
require_once(__DIR__ . '/../../02_backend/db/db_.php');
require_once __DIR__ . '/../../lang/language.php';


$message = "";
$is_success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $students_id = trim($_POST["students_id"] ?? "");
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
    $class_id = $_POST["class_id"] ?? "";

    if ($students_id === "" || $name === "" || $email === "" || $password === "" || $class_id === "") {
        $message = "全部入力してください";
    } elseif ($password !== $confirm_password) {
        $message = "Password does not match";
    } else {
        $dbh = db_connect();

        $check_stmt = $dbh->prepare("
            SELECT id
            FROM users
            WHERE students_id = ? OR email = ?
            LIMIT 1
        ");
        $check_stmt->execute([$students_id, $email]);

        if ($check_stmt->fetch()) {
            $message = "この学生IDまたはメールは既に登録されています";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

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
                    ?, ?, ?, ?, ?
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
            header("Location: login.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8') ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $lang['register_title'] ?></title>

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

    <link rel="stylesheet"
        href="/AURA/01_front/css/style.css">
</head>

<body class="bg-light">

    <div class="container py-5" style="max-width:500px;">

        <div class="card border-0 shadow-sm rounded-4">

            <div class="card-body p-4">

                <!-- ==========================
                     Page Title
                =========================== -->
                <h2 class="text-center fw-bold mb-4">
                    <?= $lang['create_account'] ?>
                </h2>

                <!-- ==========================
                     Error Message
                =========================== -->
                <?php if (!empty($message)): ?>

                    <div class="alert alert-danger rounded-4">

                        <?= htmlspecialchars(
                            $message,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </div>

                <?php endif; ?>

                <!-- ==========================
                     Register Form
                =========================== -->
                <form action="/AURA/register" method="POST">

                    <!-- Student ID -->
                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            <?= $lang['student_id'] ?>
                        </label>

                        <input
                            type="text"
                            name="students_id"
                            class="form-control rounded-3"
                            placeholder="<?= $lang['student_id_placeholder'] ?>">

                    </div>

                    <!-- Name -->
                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            <?= $lang['name'] ?>
                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control rounded-3"
                            placeholder="<?= $lang['name_placeholder'] ?>">

                    </div>

                    <!-- Email -->
                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            <?= $lang['email'] ?>
                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control rounded-3"
                            placeholder="<?= $lang['email_placeholder'] ?>">

                    </div>

                    <!-- Password -->
                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            <?= $lang['password'] ?>
                        </label>

                        <input
                            type="password"
                            name="password"
                            class="form-control rounded-3"
                            placeholder="<?= $lang['password_placeholder'] ?>">

                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            <?= $lang['confirm_password'] ?>
                        </label>

                        <input
                            type="password"
                            name="confirm_password"
                            class="form-control rounded-3"
                            placeholder="<?= $lang['confirm_password_placeholder'] ?>">

                    </div>

                    <!-- Class -->
                    <div class="mb-4">

                        <label class="form-label fw-bold">
                            <?= $lang['class_label'] ?>
                        </label>

                        <select
                            name="class_id"
                            class="form-select rounded-3">

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

                    </div>

                    <!-- Register Button -->
                    <button
                        type="submit"
                        class="btn btn-primary w-100 rounded-pill py-3 fw-bold">

                        <?= $lang['register_button'] ?>

                    </button>

                </form>

                <!-- ==========================
                     Login Link
                =========================== -->
                <p class="text-center text-muted mt-4 mb-0">

                    <?= $lang['already_have_account'] ?>

                    <a
                        href="/AURA/login"
                        class="fw-bold text-decoration-none">

                        <?= $lang['login_here'] ?>

                    </a>

                </p>

            </div>

        </div>

    </div>

</body>

</html>