<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
require_login(['organizer']);
$pdo = require __DIR__ . '/../config/db.php';
$currentUser = current_user();
$pageTitle = 'My Competitions | Evntra';
$competitions = organizer_competitions($pdo, (int) $currentUser['id']);

include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
    <div class="section-head">
        <div>
            <h1>My competitions</h1>
            <p>All competitions you created, grouped by lifecycle status.</p>
        </div>
        <a class="btn btn-primary" href="/organizer/create-competition.php">Create New Competition</a>
    </div>
</section>

<div class="grid grid-2">
    <?php foreach ($competitions as $competition): ?>
        <article class="card">
            <div class="card-body">
                <div class="card-meta" style="justify-content:space-between;align-items:center;">
                    <span class="badge" <?= category_badge_style($competition['category']) ?>><?= e($competition['category']) ?></span>
                    <span class="badge" style="background:rgba(255,255,255,0.08);"><?= e($competition['status']) ?></span>
                </div>
                <h3 class="card-title"><a href="<?= e(competition_url($competition)) ?>"><?= e($competition['title']) ?></a></h3>
                <p><?= e(substr($competition['description'], 0, 120)) ?><?= strlen($competition['description']) > 120 ? '...' : '' ?></p>
                <div class="card-meta">
                    <span><?= e($competition['venue']) ?></span>
                    <span><?= (int) $competition['views'] ?> views</span>
                </div>
                <div class="form-actions" style="margin-top:1rem;">
                    <a class="btn btn-outline" href="/organizer/edit-competition.php?id=<?= (int) $competition['id'] ?>">Edit</a>
                    <a class="btn btn-primary" href="<?= e(competition_url($competition)) ?>">Open</a>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
    <?php if (!$competitions): ?><div class="panel">You have not created any competitions yet.</div><?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
