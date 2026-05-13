<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
require_login(['organizer']);
$pdo = require __DIR__ . '/../config/db.php';
$currentUser = current_user();
$pageTitle = 'Analytics | Evntra';
$pageScripts = ['https://cdn.jsdelivr.net/npm/chart.js', '/assets/js/analytics.js'];
$data = organizer_chart_data($pdo, (int) $currentUser['id']);
$stats = [
    'total_views' => dashboard_stats_for_organizer($pdo, (int) $currentUser['id'])['total_views'],
    'conversion_rate' => conversion_rate($pdo, (int) $currentUser['id']),
    'avg_team_size' => avg_team_size($pdo, (int) $currentUser['id']),
];

include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
    <h1>Analytics</h1>
    <p>Live competition performance across registrations, views, and category mix.</p>
</section>

<section class="kpi-grid">
    <div class="stat-card card"><p class="small-text">Total views</p><h3 class="stat-value"><?= (int) $stats['total_views'] ?></h3></div>
    <div class="stat-card card"><p class="small-text">Conversion rate</p><h3 class="stat-value"><?= e((string) $stats['conversion_rate']) ?>%</h3></div>
    <div class="stat-card card"><p class="small-text">Avg team size</p><h3 class="stat-value"><?= e((string) $stats['avg_team_size']) ?></h3></div>
</section>

<div id="analytics-data" hidden><?= e(json_encode($data, JSON_UNESCAPED_SLASHES)) ?></div>
<section class="dashboard-grid" style="margin-top:1.5rem;">
    <div class="panel chart-card"><h2>Registrations per competition</h2><div class="chart-wrap"><canvas id="registrationsBarChart"></canvas></div></div>
    <div class="panel chart-card"><h2>Registrations over time</h2><div class="chart-wrap"><canvas id="registrationsLineChart"></canvas></div></div>
    <div class="panel chart-card"><h2>Breakdown by category</h2><div class="chart-wrap"><canvas id="registrationsDoughnutChart"></canvas></div></div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
