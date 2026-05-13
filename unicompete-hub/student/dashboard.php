<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
require_login(['student']);
$pdo = require __DIR__ . '/../config/db.php';
$currentUser = current_user();
$pageTitle = 'Student Dashboard | Evntra';

$registeredCompetitions = competitions_for_user_dashboard($pdo, (int) $currentUser['id']);
$recentCompetitions = upcoming_public_competitions($pdo, 4);
$stats = [
    ['label' => 'Registered competitions', 'value' => user_registration_count($pdo, (int) $currentUser['id'])],
    ['label' => 'Bookmarks', 'value' => user_bookmark_count($pdo, (int) $currentUser['id'])],
    ['label' => 'Competitions this month', 'value' => competitions_this_month_count($pdo)],
];

include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
    <h1>Welcome back, <?= e($currentUser['full_name']) ?>.</h1>
    <p>Keep track of your registrations, bookmarks, and upcoming university competition deadlines.</p>
</section>

<section class="kpi-grid">
    <?php foreach ($stats as $stat): ?>
        <div class="stat-card card">
            <p class="small-text"><?= e($stat['label']) ?></p>
            <h3 class="stat-value"><?= (int) $stat['value'] ?></h3>
        </div>
    <?php endforeach; ?>
</section>

<section class="dashboard-grid" style="margin-top:1.5rem;">
    <div class="panel">
        <div class="section-head">
            <div>
                <h2>Upcoming registrations</h2>
                <p>Your next competitions are shown here.</p>
            </div>
        </div>
        <?php if ($registeredCompetitions): ?>
            <div class="grid">
                <?php foreach ($registeredCompetitions as $competition): ?>
                    <article class="card">
                        <div class="card-body">
                            <span class="badge" <?= category_badge_style($competition['category']) ?>><?= e($competition['category']) ?></span>
                            <h3 class="card-title"><?= e($competition['title']) ?></h3>
                            <div class="card-meta">
                                <span><?= e(date('M d, Y', strtotime($competition['event_start']))) ?></span>
                                <span><?= e($competition['venue']) ?></span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>You have not registered for any competitions yet.</p>
        <?php endif; ?>
    </div>
    <div class="panel">
        <div class="section-head">
            <div>
                <h2>Recent competitions</h2>
                <p>Newest public competitions to explore.</p>
            </div>
        </div>
        <div class="grid">
            <?php foreach ($recentCompetitions as $competition): ?>
                <article class="card">
                    <div class="card-body">
                        <span class="badge" <?= category_badge_style($competition['category']) ?>><?= e($competition['category']) ?></span>
                        <h3 class="card-title"><a href="<?= e(competition_url($competition)) ?>"><?= e($competition['title']) ?></a></h3>
                        <p><?= e(substr($competition['description'], 0, 100)) ?><?= strlen($competition['description']) > 100 ? '...' : '' ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
