<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
require_login(['organizer']);
require_once __DIR__ . '/../includes/mailer.php';
$pdo = require __DIR__ . '/../config/db.php';
$currentUser = current_user();
$pageTitle = 'Create Competition | Evntra';
$pageScripts = ['/assets/js/competition-form.js', '/assets/js/conflict-checker.js'];
$competition = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $result = save_competition_from_input($pdo, (int) $currentUser['id'], $_POST, $_FILES);
    if ($result['success']) {
        create_notification($pdo, (int) $currentUser['id'], 'Competition submitted for approval: ' . $_POST['title'], '/organizer/my-competitions.php');
        flash('success', $result['message']);
        redirect('/organizer/my-competitions.php');
    }
    flash('error', $result['message']);
}

include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
    <h1>Create competition</h1>
    <p>Submit a new competition for admin approval.</p>
</section>

<form method="post" enctype="multipart/form-data" class="form-card multi-step" data-competition-form>
    <?= csrf_field() ?>
    <div class="step-indicator">
        <span class="step-pill active" data-step-pill>Basic Info</span>
        <span class="step-pill" data-step-pill>Dates & Venue</span>
        <span class="step-pill" data-step-pill>Registration</span>
    </div>

    <section class="step-panel active" data-step-panel>
        <div class="grid grid-2">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" data-category-input required>
                    <?php foreach (array_keys(category_colors()) as $category): ?>
                        <option value="<?= e($category) ?>"><?= e($category) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" required></textarea>
        </div>
        <div class="form-group">
            <label for="eligibility">Eligibility</label>
            <textarea id="eligibility" name="eligibility"></textarea>
        </div>
        <div class="form-group">
            <label for="prize_pool">Prize pool</label>
            <input type="text" id="prize_pool" name="prize_pool">
        </div>
        <div class="form-actions"><button type="button" class="btn btn-primary" data-next-step>Next</button></div>
    </section>

    <section class="step-panel" data-step-panel>
        <div class="grid grid-2">
            <div class="form-group">
                <label for="registration_start">Registration start</label>
                <input type="datetime-local" id="registration_start" name="registration_start" required>
            </div>
            <div class="form-group">
                <label for="registration_end">Registration end</label>
                <input type="datetime-local" id="registration_end" name="registration_end" required>
            </div>
            <div class="form-group">
                <label for="event_start">Event start</label>
                <input type="datetime-local" id="event_start" name="event_start" data-event-start required>
            </div>
            <div class="form-group">
                <label for="event_end">Event end</label>
                <input type="datetime-local" id="event_end" name="event_end" data-event-end required>
            </div>
        </div>
        <div class="form-group">
            <label for="venue">Venue</label>
            <input type="text" id="venue" name="venue" placeholder="Online or physical address" required>
        </div>
        <div data-conflict-warning></div>
        <div class="form-actions"><button type="button" class="btn btn-outline" data-back-step>Back</button><button type="button" class="btn btn-primary" data-next-step>Next</button></div>
    </section>

    <section class="step-panel" data-step-panel>
        <div class="grid grid-2">
            <div class="form-group">
                <label for="max_participants">Max participants</label>
                <input type="number" id="max_participants" name="max_participants" min="1" placeholder="Leave blank for unlimited">
            </div>
            <div class="grid grid-2">
                <div class="form-group">
                    <label for="team_size_min">Team size min</label>
                    <input type="number" id="team_size_min" name="team_size_min" min="1" value="1">
                </div>
                <div class="form-group">
                    <label for="team_size_max">Team size max</label>
                    <input type="number" id="team_size_max" name="team_size_max" min="1" value="1">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="banner_image">Banner image</label>
            <input type="file" id="banner_image" name="banner_image" accept="image/png,image/jpeg,image/webp">
            <p class="form-help">JPG, PNG, or WEBP up to 2MB.</p>
        </div>
        <div class="form-actions"><button type="button" class="btn btn-outline" data-back-step>Back</button><button type="submit" class="btn btn-primary">Submit for approval</button></div>
    </section>
</form>
<?php include __DIR__ . '/../includes/footer.php'; ?>
