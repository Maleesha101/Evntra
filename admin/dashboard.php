<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
require_login(['admin']);
$pdo = require __DIR__ . '/../config/db.php';
$pageTitle = 'Admin Dashboard | Evntra';
$stats = [
    ['label' => 'Users', 'value' => users_count($pdo)],
    ['label' => 'Competitions', 'value' => competitions_count($pdo)],
    ['label' => 'Pending approvals', 'value' => pending_competitions_count($pdo)],
    ['label' => 'Conflict flags', 'value' => conflict_flags_count($pdo)],
];
$recentConflicts = array_slice(conflict_report_rows($pdo), 0, 5);

include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
    <h1>Admin dashboard</h1>
    <p>Review approvals, users, and detected competition conflicts.</p>
</section>

<section class="kpi-grid">
    <?php foreach ($stats as $stat): ?>
        <div class="stat-card card"><p class="small-text"><?= e($stat['label']) ?></p><h3 class="stat-value"><?= (int) $stat['value'] ?></h3></div>
    <?php endforeach; ?>
</section>

<div class="dashboard-grid" style="margin-top:1.5rem;">
    <div class="panel">
        <h2>Quick actions</h2>
        <div class="button-row">
            <a class="btn btn-primary" href="/admin/approve-competitions.php">Approve competitions</a>
            <a class="btn btn-outline" href="/admin/analytics.php">View Analytics</a>
            <a class="btn btn-outline" href="/admin/manage-users.php">Manage users</a>
            <a class="btn btn-outline" href="/admin/conflict-report.php">View conflicts</a>
        </div>
    </div>
    <div class="panel">
        <h2>Recent conflict flags</h2>
        <div class="grid">
            <?php foreach ($recentConflicts as $conflict): ?>
                <div class="card-body" style="padding:0;">
                    <strong><?= e($conflict['competition_a_title']) ?> vs <?= e($conflict['competition_b_title']) ?></strong>
                    <div class="card-meta"><span class="badge" style="background:<?= $conflict['severity'] === 'high' ? '#ff4757' : ($conflict['severity'] === 'medium' ? '#ffa502' : '#2ed573') ?>;"><?= e($conflict['severity']) ?></span></div>
                </div>
            <?php endforeach; ?>
            <?php if (!$recentConflicts): ?><p>No conflicts detected.</p><?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
