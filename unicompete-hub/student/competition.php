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

include __DIR__ . '/../includes/header.php';
?>
<section class="hero-panel">
    <div class="grid grid-2">
        <div>
            <span class="badge" <?= category_badge_style($competition['category']) ?>><?= e($competition['category']) ?></span>
            <h1><?= e($competition['title']) ?></h1>
            <p><?= e($competition['description']) ?></p>
            <div class="meta-row">
                <span>Venue: <?= e($competition['venue']) ?></span>
                <span>Prize: <?= e($competition['prize_pool'] ?: 'TBA') ?></span>
                <span>Team size: <?= (int) $competition['team_size_min'] ?>-<?= (int) $competition['team_size_max'] ?></span>
            </div>
            <div class="form-actions" style="margin-top:1.25rem;">
                <?php if (is_logged_in()): ?>
                    <a class="btn btn-primary" href="/student/register-event.php?id=<?= (int) $competition['id'] ?>"><?= e(competition_action_label($competition)) ?></a>
                <?php else: ?>
                    <a class="btn btn-primary" href="/auth/login.php">Login to register</a>
                <?php endif; ?>
                <button class="btn btn-outline" type="button" data-copy-link="<?= e(app_config()['app_url'] . competition_url($competition)) ?>">Share</button>
            </div>
        </div>
        <div class="panel">
            <h3>Registration countdown</h3>
            <p data-countdown data-end="<?= e($competition['registration_end']) ?>"></p>
            <h3>Participants</h3>
            <p><?= $registeredCount ?><?= $maxParticipants > 0 ? ' / ' . $maxParticipants : '' ?></p>
            <div class="progress"><div class="progress-bar" style="width:<?= $progressPercent ?>%"></div></div>
            <p class="small-text"><?= $isFull ? 'This competition is full. Waitlist may be available.' : ($registrationOpen ? 'Registration is open.' : 'Registration is closed.') ?></p>
        </div>
    </div>
</section>

<section class="dashboard-grid" style="margin-top:1.5rem;">
    <div class="panel">
        <h2>Eligibility and format</h2>
        <p><?= e($competition['eligibility'] ?: 'Open to qualified students.') ?></p>
        <div class="grid grid-2">
            <div><strong>Registration</strong><p><?= e($competition['registration_start']) ?> to <?= e($competition['registration_end']) ?></p></div>
            <div><strong>Event dates</strong><p><?= e($competition['event_start']) ?> to <?= e($competition['event_end']) ?></p></div>
        </div>
        <?php if ($isFull): ?>
            <div class="flash info">This event is currently full. Students can still join the waitlist during registration.</div>
        <?php endif; ?>
    </div>
    <div class="panel">
        <h2>Related competitions</h2>
        <div class="grid">
            <?php foreach ($relatedCompetitions as $related): ?>
                <article class="card">
                    <div class="card-body">
                        <span class="badge" <?= category_badge_style($related['category']) ?>><?= e($related['category']) ?></span>
                        <h3 class="card-title"><a href="<?= e(competition_url($related)) ?>"><?= e($related['title']) ?></a></h3>
                    </div>
                </article>
            <?php endforeach; ?>
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
