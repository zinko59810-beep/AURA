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

            <span>

                <?= htmlspecialchars($lang['chat'], ENT_QUOTES, 'UTF-8') ?>

                <?php if ($chat_count > 0): ?>

                    <span class="badge bg-danger rounded-pill">

                        <?= htmlspecialchars(
                            (string) $chat_count,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </span>

                <?php endif; ?>

            </span>

        </a>

        <a
            href="/AURA/notice"
            class="aura-menu-link <?= $currentPage === 'notice' ? 'active' : '' ?>">

            <i class="bi bi-bell"></i>

            <span>

                <?= htmlspecialchars($lang['notice'], ENT_QUOTES, 'UTF-8') ?>

                <?php if ($notice_count > 0): ?>

                    <span class="badge bg-danger rounded-pill">

                        <?= htmlspecialchars(
                            (string) $notice_count,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </span>

                <?php endif; ?>

            </span>

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
    <div class="dropdown mb-2">

        <button
            class="aura-menu-link border-0 w-100 text-start bg-transparent dropdown-toggle"
            type="button"
            data-bs-toggle="dropdown">

            <i class="bi bi-translate"></i>
            <span>Language</span>

        </button>

        <ul class="dropdown-menu w-100">

            <li>
                <a class="dropdown-item" href="?lang=ja">🇯🇵 日本語</a>
            </li>

            <li>
                <a class="dropdown-item" href="?lang=en">🇺🇸 English</a>
            </li>

        </ul>

    </div>

    <a href="/AURA/logout" class="aura-logout">

        <i class="bi bi-box-arrow-right"></i>

        <span>
            <?= htmlspecialchars($lang['logout'], ENT_QUOTES, 'UTF-8') ?>
        </span>

    </a>

</aside>