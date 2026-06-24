<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
require_login(['student']);
require_once __DIR__ . '/../includes/mailer.php';
$pdo = require __DIR__ . '/../config/db.php';
$currentUser = current_user();
$competitionId = (int) ($_GET['id'] ?? $_POST['competition_id'] ?? 0);
$competition = get_competition_by_id($pdo, $competitionId);
if (!$competition) {
    http_response_code(404);
    exit('Competition not found.');
}
$pageTitle = 'Register for ' . $competition['title'] . ' | Evntra';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $result = register_user_for_competition($pdo, (int) $currentUser['id'], $competitionId, $_POST);
    if ($result['success']) {
        create_notification($pdo, (int) $currentUser['id'], $result['message'] . ' for ' . $competition['title'], competition_url($competition));
        create_notification($pdo, (int) $competition['organizer_id'], $currentUser['full_name'] . ' registered for ' . $competition['title'], competition_url($competition));
        send_registration_confirmation_mail($currentUser, $competition, $result['status']);
        flash('success', $result['message']);
        redirect('/student/my-registrations.php');
    }
    flash('error', $result['message']);
}

include __DIR__ . '/../includes/header.php';
?>
<section class="hero-panel">
    <span class="badge" <?= category_badge_style($competition['category']) ?>><?= e($competition['category']) ?></span>
    <h1><?= e($competition['title']) ?></h1>
    <p><?= e($competition['description']) ?></p>
</section>

<section class="form-card" style="margin-top:1.5rem;">
    <h2>Registration form</h2>
    <form method="post" action="/api/register.php" class="multi-step">
        <?= csrf_field() ?>
        <input type="hidden" name="competition_id" value="<?= (int) $competition['id'] ?>">
        <input type="hidden" name="redirect_to" value="/student/my-registrations.php">
        <?php if ((int) $competition['team_size_max'] > 1): ?>
            <div class="grid grid-2">
                <div class="form-group">
                    <label for="team_name">Create a team</label>
                    <input type="text" id="team_name" name="team_name" placeholder="Enter a new team name">
                </div>
                <div class="form-group">
                    <label for="invite_code">Join with invite code</label>
                    <input type="text" id="invite_code" name="invite_code" placeholder="Invite code from captain">
                </div>
            </div>
            <label class="small-text" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-top: 0.5rem;">
                <input type="checkbox" name="waitlist" value="1" style="width: 1.15rem; height: 1.15rem; margin: 0; cursor: pointer;">
                Add me to the waitlist if the event is full
            </label>
        <?php else: ?>
            <p>This is a solo event. Click register to join immediately.</p>
        <?php endif; ?>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Confirm registration</button>
            <a class="btn btn-outline" href="<?= e(competition_url($competition)) ?>">Back to details</a>
        </div>
    </form>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
