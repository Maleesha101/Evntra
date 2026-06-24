<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
require_login(['admin']);
$pdo = require __DIR__ . '/../config/db.php';
$pageTitle = 'Analytics | Evntra';
$pageScripts = ['https://cdn.jsdelivr.net/npm/chart.js', '/assets/js/analytics.js'];

// Get platform-wide analytics data
$stats = [
    'total_competitions' => competitions_count($pdo),
    'total_registrations' => $pdo->query('SELECT COUNT(*) FROM registrations WHERE status = "registered"')->fetchColumn(),
    'total_users' => users_count($pdo),
    'total_views' => (int) $pdo->query('SELECT COALESCE(SUM(views), 0) FROM competitions')->fetchColumn(),
];

// Get registrations by category
$categoryStmt = $pdo->query('
    SELECT c.category, COUNT(r.id) as registrations
    FROM competitions c
    LEFT JOIN registrations r ON c.id = r.competition_id AND r.status = "registered"
    GROUP BY c.category
    ORDER BY registrations DESC
');
$categoryData = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Get top 10 competitions by registrations
$topCompsStmt = $pdo->query('
    SELECT c.title, COUNT(r.id) as registration_count
    FROM competitions c
    LEFT JOIN registrations r ON c.id = r.competition_id AND r.status = "registered"
    WHERE c.status IN ("published", "ongoing", "completed")
    GROUP BY c.id
    ORDER BY registration_count DESC
    LIMIT 10
');
$topComps = $topCompsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get registrations over time (last 30 days)
$timelineStmt = $pdo->query('
    SELECT DATE(registered_at) as date, COUNT(*) as count
    FROM registrations
    WHERE registered_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(registered_at)
    ORDER BY date ASC
');
$timelineData = $timelineStmt->fetchAll(PDO::FETCH_ASSOC);

// Build data for charts
$data = [
    'bar' => [
        'labels' => array_map(function($c) { return $c['title']; }, $topComps),
        'values' => array_map(function($c) { return (int) $c['registration_count']; }, $topComps),
    ],
    'line' => [
        'labels' => array_map(function($t) { return $t['date']; }, $timelineData),
        'values' => array_map(function($t) { return (int) $t['count']; }, $timelineData),
    ],
    'doughnut' => [
        'labels' => array_map(function($c) { return $c['category']; }, $categoryData),
        'values' => array_map(function($c) { return (int) $c['registrations']; }, $categoryData),
    ],
];

include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
    <h1>Platform Analytics</h1>
    <p>System-wide insights on competitions, registrations, and user engagement.</p>
</section>

<section class="kpi-grid">
    <div class="stat-card card"><p class="small-text">Total competitions</p><h3 class="stat-value"><?= (int) $stats['total_competitions'] ?></h3></div>
    <div class="stat-card card"><p class="small-text">Total registrations</p><h3 class="stat-value"><?= (int) $stats['total_registrations'] ?></h3></div>
    <div class="stat-card card"><p class="small-text">Total users</p><h3 class="stat-value"><?= (int) $stats['total_users'] ?></h3></div>
    <div class="stat-card card"><p class="small-text">Total views</p><h3 class="stat-value"><?= (int) $stats['total_views'] ?></h3></div>
</section>

<div id="analytics-data" hidden><?= e(json_encode($data, JSON_UNESCAPED_SLASHES)) ?></div>
<section class="dashboard-grid" style="margin-top:1.5rem;">
    <div class="panel chart-card"><h2>Registrations per competition</h2><div class="chart-wrap"><canvas id="registrationsBarChart"></canvas></div></div>
    <div class="panel chart-card"><h2>Registrations over time</h2><div class="chart-wrap"><canvas id="registrationsLineChart"></canvas></div></div>
    <div class="panel chart-card"><h2>Breakdown by category</h2><div class="chart-wrap"><canvas id="registrationsDoughnutChart"></canvas></div></div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
