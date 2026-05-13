<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
require_login(['student']);
$pdo = require __DIR__ . '/../config/db.php';
$currentUser = current_user();
$pageTitle = 'Bookmarks | Evntra';
$bookmarks = bookmarks_for_user($pdo, (int) $currentUser['id']);

include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
    <h1>Saved competitions</h1>
    <p>Your bookmarked competitions for quick access.</p>
</section>

<div class="grid grid-3">
    <?php foreach ($bookmarks as $competition): ?>
        <article class="card">
            <a class="card-media" href="<?= e(competition_url($competition)) ?>">
                <img src="<?= e($competition['banner_image'] ?: '/assets/img/logo.svg') ?>" alt="<?= e($competition['title']) ?>">
            </a>
            <div class="card-body">
                <span class="badge" <?= category_badge_style($competition['category']) ?>><?= e($competition['category']) ?></span>
                <h3 class="card-title"><a href="<?= e(competition_url($competition)) ?>"><?= e($competition['title']) ?></a></h3>
                <a class="btn btn-primary" href="<?= e(competition_url($competition)) ?>">View Details</a>
            </div>
        </article>
    <?php endforeach; ?>
    <?php if (!$bookmarks): ?>
        <div class="panel">No bookmarks yet. Browse competitions and save the ones you like.</div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
