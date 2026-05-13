<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
require_login(['student']);
$pdo = require __DIR__ . '/../config/db.php';
$currentUser = current_user();
$pageTitle = 'My Registrations | Evntra';
$stmt = $pdo->prepare('SELECT r.status AS registration_status, r.registered_at, r.team_id, c.* , t.team_name FROM registrations r INNER JOIN competitions c ON r.competition_id = c.id LEFT JOIN teams t ON r.team_id = t.id WHERE r.user_id = ? ORDER BY r.registered_at DESC');
$stmt->execute([(int) $currentUser['id']]);
$registrations = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
    <h1>My registrations</h1>
    <p>Everything you have joined or waitlisted appears here.</p>
</section>

<div class="table-card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Competition</th>
                    <th>Status</th>
                    <th>Team</th>
                    <th>Registered</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registrations as $registration): ?>
                    <tr>
                        <td><?= e($registration['title']) ?></td>
                        <td><span class="badge" style="background:rgba(255,255,255,0.08);"><?= e($registration['registration_status']) ?></span></td>
                        <td><?= e($registration['team_name'] ?: 'Solo') ?></td>
                        <td><?= e($registration['registered_at']) ?></td>
                        <td><a class="btn btn-outline" href="<?= e(competition_url($registration)) ?>">Open</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$registrations): ?>
                    <tr><td colspan="5">You have not registered for any competitions yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
