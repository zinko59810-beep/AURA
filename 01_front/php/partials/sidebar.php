<?php
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
        <a href="/AURA/question_list" class="aura-menu-link <?= $currentPage === 'home' ? 'active' : '' ?>">
            <i class="bi bi-house-door"></i>
            <span>Home</span>
        </a>

        <a href="/AURA/post" class="aura-menu-link <?= $currentPage === 'post' ? 'active' : '' ?>">
            <i class="bi bi-plus-square"></i>
            <span>Post</span>
        </a>

        <a href="/AURA/chat" class="aura-menu-link <?= $currentPage === 'chat' ? 'active' : '' ?>">
            <i class="bi bi-chat-dots"></i>
            <span>
                Chat
                <?php if ($chat_count > 0): ?>
                    <span class="badge bg-danger rounded-pill"><?= $chat_count ?></span>
                <?php endif; ?>
            </span>
        </a>

        <a href="/AURA/notice" class="aura-menu-link <?= $currentPage === 'notice' ? 'active' : '' ?>">
            <i class="bi bi-bell"></i>
            <span>
                Notice
                <?php if ($notice_count > 0): ?>
                    <span class="badge bg-danger rounded-pill"><?= $notice_count ?></span>
                <?php endif; ?>
            </span>
        </a>

        <a href="/AURA/ranking" class="aura-menu-link <?= $currentPage === 'ranking' ? 'active' : '' ?>">
            <i class="bi bi-trophy"></i>
            <span>Ranking</span>
        </a>

        <a href="/AURA/profile" class="aura-menu-link <?= $currentPage === 'profile' ? 'active' : '' ?>">
            <i class="bi bi-person-circle"></i>
            <span>Profile</span>
        </a>

        <a href="/AURA/game" class="aura-menu-link <?= $currentPage === 'arcade' ? 'active' : '' ?>">
            <i class="bi bi-controller"></i>
            <span>Arcade</span>
        </a>
    </nav>

    <a href="/AURA/logout" class="aura-logout">
        <i class="bi bi-box-arrow-right"></i>
        <span>Logout</span>
    </a>
</aside>
