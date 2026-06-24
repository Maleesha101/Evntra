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
<!-- Welcome Hero Section -->
<section class="relative-hero-panel" style="position:relative; padding: 2.5rem 0 3.5rem; border-bottom: 1px solid rgba(255,255,255,0.06); overflow:hidden; margin-bottom:2.5rem;">
    <div class="absolute-glow" style="position:absolute; top:0; right:0; width:350px; height:350px; background:rgba(84,233,138,0.04); border-radius:50%; filter:blur(90px); pointer-events:none;"></div>
    <!-- Premium Interactive Hero Orbit Animation -->
    <div class="hero-animation-container">
        <div class="glow-orb"></div>
        <div class="orbit-ring orbit-ring-1"></div>
        <div class="orbit-ring orbit-ring-2"></div>
        <div class="orbit-node node-1"></div>
        <div class="orbit-node node-2"></div>
    </div>
    <div style="position:relative; z-index:1;">
        <div style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.4rem 0.9rem; border-radius:999px; background:rgba(84,233,138,0.08); border:1px solid rgba(84,233,138,0.15); color:var(--accent-primary); font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:1.5rem;">
            <span style="width:6px; height:6px; background:var(--accent-primary); border-radius:50%; animation:conflictPulse 2s infinite;"></span>
            System Online
        </div>
        <h1 style="font-size:3.5rem; margin:0 0 1rem 0; font-family:'Space Grotesk', sans-serif; font-weight:700; line-height:1.1; letter-spacing:-0.02em;">
            Welcome to the <span style="background:linear-gradient(90deg, var(--accent-primary), var(--accent-secondary)); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">Arena</span>
        </h1>
        <p style="font-size:1.15rem; color:var(--text-secondary); max-width:640px; line-height:1.65; margin:0;">
            The ultimate hub for university competitions, hackathons, and elite gaming. Monitor your stats, build your roster, and dominate the leaderboards.
        </p>
    </div>
</section>

<!-- KPI Cards row -->
<section class="kpi-grid">
    <div class="stat-card card">
        <div class="stat-card-header">
            <span class="small-text">Active Competitions</span>
            <div class="stat-icon"><span class="material-symbols-outlined">event_available</span></div>
        </div>
        <h3 class="stat-value"><?= (int) $stats[0]['value'] ?></h3>
    </div>
    <div class="stat-card card">
        <div class="stat-card-header">
            <span class="small-text">Registered Students</span>
            <div class="stat-icon"><span class="material-symbols-outlined">group</span></div>
        </div>
        <h3 class="stat-value"><?= (int) $stats[1]['value'] ?></h3>
    </div>
    <div class="stat-card card">
        <div class="stat-card-header">
            <span class="small-text">Conflict Flags</span>
            <div class="stat-icon"><span class="material-symbols-outlined">warning</span></div>
        </div>
        <h3 class="stat-value"><?= (int) $stats[2]['value'] ?></h3>
    </div>
</section>

