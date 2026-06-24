<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
require_login(['student']);
$pdo = require __DIR__ . '/../config/db.php';
$currentUser = current_user();
$pageTitle = 'Bookmarks | Evntra';
$bookmarks = bookmarks_for_user($pdo, (int) $currentUser['id']);

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
    <h1>Saved competitions</h1>
    <p>Your bookmarked competitions for quick access.</p>
</section>

<div class="grid grid-3">
    <?php foreach ($bookmarks as $competition): 
        $cat = $competition['category'];
        $icon = $categoryIcons[$cat] ?? 'category';
        $colors = category_colors();
        $catColor = $colors[$cat] ?? $colors['Other'];
        $image = $competition['banner_image'] ?: category_fallback_image($cat);
        $regCount = competition_registered_count($pdo, (int)$competition['id']);
        $maxPart = $competition['max_participants'] !== null ? (int)$competition['max_participants'] : 0;
        $isFull = $maxPart > 0 && $regCount >= $maxPart;
        $status = $isFull ? 'Full' : competition_registration_status($competition);
        $venueType = strtolower($competition['venue']) === 'online' ? 'Online' : 'On-Campus';
        
        $statusLower = strtolower($status);
        if ($statusLower === 'open') {
            $statusStyle = 'background: rgba(84, 233, 138, 0.2); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border: 1px solid rgba(84, 233, 138, 0.3); color: #54e98a;';
        } elseif ($statusLower === 'full') {
            $statusStyle = 'background: rgba(255, 154, 74, 0.2); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border: 1px solid rgba(255, 154, 74, 0.3); color: #ff9a4a;';
        } else {
            $statusStyle = 'background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.2); color: var(--text-primary);';
        }
        
        $capText = $maxPart > 0 ? "{$regCount}/{$maxPart} Spots" : "{$regCount} Registered";
    ?>
        <article class="browse-card <?= e(strtolower($cat)) ?>" style="height: 100%; display: flex; flex-direction: column;">
            <div class="browse-card-image-wrap">
                <img class="browse-card-image" src="<?= e($image) ?>" alt="<?= e($competition['title']) ?>">
                <div class="browse-card-badges-left">
                    <span class="badge" style="<?= $statusStyle ?> font-size: 10px; padding: 0.25rem 0.5rem; border-radius: 4px; text-transform: uppercase; font-weight: bold; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 0.25rem;"><?= e($status) ?></span>
                    <span class="badge" style="background: rgba(17, 19, 23, 0.6); backdrop-filter: blur(8px); border: 1px solid var(--border); font-size: 10px; padding: 0.25rem 0.5rem; border-radius: 4px; text-transform: uppercase; font-weight: bold; letter-spacing: 0.05em; color: var(--text-primary); display: inline-flex; align-items: center; gap: 0.25rem;"><?= e($venueType) ?></span>
                </div>
                <button class="bookmark-badge-btn bookmarked" data-bookmark-toggle data-competition-id="<?= (int)$competition['id'] ?>" aria-label="Bookmark">
                    <span class="material-symbols-outlined" style="font-size: 18px;">bookmark</span>
                </button>
            </div>
            <div class="browse-card-body" style="padding: 1.25rem; flex: 1; display: flex; flex-direction: column;">
                <div class="browse-card-cat" style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
                    <span class="material-symbols-outlined" style="font-size: 14px;"><?= $icon ?></span>
                    <span><?= e($cat) ?></span>
                </div>
                <h3 class="browse-card-title" style="font-size: 1.15rem; font-weight: 700; margin: 0 0 0.5rem 0; line-height: 1.3; font-family: 'Space Grotesk', sans-serif;">
                    <a href="<?= e(competition_url($competition)) ?>"><?= e($competition['title']) ?></a>
                </h3>
                <p class="browse-card-desc" style="font-size: 0.88rem; color: var(--text-secondary); line-height: 1.5; margin-bottom: 1.25rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; flex-grow: 1;">
                    <?= e($competition['description']) ?>
                </p>
                <div class="browse-card-footer" style="margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; font-size: 0.88rem;">
                    <div style="display: flex; align-items: center; gap: 0.35rem; color: var(--text-secondary);">
                        <span class="material-symbols-outlined" style="font-size: 16px;">group</span>
                        <span><?= e($capText) ?></span>
                    </div>
                    <a class="browse-card-details-link" href="<?= e(competition_url($competition)) ?>" style="font-weight: 700; display: flex; align-items: center; gap: 0.25rem; transition: var(--transition);">
                        <span>Details</span>
                        <span class="material-symbols-outlined" style="font-size: 16px;">arrow_forward</span>
                    </a>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
    <?php if (!$bookmarks): ?>
        <div class="panel" style="grid-column: 1 / -1;">No bookmarks yet. Browse competitions and save the ones you like.</div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-bookmark-toggle]').forEach((button) => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            const competitionId = button.getAttribute('data-competition-id');
            if (!competitionId) return;
            
            try {
                const response = await fetch('/api/bookmark.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ competition_id: competitionId })
                });
                const data = await response.json();
                
                if (!data.bookmarked) {
                    const card = button.closest('article');
                    if (card) {
                        card.style.transition = 'all 0.4s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.9)';
                        setTimeout(() => {
                            card.remove();
                            const grid = document.querySelector('.grid');
                            if (grid && grid.querySelectorAll('article').length === 0) {
                                grid.outerHTML = '<div class="panel">No bookmarks yet. Browse competitions and save the ones you like.</div>';
                            }
                        }, 400);
                    }
                }
            } catch (err) {
                console.error('Error toggling bookmark:', err);
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
