<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
$pdo = require __DIR__ . '/../config/db.php';

// Only show this if user is admin
if (!current_user() || current_user()['role'] !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}

header('Content-Type: application/json; charset=utf-8');

// Get all competitions with their statuses
$stmt = $pdo->query('
    SELECT id, title, category, event_start, event_end, status, created_at
    FROM competitions
    ORDER BY created_at DESC
    LIMIT 50
');

$competitions = $stmt->fetchAll();

// Get count by status
$statusStmt = $pdo->query('
    SELECT status, COUNT(*) as count
    FROM competitions
    GROUP BY status
');

$statusCounts = $statusStmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Get only published/ongoing that should appear in calendar
$calendarStmt = $pdo->query('
    SELECT id, title, category, event_start, event_end, status
    FROM competitions
    WHERE status IN ("published", "ongoing")
    ORDER BY event_start ASC
');

$calendarEvents = $calendarStmt->fetchAll();

echo json_encode([
    'all_competitions' => $competitions,
    'status_counts' => $statusCounts,
    'calendar_events' => $calendarEvents,
    'total_all' => count($competitions),
    'total_calendar' => count($calendarEvents),
    'timestamp' => date('Y-m-d H:i:s'),
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
