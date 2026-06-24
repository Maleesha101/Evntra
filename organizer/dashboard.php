<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
require_login(['organizer']);
$pdo = require __DIR__ . '/../config/db.php';
$currentUser = current_user();
$pageTitle = 'Organizer Dashboard | Evntra';
$pageScripts = ['https://cdn.jsdelivr.net/npm/chart.js'];
$stats = dashboard_stats_for_organizer($pdo, (int) $currentUser['id']);
$recent = recent_registrations($pdo, (int) $currentUser['id'], 6);
$chartData = organizer_chart_data($pdo, (int) $currentUser['id']);

include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
    <h1>Organizer dashboard</h1>
    <p>Track competition growth, upcoming events, and new registrations.</p>
    <div class="button-row">
        <a class="btn btn-primary" href="/organizer/create-competition.php">Create New Competition</a>
        <a class="btn btn-outline" href="/organizer/my-competitions.php">View All Competitions</a>
    </div>
</section>

<section class="kpi-grid">
    <div class="stat-card card">
        <div class="stat-card-header">
            <span class="small-text">Total competitions</span>
            <div class="stat-icon"><span class="material-symbols-outlined">trophy</span></div>
        </div>
        <h3 class="stat-value"><?= $stats['total_competitions'] ?></h3>
    </div>
    <div class="stat-card card">
        <div class="stat-card-header">
            <span class="small-text">Total registrations</span>
            <div class="stat-icon"><span class="material-symbols-outlined">person_add</span></div>
        </div>
        <h3 class="stat-value"><?= $stats['total_registrations'] ?></h3>
    </div>
    <div class="stat-card card">
        <div class="stat-card-header">
            <span class="small-text">Upcoming events</span>
            <div class="stat-icon"><span class="material-symbols-outlined">event_available</span></div>
        </div>
        <h3 class="stat-value"><?= $stats['upcoming_events'] ?></h3>
    </div>
    <div class="stat-card card">
        <div class="stat-card-header">
            <span class="small-text">Pending approval</span>
            <div class="stat-icon"><span class="material-symbols-outlined">hourglass_empty</span></div>
        </div>
        <h3 class="stat-value"><?= $stats['pending_approval'] ?></h3>
    </div>
</section>

<section class="dashboard-grid" style="margin-top:1.5rem;">
    <div class="panel chart-card">
        <div class="section-head"><h2>Registrations per competition</h2></div>
        <div class="chart-wrap"><canvas id="organizerBarChart"></canvas></div>
    </div>
    <div class="panel">
        <h2>Recent registrations</h2>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Competition</th><th>Student</th><th>Registered</th></tr></thead>
                <tbody>
                    <?php foreach ($recent as $row): ?>
                        <tr>
                            <td><?= e($row['competition_title']) ?></td>
                            <td><?= e($row['student_name']) ?></td>
                            <td><?= e($row['registered_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$recent): ?><tr><td colspan="3">No registrations yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>
    window.addEventListener('DOMContentLoaded', () => {
        const organizerChartData = <?= json_encode($chartData, JSON_UNESCAPED_SLASHES) ?>;
        new Chart(document.getElementById('organizerBarChart'), {
            type: 'bar',
            data: {
                labels: organizerChartData.bar.labels,
                datasets: [{
                    label: 'Registrations',
                    data: organizerChartData.bar.values,
                    backgroundColor: 'rgba(84, 233, 138, 0.75)',
                    borderColor: '#54e98a',
                    borderWidth: 1,
                    borderRadius: 8,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e2024',
                        titleColor: '#e2e2e8',
                        bodyColor: '#e2e2e8',
                        borderColor: 'rgba(84, 233, 138, 0.25)',
                        borderWidth: 1,
                        displayColors: false,
                        padding: 10,
                        bodyFont: { family: 'Hanken Grotesk' },
                        titleFont: { family: 'Space Grotesk' }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#bbcbbb', font: { family: 'Hanken Grotesk' } }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#bbcbbb', font: { family: 'Hanken Grotesk', precision: 0 } }
                    },
                },
            },
        });
    });
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
