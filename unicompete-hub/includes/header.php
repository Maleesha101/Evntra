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
$bodyClass = $bodyClass ?? '';
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
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/calendar.css">
    <?php foreach ($pageStyles as $style): ?>
        <link rel="stylesheet" href="<?= e($style) ?>">
    <?php endforeach; ?>
    <script defer src="/assets/js/main.js"></script>
    <?php foreach ($pageScripts as $script): ?>
        <script defer src="<?= e($script) ?>"></script>
    <?php endforeach; ?>
</head>
<body class="has-sidebar <?= e($bodyClass) ?>">
<?php include __DIR__ . '/navbar.php'; ?>
<main class="app-shell">
    <?= render_flash_messages() ?>
