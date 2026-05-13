<?php

$navLinks = [
    ['label' => 'Home', 'href' => '/index.php', 'icon' => '⌂'],
    ['label' => 'Browse', 'href' => '/student/browse.php', 'icon' => '⌕'],
];

if ($currentUser) {
    $role = $currentUser['role'];
    if ($role === 'student') {
        $navLinks[] = ['label' => 'Dashboard', 'href' => '/student/dashboard.php', 'icon' => '◫'];
        $navLinks[] = ['label' => 'Registrations', 'href' => '/student/my-registrations.php', 'icon' => '▣'];
        $navLinks[] = ['label' => 'Bookmarks', 'href' => '/student/bookmarks.php', 'icon' => '★'];
    } elseif ($role === 'organizer') {
        $navLinks[] = ['label' => 'Dashboard', 'href' => '/organizer/dashboard.php', 'icon' => '◫'];
        $navLinks[] = ['label' => 'My Competitions', 'href' => '/organizer/my-competitions.php', 'icon' => '▣'];
        $navLinks[] = ['label' => 'Analytics', 'href' => '/organizer/analytics.php', 'icon' => '⟟'];
    } elseif ($role === 'admin') {
        $navLinks[] = ['label' => 'Dashboard', 'href' => '/admin/dashboard.php', 'icon' => '◫'];
        $navLinks[] = ['label' => 'Approvals', 'href' => '/admin/approve-competitions.php', 'icon' => '✓'];
        $navLinks[] = ['label' => 'Users', 'href' => '/admin/manage-users.php', 'icon' => '◉'];
    }
}
?>
<header class="sidebar">
    <div class="brand-wrap">
        <a class="brand" href="/index.php">
            <img src="/assets/img/logo.svg" alt="Evntra logo" class="brand-logo">
            <span>Evntra</span>
        </a>
    </div>
    <nav class="sidebar-nav">
        <?php foreach ($navLinks as $link): ?>
            <a href="<?= e($link['href']) ?>" class="nav-link">
                <span class="nav-icon"><?= e($link['icon']) ?></span>
                <span><?= e($link['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-footer">
        <?php if ($currentUser): ?>
            <button class="notification-toggle" type="button" data-notification-toggle>
                <span>Notifications</span>
                <span class="notification-badge" data-notification-count><?= (int) $unreadNotifications ?></span>
            </button>
            <div class="notification-panel" data-notification-panel>
                <div class="notification-panel-head">
                    <strong>Latest notifications</strong>
                    <button type="button" class="link-button" data-mark-all-read>Mark all read</button>
                </div>
                <div class="notification-list">
                    <?php foreach ($latestNotifications as $notification): ?>
                        <a href="<?= e(notification_link_or_default($notification['link'])) ?>" class="notification-item <?= (int) $notification['is_read'] === 0 ? 'unread' : '' ?>" data-notification-id="<?= (int) $notification['id'] ?>">
                            <span><?= e($notification['message']) ?></span>
                            <small><?= e((string) $notification['created_at']) ?></small>
                        </a>
                    <?php endforeach; ?>
                    <?php if (!$latestNotifications): ?>
                        <div class="notification-item empty">No notifications yet.</div>
                    <?php endif; ?>
                </div>
            </div>
            <a href="/auth/logout.php" class="btn btn-outline sidebar-logout">Logout</a>
        <?php else: ?>
            <a href="/auth/login.php" class="btn btn-outline sidebar-logout">Login</a>
        <?php endif; ?>
    </div>
</header>
<div class="mobile-nav">
    <?php foreach ($navLinks as $link): ?>
        <a href="<?= e($link['href']) ?>" class="mobile-nav-link"><?= e($link['label']) ?></a>
    <?php endforeach; ?>
    <?php if ($currentUser): ?>
        <a href="/auth/logout.php" class="mobile-nav-link">Logout</a>
    <?php else: ?>
        <a href="/auth/login.php" class="mobile-nav-link">Login</a>
    <?php endif; ?>
</div>
