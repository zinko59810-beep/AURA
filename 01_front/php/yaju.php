<?php
session_start();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>学習コミュニティサイト</title>
    <link rel="stylesheet" href="../css/yaju.css">
</head>

<body>

    <header>

        <h1>学習コミュニティサイト</h1>

        <div class="btns">

            <?php if (isset($_SESSION['user_id'])): ?>

                <a href="profile.php" class="user-btn">

                    👤 <?= htmlspecialchars($_SESSION['name']) ?>

                </a>

            <?php else: ?>

                <a href="auth.php" class="login">
                    ログイン
                </a>

                <a href="auth.php?mode=register" class="register">
                    新規登録
                </a>

            <?php endif; ?>

        </div>

    </header>
    </header>

    <section class="hero">

        <video autoplay muted loop playsinline class="bg-video">
            <source src="../video/137614-767056227_medium.mp4" type="video/mp4">
        </video>

        <div class="hero-content">

            <h2>学生同士で学び、つながる場所</h2>

            <p>
                プログラミングや授業で分からないことを質問したり、
                同じ分野を学ぶ仲間や先輩と交流できる学習コミュニティです。
            </p>

            <a href="auth.php?mode=register" class="start-btn">
                今すぐ始める
            </a>

        </div>

    </section>

    <section class="features">

        <h2>主な機能</h2>

        <div class="cards">

            <div class="card">
                <h3>質問・回答</h3>
                <p>分からないことを気軽に質問できます。</p>
            </div>

            <div class="card">
                <h3>勉強グループ</h3>
                <p>PHPやJavaなど分野別で交流できます。</p>
            </div>

            <div class="card">
                <h3>趣味グループ</h3>
                <p>ゲームや車など共通の趣味で交流できます。</p>
            </div>

            <div class="card">
                <h3>ランキング</h3>
                <p>回答するとポイントが獲得できます。</p>
            </div>

        </div>

    </section>

    <section class="about">

        <h2>このサイトの特徴</h2>

        <p>
            DiscordやTeamsとは異なり、
            学習支援に特化したコミュニティサイトです。
            分野ごとに情報が整理されているため、
            過去の質問も探しやすくなっています。
        </p>

    </section>

    <footer>
        <p>© 学習コミュニティサイト</p>
    </footer>

    <script>
        const video = document.querySelector(".bg-video");
        video.playbackRate = 0.5;
    </script>

</body>

</html>