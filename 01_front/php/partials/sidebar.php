<?php

/**
 * @var array $lang
 */

if (!isset($currentPage)) {
    $currentPage = '';
}

if (!isset($chat_count)) {
    $chat_count = 0;
}

if (!isset($notice_count)) {
    $notice_count = 0;
}

$sidebarLang = ($currentLang ?? 'ja') === 'ja' ? 'en' : 'ja';
$sidebarLangLabel = $sidebarLang === 'en' ? 'English' : '日本語';
$sidebarLangQuery = $_GET;
$sidebarLangQuery['lang'] = $sidebarLang;
$sidebarLangUrl = '?' . http_build_query($sidebarLangQuery);
?>

<aside class="aura-sidebar">

    <a href="/AURA/home" class="aura-brand">

        <span class="aura-brand-mark">
            <i class="bi bi-fire"></i>
        </span>

        <span>AURA Study</span>

    </a>

    <nav class="aura-menu">

        <a
            href="/AURA/question_list"
            class="aura-menu-link <?= $currentPage === 'home' ? 'active' : '' ?>">

            <i class="bi bi-house-door"></i>

            <span>
                <?= htmlspecialchars($lang['home'], ENT_QUOTES, 'UTF-8') ?>
            </span>

        </a>

        <a
            href="/AURA/post"
            class="aura-menu-link <?= $currentPage === 'post' ? 'active' : '' ?>">

            <i class="bi bi-plus-square"></i>

            <span>
                <?= htmlspecialchars($lang['post'], ENT_QUOTES, 'UTF-8') ?>
            </span>

        </a>

        <a
            href="/AURA/chat"
            class="aura-menu-link <?= $currentPage === 'chat' ? 'active' : '' ?>">

            <i class="bi bi-chat-dots"></i>

            <span class="aura-menu-label">
                <?= htmlspecialchars($lang['chat'], ENT_QUOTES, 'UTF-8') ?>
            </span>

            <?php if ($chat_count > 0): ?>
                <span class="badge bg-danger rounded-pill aura-menu-badge">
                    <?= htmlspecialchars((string) $chat_count, ENT_QUOTES, 'UTF-8') ?>
                </span>
            <?php endif; ?>

        </a>

        <a
            href="/AURA/notice"
            class="aura-menu-link <?= $currentPage === 'notice' ? 'active' : '' ?>">

            <i class="bi bi-bell"></i>

            <span class="aura-menu-label">
                <?= htmlspecialchars($lang['notice'], ENT_QUOTES, 'UTF-8') ?>
            </span>

            <?php if ($notice_count > 0): ?>
                <span class="badge bg-danger rounded-pill aura-menu-badge">
                    <?= htmlspecialchars((string) $notice_count, ENT_QUOTES, 'UTF-8') ?>
                </span>
            <?php endif; ?>

        </a>

        <a
            href="/AURA/ranking"
            class="aura-menu-link <?= $currentPage === 'ranking' ? 'active' : '' ?>">

            <i class="bi bi-trophy"></i>

            <span>
                <?= htmlspecialchars($lang['ranking'], ENT_QUOTES, 'UTF-8') ?>
            </span>

        </a>

        <a
            href="/AURA/profile"
            class="aura-menu-link <?= $currentPage === 'profile' ? 'active' : '' ?>">

            <i class="bi bi-person-circle"></i>

            <span>
                <?= htmlspecialchars($lang['profile'], ENT_QUOTES, 'UTF-8') ?>
            </span>

        </a>

        <a
            href="/AURA/game"
            class="aura-menu-link <?= $currentPage === 'arcade' ? 'active' : '' ?>">

            <i class="bi bi-controller"></i>

            <span>
                <?= htmlspecialchars($lang['arcade'], ENT_QUOTES, 'UTF-8') ?>
            </span>

        </a>

    </nav>
    <a
        href="<?= htmlspecialchars($sidebarLangUrl, ENT_QUOTES, 'UTF-8') ?>"
        class="aura-menu-link aura-language-toggle"
        aria-label="Language: <?= htmlspecialchars($sidebarLangLabel, ENT_QUOTES, 'UTF-8') ?>">

        <i class="bi bi-translate"></i>
        <span><?= htmlspecialchars($sidebarLangLabel, ENT_QUOTES, 'UTF-8') ?></span>

    </a>

    <a href="/AURA/logout" class="aura-logout">

        <i class="bi bi-box-arrow-right"></i>

        <span>
            <?= htmlspecialchars($lang['logout'], ENT_QUOTES, 'UTF-8') ?>
        </span>

    </a>

</aside>