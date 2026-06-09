<?php
require_once(__DIR__ . '/../../02_backend/db/db_.php');

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

            header("Location: login.php");
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
    <title>Register</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
<div class="container py-5" style="max-width:500px;">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <h2 class="text-center fw-bold mb-4">Create Account</h2>

            <?php if (!empty($message)): ?>
                <div class="alert alert-danger rounded-4">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Student ID</label>
                    <input type="text" name="students_id" class="form-control rounded-3" placeholder="student id">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Name</label>
                    <input type="text" name="name" class="form-control rounded-3" placeholder="Your name">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" name="email" class="form-control rounded-3" placeholder="email">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Password</label>
                    <input type="password" name="password" class="form-control rounded-3" placeholder="password">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control rounded-3" placeholder="Confirm password">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Class</label>
                    <select name="class_id" class="form-select rounded-3">
                        <option value="">クラスを選択してください</option>
                        <option value="1">SK2A</option>
                        <option value="2">SK2B</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold">
                    Register
                </button>
            </form>

            <p class="text-center text-muted mt-4 mb-0">
                Have already an account?
                <a href="login.php" class="fw-bold text-decoration-none">Login here</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
