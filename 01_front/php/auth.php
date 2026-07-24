<?php
$mode = $_GET['mode'] ?? '';
require_once(__DIR__ . '/../../02_backend/db/db_.php');

session_start();

$dbh = db_connect();

/* -----------------------------
   LOGIN
------------------------------*/

$login_message = $_SESSION['login_message'] ?? "";
$register_message = $_SESSION['register_message'] ?? "";

unset($_SESSION['login_message']);
unset($_SESSION['register_message']);

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

    // Error ဖြစ်ရင် Register Screen ကိုဆက်ပြရန်
    $mode = 'register';

    $students_id = trim($_POST["students_id"] ?? "");
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
    $class_id = $_POST["class_id"] ?? "";

    if (
        $students_id === "" ||
        $name === "" ||
        $email === "" ||
        $password === "" ||
        $confirm_password === "" ||
        $class_id === ""
    ) {

        $_SESSION['register_message'] = "全部入力してください";

        header("Location: /AURA/auth?mode=register");
        exit;
    } elseif ($password !== $confirm_password) {

        $_SESSION['register_message'] = "パスワードが一致していません";

        header("Location: /AURA/auth?mode=register");
        exit;
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

            $_SESSION['register_message'] =
                "この学生IDまたはメールは既に登録されています";

            header("Location: /AURA/auth?mode=register");
            exit;
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
                VALUES (?, ?, ?, ?, ?)
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

    <link rel="stylesheet" href="/AURA/01_front/css/auth.css">
</head>

<body>
    <div
        class="auth-container <?= ($mode == 'register') ? 'right-panel-active' : '' ?>"
        id="container">

        <!-- ==========================
            LOGIN FORM
    =========================== -->

        <div class="form-container sign-in-container">

            <form method="POST" action="/AURA/auth">

                <h1>ログイン</h1>

                <span>学生アカウントでログインしてください</span>

                <?php if (!empty($login_message)): ?>
                    <div class="alert alert-danger mt-3 mb-3">
                        <?= htmlspecialchars($login_message) ?>
                    </div>
                <?php endif; ?>

                <!-- <input
                    type="text"
                    name="students_id"
                    placeholder="学籍番号"
                    value="<?= htmlspecialchars($last_student_id) ?>"
                    required> -->
                <input
                    type="text"
                    name="students_id"
                    placeholder="学籍番号"
                    value=""
                    autocomplete="off"
                    required>

                <!-- <input
                    type="password"
                    name="password"
                    placeholder="パスワード"
                    required> -->
                <input
                    type="password"
                    name="password"
                    placeholder="パスワード"
                    value=""
                    autocomplete="new-password"
                    required>

                <button type="submit" name="login">
                    ログイン
                </button>

            </form>

        </div>





        <!-- ==========================
            REGISTER FORM
    =========================== -->

        <div class="form-container sign-up-container">

            <form method="POST"
                action="/AURA/auth?mode=register"
                autocomplete="off">

                <h1>アカウント登録</h1>

                <span>AURA Studyのアカウントを作成してください</span>

                <?php if (!empty($register_message)): ?>
                    <div class="alert alert-danger mt-3 mb-3">
                        <?= htmlspecialchars($register_message) ?>
                    </div>
                <?php endif; ?>

                <input
                    type="text"
                    name="register_students_id"
                    placeholder="学籍番号"
                    autocomplete="off"
                    autocorrect="off"
                    autocapitalize="off"
                    spellcheck="false"
                    required>

                <input
                    type="text"
                    name="register_name"
                    placeholder="氏名"
                    autocomplete="off"
                    required>

                <input
                    type="email"
                    name="register_email"
                    placeholder="メールアドレス"
                    autocomplete="off"
                    required>

                <input
                    type="password"
                    name="register_password"
                    placeholder="パスワード"
                    autocomplete="new-password"
                    required>

                <input
                    type="password"
                    name="register_confirm_password"
                    placeholder="パスワード確認"
                    autocomplete="new-password"
                    required>

                <select
                    name="class_id"
                    autocomplete="off"
                    required>

                    <option value="">クラスを選択してください</option>
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

                <button type="submit" name="register">
                    登録
                </button>

            </form>

        </div>


        <!--==========================
            OVERLAY
    ===========================-->

        <div class="overlay-container">

            <div class="overlay">

                <div class="overlay-panel overlay-left">

                    <h1>おかえりなさい</h1>

                    <p>
                        すでにアカウントをお持ちの方は、
                        ログインしてAURA Studyを
                        続けましょう。
                    </p>

                    <button
                        class="ghost"
                        id="signIn">

                        ログイン

                    </button>

                </div>


                <div class="overlay-panel overlay-right">

                    <h1>ようこそ</h1>

                    <p>
                        学生情報を入力して、
                        AURA Studyを
                        始めましょう。
                    </p>

                    <button
                        class="ghost"
                        id="signUp">

                        アカウント登録

                    </button>

                </div>

            </div>

        </div>

    </div>

    <script src="/AURA/01_front/php/auth.js"></script>

</body>

</html>