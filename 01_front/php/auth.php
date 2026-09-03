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

/* Flash messages */
$login_message = $_SESSION['login_message'] ?? '';
$register_message = $_SESSION['register_message'] ?? '';
$last_student_id = $_SESSION['last_student_id'] ?? '';

unset(
    $_SESSION['login_message'],
    $_SESSION['register_message'],
    $_SESSION['last_student_id']
);

if (isset($_SESSION['user_id'])) {
    header('Location: /AURA/profile');
    exit;
}

/* -----------------------------
   LOGIN
------------------------------ */
if (isset($_POST['login'])) {
    $students_id = trim($_POST['students_id'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($students_id === '' || $password === '') {
        $_SESSION['login_message'] = '学生IDとパスワードを入力してください。';
        header('Location: /AURA/auth');
        exit;
    }

    $stmt = $dbh->prepare(
        'SELECT *
         FROM users
         WHERE students_id = ?
         LIMIT 1'
    );
    $stmt->execute([$students_id]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['students_id'] = $user['students_id'];
        $_SESSION['name'] = $user['name'];

        $active_stmt = $dbh->prepare(
            'UPDATE users
             SET last_active_at = NOW()
             WHERE id = ?'
        );
        $active_stmt->execute([$user['id']]);

        header('Location: /AURA/profile');
        exit;
    }

    $_SESSION['login_message'] = '学生IDまたはパスワードが間違っています。';
    header('Location: /AURA/auth');
    exit;
}

/* -----------------------------
   REGISTER
------------------------------ */
if (isset($_POST['register'])) {
    $students_id = trim($_POST['register_students_id'] ?? '');
    $name = trim($_POST['register_name'] ?? '');
    $email = trim($_POST['register_email'] ?? '');
    $password = $_POST['register_password'] ?? '';
    $confirm_password = $_POST['register_confirm_password'] ?? '';
    $class_id = trim($_POST['class_id'] ?? '');

    if (
        $students_id === '' ||
        $name === '' ||
        $email === '' ||
        $password === '' ||
        $confirm_password === '' ||
        $class_id === ''
    ) {
        $_SESSION['register_message'] = '全部入力してください。';
        header('Location: /AURA/auth?mode=register');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_message'] = '正しいメールアドレスを入力してください。';
        header('Location: /AURA/auth?mode=register');
        exit;
    }

    if ($password !== $confirm_password) {
        $_SESSION['register_message'] = 'パスワードが一致していません。';
        header('Location: /AURA/auth?mode=register');
        exit;
    }

    $check_stmt = $dbh->prepare(
        'SELECT id
         FROM users
         WHERE students_id = ?
            OR email = ?
         LIMIT 1'
    );
    $check_stmt->execute([$students_id, $email]);

    if ($check_stmt->fetch()) {
        $_SESSION['register_message'] =
            'この学生IDまたはメールは既に登録されています。';

        header('Location: /AURA/auth?mode=register');
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $dbh->prepare(
            'INSERT INTO users
                (students_id, name, email, password_hash, class_id)
             VALUES (?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $students_id,
            $name,
            $email,
            $password_hash,
            $class_id
        ]);

    } catch (PDOException $e) {

        $mysqlError =
            isset($e->errorInfo[1])
                ? (int)$e->errorInfo[1]
                : 0;

        if ($mysqlError === 1062) {

            $_SESSION['register_message'] =
                'この学生IDまたはメールアドレスは既に登録されています。';

            header('Location: /AURA/auth?mode=register');
            exit;
        }

        throw $e;
    }

    $_SESSION['last_student_id'] = $students_id;

    header('Location: /AURA/auth');
    exit;
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8') ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($lang['site_title'], ENT_QUOTES, 'UTF-8') ?></title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="/AURA/01_front/css/auth.css">

    <!-- AURA PWA -->
    <link rel="manifest" href="/AURA/manifest.webmanifest">
    <meta name="theme-color" content="#2563eb">
    <meta name="application-name" content="AURA Study">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="icon" type="image/png" sizes="32x32" href="/AURA/01_front/assets/icons/favicon-32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/AURA/01_front/assets/icons/favicon-16.png">
    <link rel="apple-touch-icon" href="/AURA/01_front/assets/icons/apple-touch-icon.png">
</head>

<body>
    <div
        class="auth-container <?= $mode === 'register' ? 'right-panel-active' : '' ?>"
        id="container">

        <!-- LOGIN FORM -->
        <div class="form-container sign-in-container">
            <form method="POST" action="/AURA/auth" autocomplete="off">
                <h1><?= htmlspecialchars($lang['login'], ENT_QUOTES, 'UTF-8') ?></h1>

                <span>
                    <?= htmlspecialchars($lang['login_subtitle'], ENT_QUOTES, 'UTF-8') ?>
                </span>

                <?php if ($login_message !== ''): ?>
                    <div class="alert alert-danger mt-3 mb-3">
                        <?= htmlspecialchars($login_message, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <input
                    type="text"
                    name="students_id"
                    placeholder="<?= htmlspecialchars($lang['student_id'], ENT_QUOTES, 'UTF-8') ?>"
                    value="<?= htmlspecialchars($last_student_id, ENT_QUOTES, 'UTF-8') ?>"
                    autocomplete="username"
                    required>

                <input
                    type="password"
                    name="password"
                    placeholder="<?= htmlspecialchars($lang['password'], ENT_QUOTES, 'UTF-8') ?>"
                    autocomplete="current-password"
                    required>

                <button type="submit" name="login">
                    <?= htmlspecialchars($lang['login'], ENT_QUOTES, 'UTF-8') ?>
                </button>
            </form>
        </div>

        <!-- REGISTER FORM -->
        <div class="form-container sign-up-container">
            <form
                method="POST"
                action="/AURA/auth?mode=register"
                autocomplete="off">

                <h1>
                    <?= htmlspecialchars($lang['register_title'], ENT_QUOTES, 'UTF-8') ?>
                </h1>

                <span>
                    <?= htmlspecialchars($lang['register_subtitle'], ENT_QUOTES, 'UTF-8') ?>
                </span>

                <?php if ($register_message !== ''): ?>
                    <div class="alert alert-danger mt-3 mb-3">
                        <?= htmlspecialchars($register_message, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <input
                    type="text"
                    name="register_students_id"
                    placeholder="<?= htmlspecialchars($lang['student_id'], ENT_QUOTES, 'UTF-8') ?>"
                    autocomplete="off"
                    autocorrect="off"
                    autocapitalize="off"
                    spellcheck="false"
                    required>

                <input
                    type="text"
                    name="register_name"
                    placeholder="<?= htmlspecialchars($lang['name'], ENT_QUOTES, 'UTF-8') ?>"
                    autocomplete="off"
                    required>

                <input
                    type="email"
                    name="register_email"
                    placeholder="<?= htmlspecialchars($lang['email'], ENT_QUOTES, 'UTF-8') ?>"
                    autocomplete="off"
                    required>

                <input
                    type="password"
                    name="register_password"
                    placeholder="<?= htmlspecialchars($lang['password'], ENT_QUOTES, 'UTF-8') ?>"
                    autocomplete="new-password"
                    required>

                <input
                    type="password"
                    name="register_confirm_password"
                    placeholder="<?= htmlspecialchars($lang['confirm_password'], ENT_QUOTES, 'UTF-8') ?>"
                    autocomplete="new-password"
                    required>

                <select name="class_id" autocomplete="off" required>
                    <option value="">
                        <?= htmlspecialchars($lang['select_class'], ENT_QUOTES, 'UTF-8') ?>
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

                <button type="submit" name="register">
                    <?= htmlspecialchars($lang['register_button'], ENT_QUOTES, 'UTF-8') ?>
                </button>
            </form>
        </div>

        <!-- OVERLAY -->
        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h1>
                        <?= htmlspecialchars($lang['welcome_back'], ENT_QUOTES, 'UTF-8') ?>
                    </h1>

                    <p>
                        <?= htmlspecialchars($lang['welcome_back_text'], ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <button class="ghost" id="signIn" type="button">
                        <?= htmlspecialchars($lang['login'], ENT_QUOTES, 'UTF-8') ?>
                    </button>
                </div>

                <div class="overlay-panel overlay-right">
                    <h1>
                        <?= htmlspecialchars($lang['welcome'], ENT_QUOTES, 'UTF-8') ?>
                    </h1>

                    <p>
                        <?= htmlspecialchars($lang['welcome_text'], ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <button class="ghost" id="signUp" type="button">
                        <?= htmlspecialchars($lang['register_title'], ENT_QUOTES, 'UTF-8') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="/AURA/01_front/php/auth.js"></script>

    <script src="/AURA/pwa-register.js" defer></script>
</body>

</html>