<!-- Featured Events Grid -->
<section style="margin-top:3rem;">
    <div style="display:flex; justify-content:space-between; align-items:end; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:1rem; margin-bottom:2rem;">
        <div>
            <h2 style="font-size:1.8rem; margin:0 0 0.25rem 0; font-family:'Space Grotesk', sans-serif;">Featured Events</h2>
            <p class="small-text" style="margin:0;">Top tier competitions open for registration</p>
        </div>
        <a href="/student/browse.php" style="color:var(--accent-primary); font-size:0.9rem; font-weight:700; display:flex; align-items:center; gap:0.25rem;">
            View All <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
        </a>
    </div>

    <div class="grid grid-3">
        <?php
        $categoryIcons = [
            'CTF' => 'vpn_key',
            'Hackathon' => 'terminal',
            'Robotics' => 'precision_manufacturing',
            'Gaming' => 'sports_esports',
            'Coding' => 'code',
            'AI/ML' => 'smart_toy',
            'Other' => 'category',
        ];
        foreach ($featured as $competition):
            $cat = $competition['category'];
            $icon = $categoryIcons[$cat] ?? 'category';
            $colors = category_colors();
            $catColor = $colors[$cat] ?? $colors['Other'];
            $regCount = competition_registered_count($pdo, (int)$competition['id']);
            $maxPart = $competition['max_participants'] !== null ? (int)$competition['max_participants'] : 0;
            $isBookmarked = false;
            if ($currentUser) {
                $isBookmarked = competition_is_bookmarked($pdo, (int)$competition['id'], (int)$currentUser['id']);
            }
        ?>
            <article class="card <?= e(strtolower($cat)) ?>" style="display:flex; flex-direction:column; justify-content:space-between; height:100%; border-top: 4px solid <?= $catColor ?>;">
                <div class="card-body" style="padding:1.5rem; display:flex; flex-direction:column; flex:1;">
                    <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:1.5rem;">
                        <span class="badge" <?= category_badge_style($cat) ?> style="font-size:0.7rem; font-weight:700; padding:0.3rem 0.6rem; display:inline-flex; align-items:center; gap:0.35rem;">
                            <span class="material-symbols-outlined" style="font-size:14px; color:#fff;"><?= $icon ?></span>
                            <?= e($cat) ?>
                        </span>
                        <button class="bookmark-btn <?= $isBookmarked ? 'bookmarked' : '' ?>" data-bookmark-toggle data-competition-id="<?= (int)$competition['id'] ?>" style="background:transparent; border:0; color:<?= $isBookmarked ? '#ff9a4a' : 'var(--text-secondary)' ?>; cursor:pointer;">
                            <span class="material-symbols-outlined" style="font-size:20px;"><?= $isBookmarked ? 'bookmark' : 'bookmark_border' ?></span>
                        </button>
                    </div>

                    <h3 class="card-title" style="font-size:1.3rem; margin:0 0 1rem 0; font-family:'Space Grotesk', sans-serif;"><a href="<?= e(competition_url($competition)) ?>"><?= e($competition['title']) ?></a></h3>
                    
                    <div style="display:flex; flex-direction:column; gap:0.5rem; margin-bottom:2rem; color:var(--text-secondary); font-size:0.9rem;">
                        <div style="display:flex; align-items:center; gap:0.5rem;">
                            <span class="material-symbols-outlined" style="font-size:16px;">schedule</span>
                            <span>Starts <?= e(date('M d', strtotime($competition['event_start']))) ?></span>
                        </div>
                        <div style="display:flex; align-items:center; gap:0.5rem;">
                            <span class="material-symbols-outlined" style="font-size:16px;">group</span>
                            <span><?= $regCount ?><?= $maxPart > 0 ? ' / ' . $maxPart : '' ?> participants</span>
                        </div>
                    </div>

                    <div style="margin-top:auto; padding-top:1rem; border-top:1px solid rgba(255,255,255,0.05); display:flex; justify-content:space-between; align-items:center;">
                        <div style="display:flex; align-items:center; gap:0.25rem;">
                            <div style="width:24px; height:24px; border-radius:50%; background:rgba(255,255,255,0.03); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; font-size:0.65rem; color:var(--text-secondary); font-weight:700;">+<?= $regCount ?></div>
                        </div>
                        <a href="<?= e(competition_url($competition)) ?>" class="btn btn-outline" style="padding:0.4rem 1rem; font-size:0.8rem; border-radius:var(--radius-sm);">Details</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const isLoggedIn = <?= json_encode(is_logged_in()) ?>;
    
    document.querySelectorAll('[data-bookmark-toggle]').forEach((button) => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            if (!isLoggedIn) {
                window.location.href = '/auth/login.php';
                return;
            }
            
            const competitionId = button.getAttribute('data-competition-id');
            if (!competitionId) return;
            
            try {
                const response = await fetch('/api/bookmark.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ competition_id: competitionId })
                });
                const data = await response.json();
                const icon = button.querySelector('.material-symbols-outlined');
                
                if (data.bookmarked) {
                    button.classList.add('bookmarked');
                    button.style.color = '#ff9a4a';
                    if (icon) icon.textContent = 'bookmark';
                } else {
                    button.classList.remove('bookmarked');
                    button.style.color = 'var(--text-secondary)';
                    if (icon) icon.textContent = 'bookmark_border';
                }
            } catch (err) {
                console.error('Error toggling bookmark:', err);
            }
        });
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
