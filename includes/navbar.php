<?php

$navLinks = [
    ['label' => 'Home', 'href' => '/index.php', 'icon' => 'hub'],
    ['label' => 'Browse', 'href' => '/student/browse.php', 'icon' => 'trophy'],
];

if ($currentUser) {
    $role = $currentUser['role'];
    if ($role === 'student') {
        $navLinks[] = ['label' => 'Dashboard', 'href' => '/student/dashboard.php', 'icon' => 'space_dashboard'];
        $navLinks[] = ['label' => 'Registrations', 'href' => '/student/my-registrations.php', 'icon' => 'event_note'];
        $navLinks[] = ['label' => 'Bookmarks', 'href' => '/student/bookmarks.php', 'icon' => 'bookmark'];
    } elseif ($role === 'organizer') {
        $navLinks[] = ['label' => 'Dashboard', 'href' => '/organizer/dashboard.php', 'icon' => 'space_dashboard'];
        $navLinks[] = ['label' => 'My Competitions', 'href' => '/organizer/my-competitions.php', 'icon' => 'table_chart'];
        $navLinks[] = ['label' => 'Analytics', 'href' => '/organizer/analytics.php', 'icon' => 'leaderboard'];
    } elseif ($role === 'admin') {
        $navLinks[] = ['label' => 'Dashboard', 'href' => '/admin/dashboard.php', 'icon' => 'space_dashboard'];
        $navLinks[] = ['label' => 'Approvals', 'href' => '/admin/approve-competitions.php', 'icon' => 'rule'];
        $navLinks[] = ['label' => 'Users', 'href' => '/admin/manage-users.php', 'icon' => 'groups'];
    }
}

$currentScript = $_SERVER['SCRIPT_NAME'];
?>
<header class="sidebar">
    <div class="brand-wrap" style="display:flex; align-items:center; gap:0.75rem; padding: 0.5rem 0.5rem 1.5rem; border-bottom:1px solid var(--border); margin-bottom:1.5rem;">
        <div style="width:40px; height:40px; border-radius:8px; background:rgba(84,233,138,0.1); border:1px solid rgba(84,233,138,0.2); display:flex; align-items:center; justify-content:center;">
            <span class="material-symbols-outlined" style="font-size:24px; color:var(--accent-primary);">hub</span>
        </div>
        <div>
            <h1 style="font-size:1.5rem; font-weight:700; color:var(--accent-primary); margin:0; line-height:1;">Evntra</h1>
            <p style="font-size:0.6rem; color:var(--text-secondary); margin:0.25rem 0 0 0; text-transform:uppercase; letter-spacing:0.1em;">Elite Competition Hub</p>
        </div>
    </div>
    <nav class="sidebar-nav">
        <?php foreach ($navLinks as $link): ?>
            <a href="<?= e($link['href']) ?>" class="nav-link <?= $currentScript === $link['href'] ? 'active' : '' ?>">
                <span class="nav-icon material-symbols-outlined"><?= e($link['icon']) ?></span>
                <span><?= e($link['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-footer">
        <?php if ($currentUser): ?>
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
