<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
require_login(['organizer']);
$pdo = require __DIR__ . '/../config/db.php';
$currentUser = current_user();
$competitionId = (int) ($_GET['id'] ?? $_POST['competition_id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM competitions WHERE id = ? AND organizer_id = ? LIMIT 1');
$stmt->execute([$competitionId, (int) $currentUser['id']]);
$competition = $stmt->fetch();
if (!$competition) {
    http_response_code(404);
    exit('Competition not found.');
}
$pageTitle = 'Edit Competition | Evntra';
$pageScripts = ['/assets/js/competition-form.js', '/assets/js/conflict-checker.js'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $result = save_competition_from_input($pdo, (int) $currentUser['id'], $_POST, $_FILES, $competition);
    if ($result['success']) {
        flash('success', $result['message']);
        redirect('/organizer/my-competitions.php');
    }
    flash('error', $result['message']);
}

include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
    <h1>Edit competition</h1>
    <p>Update your event details.</p>
</section>
<form method="post" enctype="multipart/form-data" class="form-card multi-step" data-competition-form>
    <?= csrf_field() ?>
    <input type="hidden" name="competition_id" value="<?= (int) $competition['id'] ?>">
    <div class="step-indicator">
        <span class="step-pill active" data-step-pill>Basic Info</span>
        <span class="step-pill" data-step-pill>Dates & Venue</span>
        <span class="step-pill" data-step-pill>Registration</span>
    </div>

    <section class="step-panel active" data-step-panel>
        <div class="grid grid-2">
            <div class="form-group"><label for="title">Title</label><input type="text" id="title" name="title" value="<?= e($competition['title']) ?>" required></div>
            <div class="form-group"><label for="category">Category</label><select id="category" name="category" data-category-input required><?php foreach (array_keys(category_colors()) as $category): ?><option value="<?= e($category) ?>" <?= $competition['category'] === $category ? 'selected' : '' ?>><?= e($category) ?></option><?php endforeach; ?></select></div>
        </div>
        <div class="form-group"><label for="description">Description</label><textarea id="description" name="description" required><?= e($competition['description']) ?></textarea></div>
        <div class="form-group"><label for="eligibility">Eligibility</label><textarea id="eligibility" name="eligibility"><?= e($competition['eligibility'] ?? '') ?></textarea></div>
        <div class="form-group"><label for="prize_pool">Prize pool</label><input type="text" id="prize_pool" name="prize_pool" value="<?= e($competition['prize_pool'] ?? '') ?>"></div>
        <div class="form-actions"><button type="button" class="btn btn-primary" data-next-step>Next</button></div>
    </section>

    <section class="step-panel" data-step-panel>
        <div class="grid grid-2">
            <div class="form-group"><label for="registration_start">Registration start</label><input type="datetime-local" id="registration_start" name="registration_start" value="<?= date('Y-m-d\TH:i', strtotime($competition['registration_start'])) ?>" required></div>
            <div class="form-group"><label for="registration_end">Registration end</label><input type="datetime-local" id="registration_end" name="registration_end" value="<?= date('Y-m-d\TH:i', strtotime($competition['registration_end'])) ?>" required></div>
            <div class="form-group"><label for="event_start">Event start</label><input type="datetime-local" id="event_start" name="event_start" data-event-start value="<?= date('Y-m-d\TH:i', strtotime($competition['event_start'])) ?>" required></div>
            <div class="form-group"><label for="event_end">Event end</label><input type="datetime-local" id="event_end" name="event_end" data-event-end value="<?= date('Y-m-d\TH:i', strtotime($competition['event_end'])) ?>" required></div>
        </div>
        <div class="form-group"><label for="venue">Venue</label><input type="text" id="venue" name="venue" value="<?= e($competition['venue']) ?>" required></div>
        <div class="form-group" data-conflict-warning></div>
        <div class="form-actions"><button type="button" class="btn btn-outline" data-back-step>Back</button><button type="button" class="btn btn-primary" data-next-step>Next</button></div>
    </section>

    <section class="step-panel" data-step-panel>
        <div class="grid grid-2">
            <div class="form-group"><label for="max_participants">Max participants</label><input type="number" id="max_participants" name="max_participants" min="1" value="<?= e((string) ($competition['max_participants'] ?? '')) ?>"></div>
            <div class="grid grid-2">
                <div class="form-group"><label for="team_size_min">Team size min</label><input type="number" id="team_size_min" name="team_size_min" min="1" value="<?= (int) $competition['team_size_min'] ?>"></div>
                <div class="form-group"><label for="team_size_max">Team size max</label><input type="number" id="team_size_max" name="team_size_max" min="1" value="<?= (int) $competition['team_size_max'] ?>"></div>
            </div>
        </div>
        <div class="form-group"><label for="banner_image">Banner image</label><input type="file" id="banner_image" name="banner_image" accept="image/png,image/jpeg,image/webp"><p class="form-help">Upload to replace the current banner.</p></div>
        <div class="form-actions"><button type="button" class="btn btn-outline" data-back-step>Back</button><button type="submit" class="btn btn-primary">Save changes</button></div>
    </section>
</form>
<?php include __DIR__ . '/../includes/footer.php'; ?>
