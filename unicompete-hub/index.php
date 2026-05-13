<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth-guard.php';
$pdo = require __DIR__ . '/config/db.php';
$pageTitle = 'Evntra | University Competition Hub';
$pageDescription = 'Manage university competitions, registrations, calendars, and organizer analytics with Evntra.';
$pageStyles = [];
$pageScripts = [];

$featured = upcoming_public_competitions($pdo, 4);
$stats = [
    ['label' => 'Active competitions', 'value' => competitions_count($pdo)],
    ['label' => 'Registered students', 'value' => users_count($pdo)],
    ['label' => 'Conflict flags', 'value' => conflict_flags_count($pdo)],
];

include __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div>
        <span class="badge" style="background:rgba(0,212,170,0.18);">University competition intelligence</span>
        <h1>Plan, discover, and manage competitions without spreadsheet chaos.</h1>
        <p>Evntra centralizes student registrations, organizer approvals, calendar scheduling, conflict detection, and analytics in one production-ready platform.</p>
        <div class="form-actions">
            <a class="btn btn-primary" href="/student/browse.php">Browse competitions</a>
            <?php if (!$currentUser): ?>
                <a class="btn btn-outline" href="/auth/register.php">Create account</a>
            <?php else: ?>
                <a class="btn btn-outline" href="/<?= e($currentUser['role']) ?>/dashboard.php">Go to dashboard</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="hero-panel">
        <div class="kpi-grid" style="grid-template-columns:repeat(3,1fr);">
            <?php foreach ($stats as $stat): ?>
                <div class="stat-card">
                    <p class="small-text"><?= e($stat['label']) ?></p>
                    <h3 class="stat-value"><?= (int) $stat['value'] ?></h3>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="page-hero">
    <div class="section-head">
        <div>
            <h2>Featured competitions</h2>
            <p>Latest public events available to students across categories.</p>
        </div>
        <a class="btn btn-outline" href="/student/browse.php">View all</a>
    </div>
    <div class="grid grid-4">
        <?php foreach ($featured as $competition): ?>
            <article class="card">
                <a class="card-media" href="<?= e(competition_url($competition)) ?>">
                    <img src="<?= e($competition['banner_image'] ?: '/assets/img/logo.svg') ?>" alt="<?= e($competition['title']) ?>">
                </a>
                <div class="card-body">
                    <span class="badge" <?= category_badge_style($competition['category']) ?>><?= e($competition['category']) ?></span>
                    <h3 class="card-title"><a href="<?= e(competition_url($competition)) ?>"><?= e($competition['title']) ?></a></h3>
                    <div class="card-meta">
                        <span><?= e(date('M d', strtotime($competition['event_start']))) ?></span>
                        <span><?= e($competition['venue']) ?></span>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="grid grid-3" style="margin-top:2rem;">
    <div class="panel">
        <h3>Students</h3>
        <p>Search, bookmark, register, and track all your competition activity from one dashboard.</p>
    </div>
    <div class="panel">
        <h3>Organizers</h3>
        <p>Create competitions, monitor registrations, and compare performance with live analytics.</p>
    </div>
    <div class="panel">
        <h3>Admins</h3>
        <p>Approve submissions, review conflict reports, and manage platform users at scale.</p>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
