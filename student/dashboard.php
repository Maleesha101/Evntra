<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
require_login(['student']);
$pdo = require __DIR__ . '/../config/db.php';
$currentUser = current_user();
$pageTitle = 'Student Dashboard | Evntra';

$userId = (int) $currentUser['id'];

// Active/Upcoming registered competitions
$stmt = $pdo->prepare('
    SELECT c.* 
    FROM registrations r 
    INNER JOIN competitions c ON r.competition_id = c.id 
    WHERE r.user_id = ? AND r.status <> "cancelled" AND c.event_end >= NOW() 
    ORDER BY c.event_start ASC
');
$stmt->execute([$userId]);
$activeCompetitions = $stmt->fetchAll();

// Previous registered competitions
$stmt = $pdo->prepare('
    SELECT c.* 
    FROM registrations r 
    INNER JOIN competitions c ON r.competition_id = c.id 
    WHERE r.user_id = ? AND r.status <> "cancelled" AND c.event_end < NOW() 
    ORDER BY c.event_start DESC
');
$stmt->execute([$userId]);
$previousCompetitions = $stmt->fetchAll();
$stats = [
    ['label' => 'Registered competitions', 'value' => user_registration_count($pdo, (int) $currentUser['id'])],
    ['label' => 'Bookmarks', 'value' => user_bookmark_count($pdo, (int) $currentUser['id'])],
];

$categoryIcons = [
    'CTF' => 'vpn_key',
    'Hackathon' => 'terminal',
    'Robotics' => 'precision_manufacturing',
    'Gaming' => 'sports_esports',
    'Coding' => 'code',
    'AI/ML' => 'smart_toy',
    'Other' => 'category',
];

include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
    <h1>Welcome back, <?= e($currentUser['full_name']) ?>.</h1>
    <p>Keep track of your registrations, bookmarks, and upcoming university competition deadlines.</p>
</section>

<section class="kpi-grid">
    <div class="stat-card card">
        <div class="stat-card-header">
            <span class="small-text">Registered competitions</span>
            <div class="stat-icon"><span class="material-symbols-outlined">trophy</span></div>
        </div>
        <h3 class="stat-value"><?= (int) $stats[0]['value'] ?></h3>
    </div>
    <div class="stat-card card">
        <div class="stat-card-header">
            <span class="small-text">Bookmarks</span>
            <div class="stat-icon"><span class="material-symbols-outlined">bookmark</span></div>
        </div>
        <h3 class="stat-value"><?= (int) $stats[1]['value'] ?></h3>
    </div>
</section>

<section class="dashboard-grid" style="margin-top:1.5rem;">
    <!-- Active/Upcoming registered competitions -->
    <div class="panel">
        <div class="section-head">
            <div>
                <h2>Registered competitions</h2>
                <p>Your active registered competitions.</p>
            </div>
        </div>
        <?php if ($activeCompetitions): ?>
            <div class="grid">
                <?php foreach ($activeCompetitions as $competition): 
                    $cat = $competition['category'];
                    $icon = $categoryIcons[$cat] ?? 'category';
                    $colors = category_colors();
                    $catColor = $colors[$cat] ?? $colors['Other'];
                    $url = competition_url($competition);
                ?>
                    <article class="card <?= e(strtolower($cat)) ?>" onclick="window.location.href='<?= e($url) ?>'" style="border-left: 4px solid <?= $catColor ?>; display: flex; flex-direction: column; justify-content: space-between; cursor: pointer;">
                        <div class="card-body" style="padding: 1.25rem;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <span class="badge" <?= category_badge_style($cat) ?> style="font-size: 0.7rem; font-weight: 700; padding: 0.3rem 0.6rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <span class="material-symbols-outlined" style="font-size: 14px; color: #fff;"><?= $icon ?></span>
                                    <?= e($cat) ?>
                                </span>
                            </div>
                            <h3 class="card-title" style="font-size: 1.2rem; margin: 0 0 0.75rem 0; font-family: 'Space Grotesk', sans-serif;">
                                <a href="<?= e(competition_url($competition)) ?>"><?= e($competition['title']) ?></a>
                            </h3>
                            <div style="display: flex; gap: 1rem; color: var(--text-secondary); font-size: 0.85rem; border-top: 1px solid var(--border); padding-top: 0.75rem; margin-top: 0.75rem;">
                                <div style="display: flex; align-items: center; gap: 0.35rem;">
                                    <span class="material-symbols-outlined" style="font-size: 16px; color: var(--accent-primary);">calendar_month</span>
                                    <span><?= e(date('M d, Y', strtotime($competition['event_start']))) ?></span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.35rem;">
                                    <span class="material-symbols-outlined" style="font-size: 16px; color: var(--accent-primary);">location_on</span>
                                    <span><?= e($competition['venue']) ?></span>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color: var(--text-secondary);">You have no active registered competitions.</p>
        <?php endif; ?>
    </div>

    <!-- Previous registered events -->
    <div class="panel">
        <div class="section-head">
            <div>
                <h2>Previous registered events</h2>
                <p>Events you have participated in.</p>
            </div>
        </div>
        <?php if ($previousCompetitions): ?>
            <div class="grid">
                <?php foreach ($previousCompetitions as $competition): 
                    $cat = $competition['category'];
                    $icon = $categoryIcons[$cat] ?? 'category';
                    $colors = category_colors();
                    $catColor = $colors[$cat] ?? $colors['Other'];
                    $url = competition_url($competition);
                ?>
                    <article class="card <?= e(strtolower($cat)) ?>" onclick="window.location.href='<?= e($url) ?>'" style="border-left: 4px solid <?= $catColor ?>; display: flex; flex-direction: column; justify-content: space-between; opacity: 0.8; cursor: pointer;">
                        <div class="card-body" style="padding: 1.25rem;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <span class="badge" <?= category_badge_style($cat) ?> style="font-size: 0.7rem; font-weight: 700; padding: 0.3rem 0.6rem; display: inline-flex; align-items: center; gap: 0.35rem; filter: grayscale(30%);">
                                    <span class="material-symbols-outlined" style="font-size: 14px; color: #fff;"><?= $icon ?></span>
                                    <?= e($cat) ?>
                                </span>
                            </div>
                            <h3 class="card-title" style="font-size: 1.2rem; margin: 0 0 0.75rem 0; font-family: 'Space Grotesk', sans-serif;">
                                <a href="<?= e(competition_url($competition)) ?>"><?= e($competition['title']) ?></a>
                            </h3>
                            <div style="display: flex; gap: 1rem; color: var(--text-secondary); font-size: 0.85rem; border-top: 1px solid var(--border); padding-top: 0.75rem; margin-top: 0.75rem;">
                                <div style="display: flex; align-items: center; gap: 0.35rem;">
                                    <span class="material-symbols-outlined" style="font-size: 16px; color: var(--text-secondary);">calendar_month</span>
                                    <span><?= e(date('M d, Y', strtotime($competition['event_start']))) ?></span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.35rem;">
                                    <span class="material-symbols-outlined" style="font-size: 16px; color: var(--text-secondary);">location_on</span>
                                    <span><?= e($competition['venue']) ?></span>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color: var(--text-secondary);">You have no previous registered events.</p>
        <?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
