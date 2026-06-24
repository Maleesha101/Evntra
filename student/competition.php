<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
$pdo = require __DIR__ . '/../config/db.php';
$currentUser = current_user();
$slug = (string) ($_GET['slug'] ?? '');
$competitionId = (int) ($_GET['id'] ?? 0);
$competition = $slug !== '' ? get_competition_by_slug($pdo, $slug) : get_competition_by_id($pdo, $competitionId);
if (!$competition) {
    http_response_code(404);
    exit('Competition not found.');
}
competition_views_throttled($pdo, (int) $competition['id']);
$pageTitle = $competition['title'] . ' | Evntra';
$pageScripts = [];
$registeredCount = competition_registered_count($pdo, (int) $competition['id']);
$maxParticipants = $competition['max_participants'] !== null ? (int) $competition['max_participants'] : 0;
$progressPercent = $maxParticipants > 0 ? min(100, (int) round(($registeredCount / $maxParticipants) * 100)) : 0;
$relatedStmt = $pdo->prepare('SELECT * FROM competitions WHERE category = ? AND id <> ? AND status IN ("published", "ongoing") ORDER BY event_start ASC LIMIT 3');
$relatedStmt->execute([$competition['category'], (int) $competition['id']]);
$relatedCompetitions = $relatedStmt->fetchAll();
$registrationOpen = competition_registration_open($competition);
$isFull = $maxParticipants > 0 && $registeredCount >= $maxParticipants;

$isRegistered = false;
if ($currentUser) {
    $stmt = $pdo->prepare('SELECT 1 FROM registrations WHERE competition_id = ? AND user_id = ? AND status <> "cancelled" LIMIT 1');
    $stmt->execute([(int) $competition['id'], (int) $currentUser['id']]);
    $isRegistered = (bool) $stmt->fetchColumn();
}

include __DIR__ . '/../includes/header.php';
?>
<!-- Details Hero Banner -->
<section class="details-hero">
    <div class="hero-bg" style="background-image: url('<?= e($competition['banner_image'] ?: category_fallback_image($competition['category'])) ?>')"></div>
    <div class="hero-gradient"></div>
    <div class="hero-content">
        <div class="hero-status-row">
            <span class="badge" <?= category_badge_style($competition['category']) ?>><?= e($competition['category']) ?></span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-container-high/80 backdrop-blur-md text-on-surface font-bold text-xs border border-outline-variant/30">
                <span class="w-2 h-2 rounded-full <?= $registrationOpen ? 'bg-primary animate-pulse' : 'bg-danger' ?>" style="background: <?= $registrationOpen ? 'var(--accent-primary)' : 'var(--accent-danger)' ?>"></span>
                <?= $isFull ? 'FULL' : ($registrationOpen ? 'REGISTRATION OPEN' : 'REGISTRATION CLOSED') ?>
            </span>
        </div>
        <h1 class="hero-title"><?= e($competition['title']) ?></h1>
        <p class="hero-subtitle"><?= e(substr($competition['description'], 0, 220)) ?><?= strlen($competition['description']) > 220 ? '...' : '' ?></p>
    </div>
</section>

