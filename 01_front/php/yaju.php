<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Language change
|--------------------------------------------------------------------------
| ?lang=ja or ?lang=en ကနေလာတဲ့ value ကို session ထဲသိမ်းမယ်
*/
if (
    isset($_GET['lang']) &&
    in_array($_GET['lang'], ['ja', 'en'], true)
) {
    $_SESSION['lang'] = $_GET['lang'];
}

/*
|--------------------------------------------------------------------------
| Current language
|--------------------------------------------------------------------------
*/
$currentLang = $_SESSION['lang'] ?? 'ja';

/*
|--------------------------------------------------------------------------
| Load language file
|--------------------------------------------------------------------------
| yaju.php က AURA/01_front/php/ ထဲမှာရှိပြီး
| language files က AURA/lang/ ထဲမှာရှိတယ်
*/
$lang = require __DIR__ . '/../../lang/' . $currentLang . '.php';

/*
|--------------------------------------------------------------------------
| Toggle button setting
|--------------------------------------------------------------------------
*/
if ($currentLang === 'ja') {
    $nextLang = 'en';
    $languageLabel = 'US English';
} else {
    $nextLang = 'ja';
    $languageLabel = 'JP 日本語';
}

/*
|--------------------------------------------------------------------------
| Keep current page query parameters
|--------------------------------------------------------------------------
| lang parameter တစ်ခုပဲပြောင်းပြီး တခြား GET parameters မပျောက်အောင်လုပ်ထားတယ်
*/
$queryParams = $_GET;
$queryParams['lang'] = $nextLang;

$languageUrl = '?' . http_build_query($queryParams);
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8') ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($lang['site_title'], ENT_QUOTES, 'UTF-8') ?>
    </title>

    <link rel="stylesheet" href="/AURA/01_front/css/yaju.css">
</head>

<body>

    <header>

        <h1>
            <?= htmlspecialchars($lang['site_title'], ENT_QUOTES, 'UTF-8') ?>
        </h1>

        <div class="btns">

            <?php if (isset($_SESSION['user_id'])): ?>

                <a href="/AURA/profile" class="user-btn">
                    👤
                    <?= htmlspecialchars(
                        $_SESSION['name'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </a>

                <div class="language-switch">
                    <a
                        href="<?= htmlspecialchars(
                                    $languageUrl,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                        class="lang-btn">
                        <?= htmlspecialchars(
                            $languageLabel,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </a>
                </div>

            <?php else: ?>

                <a href="/AURA/auth" class="login">
                    <?= htmlspecialchars(
                        $lang['login'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </a>

                <a href="/AURA/auth?mode=register" class="register">
                    <?= htmlspecialchars(
                        $lang['register'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </a>

                <div class="language-switch">
                    <a
                        href="<?= htmlspecialchars(
                                    $languageUrl,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                        class="lang-btn">
                        <?= htmlspecialchars(
                            $languageLabel,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </a>
                </div>

            <?php endif; ?>

        </div>

    </header>

    <section class="hero">

        <video autoplay muted loop playsinline class="bg-video">
            <source src="/AURA/01_front/video/137614-767056227_medium.mp4" type="video/mp4">
        </video>

        <div class="hero-content">

            <h2><?= $lang['hero_title'] ?></h2>

            <p>
                <?= $lang['hero_description'] ?>
            </p>

            <a href="/AURA/auth?mode=register" class="start-btn">
                <?= $lang['start_now'] ?>
            </a>

        </div>

    </section>

    <section class="features">

        <h2><?= $lang['main_features'] ?></h2>

        <div class="cards">

            <div class="card">
                <h3><?= $lang['question_answer'] ?></h3>
                <p><?= $lang['question_answer_desc'] ?></p>
            </div>

            <div class="card">
                <h3><?= $lang['study_group'] ?></h3>
                <p><?= $lang['study_group_desc'] ?></p>
            </div>

            <div class="card">
                <h3><?= $lang['hobby_group'] ?></h3>
                <p><?= $lang['hobby_group_desc'] ?></p>
            </div>

            <div class="card">
                <h3><?= $lang['ranking'] ?></h3>
                <p><?= $lang['ranking_desc'] ?></p>
            </div>

        </div>

    </section>

    <section class="about">

        <h2><?= $lang['about_title'] ?></h2>

        <p>
            <?= $lang['about_description'] ?>
        </p>

    </section>

    <footer>
        <p>© <?= $lang['site_title'] ?></p>
    </footer>

    <script>
        const video = document.querySelector(".bg-video");
        video.playbackRate = 0.5;
    </script>

</body>

</html>