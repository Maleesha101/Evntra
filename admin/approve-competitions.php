<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
require_login(['admin']);
require_once __DIR__ . '/../includes/mailer.php';
$pdo = require __DIR__ . '/../config/db.php';
$pageTitle = 'Approve Competitions | Evntra';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $competitionId = (int) ($_POST['competition_id'] ?? 0);
    $action = (string) ($_POST['action'] ?? 'approve');
    $stmt = $pdo->prepare('SELECT c.*, u.full_name AS organizer_name, u.email AS organizer_email FROM competitions c INNER JOIN users u ON c.organizer_id = u.id WHERE c.id = ? LIMIT 1');
    $stmt->execute([$competitionId]);
    $competition = $stmt->fetch();
    if ($competition) {
        $newStatus = $action === 'approve' ? 'published' : 'cancelled';
        $updateStmt = $pdo->prepare('UPDATE competitions SET status = ? WHERE id = ?');
        $result = $updateStmt->execute([$newStatus, $competitionId]);
        
        if ($result) {
            // Verify the update worked
            $verifyStmt = $pdo->prepare('SELECT status FROM competitions WHERE id = ? LIMIT 1');
            $verifyStmt->execute([$competitionId]);
            $updatedCompetition = $verifyStmt->fetch();
            
            if ($updatedCompetition && $updatedCompetition['status'] === $newStatus) {
                // Clear any OPcache if enabled
                if (function_exists('opcache_reset')) {
                    @opcache_reset();
                }
                run_conflict_scan($pdo);
                create_notification($pdo, (int) $competition['organizer_id'], 'Your competition "' . $competition['title'] . '" has been ' . ($action === 'approve' ? 'approved' : 'rejected') . '.', competition_url($competition));
                send_approval_mail(['full_name' => $competition['organizer_name'], 'email' => $competition['organizer_email']], $competition, $action === 'approve');
                flash('success', 'Competition ' . ($action === 'approve' ? 'approved' : 'rejected') . '. It will appear in the calendar within 10 seconds.');
            } else {
                flash('error', 'Competition status update verification failed. Current status: ' . ($updatedCompetition['status'] ?? 'unknown') . '. Please refresh and try again.');
            }
        } else {
            flash('error', 'Failed to update competition status. Please try again.');
        }
    } else {
        flash('error', 'Competition not found.');
    }
}

$pending = pending_competitions($pdo);
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
    <h1>Approve competitions</h1>
    <p>Review organizer submissions before they become public.</p>
</section>

<div class="table-card">
    <div class="table-responsive">
        <table>
            <thead><tr><th>Title</th><th>Organizer</th><th>Category</th><th>Dates</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($pending as $competition): ?>
                    <tr>
                        <td><?= e($competition['title']) ?></td>
                        <td><?= e($competition['organizer_name']) ?></td>
                        <td><span class="badge" <?= category_badge_style($competition['category']) ?>><?= e($competition['category']) ?></span></td>
                        <td><?= e($competition['event_start']) ?> to <?= e($competition['event_end']) ?></td>
                        <td>
                            <form method="post" class="button-row">
                                <?= csrf_field() ?>
                                <input type="hidden" name="competition_id" value="<?= (int) $competition['id'] ?>">
                                <button class="btn btn-primary" name="action" value="approve" type="submit">Approve</button>
                                <button class="btn btn-danger" name="action" value="reject" type="submit">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$pending): ?><tr><td colspan="5">No pending competitions.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