<!-- Details Grid Layout -->
<section class="details-layout">
    <!-- Main Left Column -->
    <div class="space-y-6">
        <!-- Overview Panel -->
        <div class="panel">
            <h2 class="section-head" style="margin-bottom:1rem; font-size:1.4rem;">
                <span style="display:flex; align-items:center; gap:0.5rem;">
                    <span class="material-symbols-outlined text-primary">info</span> Overview
                </span>
            </h2>
            <p style="margin-bottom:1.5rem; line-height:1.7;"><?= e($competition['description']) ?></p>
            
            <div class="grid grid-2" style="border-top:1px solid var(--border); padding-top:1.5rem;">
                <div style="display:flex; gap:0.75rem; align-items:start;">
                    <div style="padding:0.5rem; border-radius:50%; background:rgba(255,255,255,0.03); color:var(--accent-primary); display:inline-flex;">
                        <span class="material-symbols-outlined">group</span>
                    </div>
                    <div>
                        <strong style="display:block; margin-bottom:0.15rem;">Eligibility</strong>
                        <span class="small-text"><?= e($competition['eligibility'] ?: 'Open to qualified university students.') ?></span>
                    </div>
                </div>
                <div style="display:flex; gap:0.75rem; align-items:start;">
                    <div style="padding:0.5rem; color:var(--accent-primary); display:inline-flex;">
                        <span class="material-symbols-outlined">location_on</span>
                    </div>
                    <div>
                        <strong style="display:block; margin-bottom:0.15rem;">Format & Venue</strong>
                        <span class="small-text"><?= e($competition['venue']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prize Pool Bento Podium Grid -->
        <div>
            <h2 class="section-head" style="margin-bottom:1rem; font-size:1.4rem;">
                <span style="display:flex; align-items:center; gap:0.5rem;">
                    <span class="material-symbols-outlined text-warning" style="color:var(--accent-warning);">trophy</span> Prize Pool
                </span>
            </h2>
            <div class="prize-bento-grid">
                <!-- 2nd Place -->
                <div class="prize-card silver">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 2.25rem;">military_tech</span>
                    <span class="prize-rank">2ND PLACE</span>
                    <span class="prize-amount">TBA</span>
                </div>
                <!-- 1st Place (Gold, Champion) -->
                <div class="prize-card gold">
                    <span class="material-symbols-outlined text-warning" style="font-size: 3rem; color: var(--accent-warning); font-variation-settings: 'FILL' 1;">trophy</span>
                    <span class="prize-rank">CHAMPION</span>
                    <span class="prize-amount"><?= e($competition['prize_pool'] ?: 'TBA') ?></span>
                    <span class="prize-detail">Plus Winner Badge</span>
                </div>
                <!-- 3rd Place -->
                <div class="prize-card bronze">
                    <span class="material-symbols-outlined" style="font-size: 2.25rem; color:#cd7f32;">military_tech</span>
                    <span class="prize-rank">3RD PLACE</span>
                    <span class="prize-amount">TBA</span>
                </div>
            </div>
        </div>

        <!-- Event Schedule Timeline -->
        <div class="panel">
            <h2 class="section-head" style="margin-bottom:1rem; font-size:1.4rem;">
                <span style="display:flex; align-items:center; gap:0.5rem;">
                    <span class="material-symbols-outlined text-primary">calendar_month</span> Event Schedule
                </span>
            </h2>
            
            <div class="timeline-container">
                <!-- Point 1 -->
                <div class="timeline-item active">
                    <div class="timeline-line"></div>
                    <div class="timeline-marker"><div class="timeline-dot"></div></div>
                    <div>
                        <div class="timeline-header">
                            <h3 class="timeline-title">Registration Opens</h3>
                            <span class="timeline-date">START</span>
                        </div>
                        <p class="timeline-description">Registration opens for eligible students on <?= e(date('M d, Y H:i', strtotime($competition['registration_start']))) ?>.</p>
                    </div>
                </div>
                <!-- Point 2 -->
                <div class="timeline-item">
                    <div class="timeline-line"></div>
                    <div class="timeline-marker"><div class="timeline-dot"></div></div>
                    <div>
                        <div class="timeline-header">
                            <h3 class="timeline-title">Registration Closes</h3>
                            <span class="timeline-date">DEADLINE</span>
                        </div>
                        <p class="timeline-description">Teams must submit registrations before <?= e(date('M d, Y H:i', strtotime($competition['registration_end']))) ?>.</p>
                    </div>
                </div>
                <!-- Point 3 -->
                <div class="timeline-item">
                    <div class="timeline-line"></div>
                    <div class="timeline-marker"><div class="timeline-dot"></div></div>
                    <div>
                        <div class="timeline-header">
                            <h3 class="timeline-title">Event Starts</h3>
                            <span class="timeline-date">KICKOFF</span>
                        </div>
                        <p class="timeline-description">The competition starts on <?= e(date('M d, Y H:i', strtotime($competition['event_start']))) ?>.</p>
                    </div>
                </div>
                <!-- Point 4 -->
                <div class="timeline-item">
                    <div class="timeline-line"></div>
                    <div class="timeline-marker"><div class="timeline-dot"></div></div>
                    <div>
                        <div class="timeline-header">
                            <h3 class="timeline-title">Grand Finals & Closure</h3>
                            <span class="timeline-date">FINISH</span>
                        </div>
                        <p class="timeline-description">The event concludes on <?= e(date('M d, Y H:i', strtotime($competition['event_end']))) ?>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sticky Sidebar Column -->
    <div class="sticky-sidebar">
        <!-- Action Card -->
        <div class="panel" style="border-color: rgba(84, 233, 138, 0.25); background: linear-gradient(180deg, rgba(26,28,32,0.85) 0%, rgba(17,19,23,0.85) 100%);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; padding-bottom:1rem; border-bottom:1px solid rgba(255,255,255,0.05);">
                <div>
                    <span class="small-text" style="display:block; text-transform:uppercase; font-weight:700; letter-spacing:0.05em; margin-bottom:0.25rem;">Registration Closes In</span>
                    <strong style="font-size:1.3rem; display:block;" data-countdown data-end="<?= e($competition['registration_end']) ?>">Calculating...</strong>
                </div>
                <div style="width:2.5rem; height:2.5rem; border-radius:50%; background:rgba(255,255,255,0.03); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; color:var(--accent-primary);">
                    <span class="material-symbols-outlined">timer</span>
                </div>
            </div>

            <div style="display:flex; flex-direction:column; gap:0.85rem; margin-bottom:1.5rem; font-size:0.92rem;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="color:var(--text-secondary); display:flex; align-items:center; gap:0.35rem;"><span class="material-symbols-outlined" style="font-size:16px;">groups</span> Team Size</span>
                    <strong style="color:var(--text-primary);"><?= (int) $competition['team_size_min'] ?> - <?= (int) $competition['team_size_max'] ?> members</strong>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="color:var(--text-secondary); display:flex; align-items:center; gap:0.35rem;"><span class="material-symbols-outlined" style="font-size:16px;">location_on</span> Venue</span>
                    <strong style="color:var(--text-primary); truncate; max-width: 150px; text-align:right;" title="<?= e($competition['venue']) ?>"><?= e($competition['venue']) ?></strong>
                </div>
                
                <div class="spots-count-wrapper">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="color:var(--text-secondary);">Capacity Status</span>
                        <strong style="color:var(--accent-primary);"><?= $registeredCount ?><?= $maxParticipants > 0 ? ' / ' . $maxParticipants : '' ?> filled</strong>
                    </div>
                    <?php if ($maxParticipants > 0): ?>
                        <div class="spots-bar-bg">
                            <div class="spots-bar-fill" style="width: <?= $progressPercent ?>%"></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div style="display:flex; flex-direction:column; gap:0.75rem;">
                <?php if (is_logged_in()): ?>
                    <?php if ($isRegistered): ?>
                        <button class="btn" style="width:100%; background: rgba(255, 255, 255, 0.05); color: var(--text-secondary); border: 1px solid var(--border); cursor: not-allowed; pointer-events: none;" disabled>Already Registered</button>
                    <?php else: ?>
                        <a class="btn btn-primary" style="width:100%;" href="/student/register-event.php?id=<?= (int) $competition['id'] ?>"><?= e(competition_action_label($competition)) ?></a>
                    <?php endif; ?>
                <?php else: ?>
                    <a class="btn btn-primary" style="width:100%;" href="/auth/login.php">Login to register</a>
                <?php endif; ?>
                <button class="btn btn-outline" style="width:100%;" type="button" data-copy-link="<?= e(app_config()['app_url'] . competition_url($competition)) ?>">
                    <span class="material-symbols-outlined" style="font-size:18px;">share</span> Share Event
                </button>
            </div>
            
            <p class="small-text" style="text-align:center; margin-top:0.75rem;"><?= $isFull ? 'This competition is full.' : ($registrationOpen ? 'Open for registration.' : 'Registration is closed.') ?></p>
        </div>

        <!-- Related Competitions Panel -->
        <div class="panel">
            <h3 style="font-size:1.15rem; font-weight:700; margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined text-primary" style="font-size:20px;">explore</span> Related Competitions
            </h3>
            <div class="grid" style="gap:0.75rem;">
                <?php foreach ($relatedCompetitions as $related): ?>
                    <article class="card" style="border: 1px solid rgba(255,255,255,0.03);">
                        <div class="card-body" style="padding:0.75rem 1rem;">
                            <span class="badge" <?= category_badge_style($related['category']) ?> style="font-size:0.65rem; padding:0.2rem 0.5rem; margin-bottom:0.35rem;"><?= e($related['category']) ?></span>
                            <h4 class="card-title" style="font-size:0.95rem; margin-top:0.5rem;"><a href="<?= e(competition_url($related)) ?>" style="color:var(--text-primary); hover:color:var(--accent-primary);"><?= e($related['title']) ?></a></h4>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php if (!$relatedCompetitions): ?>
                    <p class="small-text">No related competitions found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
  (function () {
    const countdown = document.querySelector('[data-countdown]');
    if (!countdown) return;
    const end = new Date(countdown.getAttribute('data-end'));
    const tick = () => {
      const diff = end - new Date();
      if (diff <= 0) {
        countdown.textContent = 'Registration closed';
        return;
      }
      const days = Math.floor(diff / 86400000);
      const hours = Math.floor((diff % 86400000) / 3600000);
      const minutes = Math.floor((diff % 3600000) / 60000);
      countdown.textContent = `${days}d ${hours}h ${minutes}m remaining`;
    };
    tick();
    setInterval(tick, 60000);
  }());
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
