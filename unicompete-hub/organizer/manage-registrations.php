<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
require_login(['organizer']);
$pdo = require __DIR__ . '/../config/db.php';
$currentUser = current_user();
$pageTitle = 'Manage Registrations | Evntra';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $registrationId = (int) ($_POST['registration_id'] ?? 0);
    $newStatus = (string) ($_POST['status'] ?? 'registered');
    if (in_array($newStatus, ['registered', 'waitlisted', 'cancelled'], true)) {
        $stmt = $pdo->prepare('UPDATE registrations r INNER JOIN competitions c ON r.competition_id = c.id SET r.status = ? WHERE r.id = ? AND c.organizer_id = ?');
        $stmt->execute([$newStatus, $registrationId, (int) $currentUser['id']]);
        flash('success', 'Registration updated.');
    }
}

$registrations = competition_registrations_for_organizer($pdo, (int) $currentUser['id']);

include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
    <h1>Manage registrations</h1>
    <p>Review students and update registration status where needed.</p>
</section>

<div class="table-card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr><th>Competition</th><th>Student</th><th>Status</th><th>Team</th><th>Registered</th><th>Update</th></tr>
            </thead>
            <tbody>
                <?php foreach ($registrations as $registration): ?>
                    <tr>
                        <td><?= e($registration['competition_title']) ?></td>
                        <td><?= e($registration['student_name']) ?></td>
                        <td><?= e($registration['status']) ?></td>
                        <td><?= e($registration['team_id'] ? 'Team #' . $registration['team_id'] : 'Solo') ?></td>
                        <td><?= e($registration['registered_at']) ?></td>
                        <td>
                            <form method="post" class="form-actions">
                                <?= csrf_field() ?>
                                <input type="hidden" name="registration_id" value="<?= (int) $registration['id'] ?>">
                                <select name="status">
                                    <option value="registered" <?= $registration['status'] === 'registered' ? 'selected' : '' ?>>Registered</option>
                                    <option value="waitlisted" <?= $registration['status'] === 'waitlisted' ? 'selected' : '' ?>>Waitlisted</option>
                                    <option value="cancelled" <?= $registration['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                                <button class="btn btn-outline" type="submit">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$registrations): ?><tr><td colspan="6">No registrations found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
