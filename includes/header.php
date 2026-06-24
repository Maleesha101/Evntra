<?php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

app_start_session();
$pdo = app_pdo();
$currentUser = is_logged_in() ? current_user() : null;
$unreadNotifications = $currentUser ? unread_notification_count($pdo, (int) $currentUser['id']) : 0;
$latestNotifications = $currentUser ? latest_notifications($pdo, (int) $currentUser['id']) : [];
$pageTitle = $pageTitle ?? 'Evntra';
$pageDescription = $pageDescription ?? 'Evntra competition management platform';
$pageStyles = $pageStyles ?? [];
$pageScripts = $pageScripts ?? [];
$isAuthPage = in_array(basename($_SERVER['SCRIPT_NAME']), ['login.php', 'register.php', 'forgot-password.php', 'reset-password.php']);
$bodyClass = $bodyClass ?? '';
if (!$isAuthPage) {
    $bodyClass .= ' has-top-bar';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($pageDescription) ?>">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="/assets/css/main.css?v=<?= file_exists(dirname(__DIR__) . '/assets/css/main.css') ? filemtime(dirname(__DIR__) . '/assets/css/main.css') : '1' ?>">
    <link rel="stylesheet" href="/assets/css/dashboard.css?v=<?= file_exists(dirname(__DIR__) . '/assets/css/dashboard.css') ? filemtime(dirname(__DIR__) . '/assets/css/dashboard.css') : '1' ?>">
    <link rel="stylesheet" href="/assets/css/calendar.css?v=<?= file_exists(dirname(__DIR__) . '/assets/css/calendar.css') ? filemtime(dirname(__DIR__) . '/assets/css/calendar.css') : '1' ?>">
    <?php foreach ($pageStyles as $style): 
        $styleVer = '';
        if (strpos($style, '/') === 0) {
            $stylePath = dirname(__DIR__) . $style;
            if (file_exists($stylePath)) {
                $styleVer = '?v=' . filemtime($stylePath);
            }
        }
    ?>
        <link rel="stylesheet" href="<?= e($style) ?><?= $styleVer ?>">
    <?php endforeach; ?>
    <script defer src="/assets/js/main.js?v=<?= file_exists(dirname(__DIR__) . '/assets/js/main.js') ? filemtime(dirname(__DIR__) . '/assets/js/main.js') : '1' ?>"></script>
    <?php foreach ($pageScripts as $script): 
        $scriptVer = '';
        if (strpos($script, '/') === 0) {
            $scriptPath = dirname(__DIR__) . $script;
            if (file_exists($scriptPath)) {
                $scriptVer = '?v=' . filemtime($scriptPath);
            }
        }
    ?>
        <script defer src="<?= e($script) ?><?= $scriptVer ?>"></script>
    <?php endforeach; ?>
</head>
<body class="has-sidebar <?= e($bodyClass) ?>">
<?php include __DIR__ . '/navbar.php'; ?>

<?php if (!$isAuthPage): ?>
<header class="app-header">
    <div class="search-container">
        <span class="material-symbols-outlined search-icon">search</span>
        <input class="search-input" placeholder="Search events, teams..." type="text"/>
    </div>
    <div class="header-actions">
        <?php if ($currentUser): ?>
            <!-- Notification Toggle -->
            <button class="header-action-btn" type="button" data-notification-toggle aria-label="Notifications">
                <span class="material-symbols-outlined">notifications</span>
                <?php if ($unreadNotifications > 0): ?>
                    <span class="notification-dot" data-notification-count></span>
                <?php endif; ?>
            </button>
            
            <!-- Notification Panel -->
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
            
            <!-- User Avatar & Profile Dropdown -->
            <button class="profile-dropdown-trigger" type="button">
                <span class="profile-name">Hi <?= e($currentUser['full_name']) ?></span>
            </button>
        <?php else: ?>
            <a href="/auth/login.php" class="btn btn-primary" style="padding: 0.4rem 1rem; font-size: 0.8rem; border-radius: 9999px;">Login</a>
        <?php endif; ?>
    </div>
</header>
<?php endif; ?>

<main class="app-shell">
    <?= render_flash_messages() ?>
