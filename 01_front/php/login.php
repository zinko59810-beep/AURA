<?php
require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

$last_student_id = $_SESSION['last_student_id'] ?? '';
unset($_SESSION['last_student_id']);

if (isset($_SESSION['user_id'])) {
    header("Location: profile.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $students_id = trim($_POST["students_id"] ?? "");
    $password = $_POST["password"] ?? "";

    $dbh = db_connect();

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


        header("Location: profile.php");
        exit;
    }

    $message = "Student ID or Password is wrong";
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="container py-5" style="max-width:500px;">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="rounded-circle bg-primary text-white mx-auto mb-3 d-flex align-items-center justify-content-center"
                        style="width:80px;height:80px;font-size:34px;">
                        A
                    </div>
                    <h2 class="fw-bold">AURA Study</h2>
                    <p class="text-muted">Login to continue</p>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-danger rounded-4">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Student ID</label>
                        <input
                            type="text"
                            name="students_id"
                            class="form-control rounded-3"
                            placeholder="student id"
                            value="<?= htmlspecialchars($last_student_id) ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Password</label>
                        <input type="password" name="password" class="form-control rounded-3" placeholder="password">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold">
                        Login
                    </button>
                </form>

                <p class="text-center text-muted mt-4 mb-0">
                    New user?
                    <a href="register.php" class="fw-bold text-decoration-none">Create account</a>
                </p>
            </div>
        </div>
    </div>
</body>

</html>