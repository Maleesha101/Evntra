<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
require_login(['admin']);
$pdo = require __DIR__ . '/../config/db.php';
$pageTitle = 'Conflict Report | Evntra';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    run_conflict_scan($pdo);
    flash('success', 'Conflict scan refreshed.');
}

$rows = conflict_report_rows($pdo);
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
    <div class="section-head">
        <div>
            <h1>Conflict report</h1>
            <p>Overlapping competitions detected by the scheduler.</p>
        </div>
        <form method="post">
            <?= csrf_field() ?>
            <button class="btn btn-primary" type="submit">Run scan</button>
        </form>
    </div>
</section>

<div class="grid grid-3" style="margin-top:1.5rem;">
    <?php foreach ($rows as $row): ?>
        <?php
        $severity = $row['severity'];
        $glowClass = $severity === 'high' ? 'glow-red active-pulse' : ($severity === 'medium' ? 'glow-orange active-pulse' : 'glow-blue');
        $badgeClass = $severity === 'high' ? 'bg-[#ff4757]/10 text-[#ff4757] border-[#ff4757]/20' : ($severity === 'medium' ? 'bg-[#ffa502]/10 text-[#ffa502] border-[#ffa502]/20' : 'bg-[#3498db]/10 text-[#3498db] border-[#3498db]/20');
        $bulletColor = $severity === 'high' ? '#ff4757' : ($severity === 'medium' ? '#ffa502' : '#3498db');
        ?>
        <div class="panel <?= $glowClass ?>" style="display:flex; flex-direction:column; padding:1.5rem; justify-content:space-between; height:100%;">
            <div>
                <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:1rem;">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold uppercase border <?= $badgeClass ?>" style="display:inline-flex; align-items:center;">
                        <span class="w-1.5 h-1.5 rounded-full" style="background: <?= $bulletColor ?>; <?= $severity === 'high' ? 'animation: conflictPulse 2s infinite;' : '' ?>"></span>
                        <?= e(ucfirst($severity)) ?> Conflict
                    </span>
                    <span class="small-text"><?= e(date('M d', strtotime($row['flagged_at']))) ?></span>
                </div>

                <div class="space-y-4" style="margin-bottom:1.5rem;">
                    <div class="overlap-details">
                        <span class="overlap-details-label">Competition A</span>
                        <a href="<?= e(competition_url(['slug' => $row['competition_a_slug']])) ?>" style="display:block; font-size:1.1rem; font-weight:700; color:var(--text-primary); margin-top:0.25rem;" class="truncate"><?= e($row['competition_a_title']) ?></a>
                    </div>
                    
                    <div class="overlap-indicator">
                        <div class="overlap-icon">
                            <span class="material-symbols-outlined" style="font-size:16px; color:<?= $bulletColor ?>; transform:rotate(90deg);">compare_arrows</span>
                        </div>
                    </div>
                    
                    <div class="overlap-details">
                        <span class="overlap-details-label">Competition B</span>
                        <a href="<?= e(competition_url(['slug' => $row['competition_b_slug']])) ?>" style="display:block; font-size:1.1rem; font-weight:700; color:var(--text-primary); margin-top:0.25rem;" class="truncate"><?= e($row['competition_b_title']) ?></a>
                    </div>
                </div>
            </div>
            
            <div class="conflict-card-actions">
                <a href="/organizer/edit-competition.php?id=<?= (int)$row['competition_a_id'] ?>" class="btn btn-outline" style="flex:1; padding:0.5rem 0.75rem; font-size:0.8rem; text-align:center;">Reschedule</a>
                <button class="btn btn-primary" style="background:rgba(255, 255, 255, 0.05); color:var(--text-primary); flex:1; padding:0.5rem 0.75rem; font-size:0.8rem; border:1px solid var(--border);">Acknowledge</button>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php if (!$rows): ?>
    <div class="panel" style="text-align:center; padding:3rem 0;">
        <span class="material-symbols-outlined text-primary" style="font-size:3rem; margin-bottom:1rem;">check_circle</span>
        <h3>No conflict flags recorded</h3>
        <p>The scheduler hasn't detected any overlapping competition tracks.</p>
    </div>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
