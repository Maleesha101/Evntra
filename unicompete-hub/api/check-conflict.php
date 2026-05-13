<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
require_login(['organizer', 'admin']);
$pdo = require __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$input = json_decode((string) file_get_contents('php://input'), true) ?: $_GET;
$competitionId = (int) ($input['competition_id'] ?? 0);
$eventStart = (string) ($input['event_start'] ?? '');
$eventEnd = (string) ($input['event_end'] ?? '');
$category = (string) ($input['category'] ?? '');

if ($competitionId > 0) {
    $candidate = get_competition_by_id($pdo, $competitionId);
    if ($candidate) {
        $eventStart = $candidate['event_start'];
        $eventEnd = $candidate['event_end'];
        $category = $candidate['category'];
    }
}

if ($eventStart === '' || $eventEnd === '' || $category === '') {
    echo json_encode(['conflicts' => [], 'message' => '']);
    exit;
}

$candidate = [
    'id' => $competitionId,
    'category' => $category,
    'event_start' => $eventStart,
    'event_end' => $eventEnd,
];

$stmt = $pdo->prepare('SELECT * FROM competitions WHERE status IN ("published", "ongoing") AND id <> ?');
$stmt->execute([$competitionId]);
$published = $stmt->fetchAll();
$conflicts = [];

foreach ($published as $competition) {
    $severity = competition_conflict_severity($candidate, $competition);
    if ($severity !== null) {
        $conflicts[] = [
            'competition_id' => (int) $competition['id'],
            'title' => $competition['title'],
            'category' => $competition['category'],
            'severity' => $severity,
            'event_start' => $competition['event_start'],
            'event_end' => $competition['event_end'],
        ];
    }
}

$severityRank = ['high' => 3, 'medium' => 2, 'low' => 1];
usort($conflicts, static fn (array $left, array $right): int => ($severityRank[$right['severity']] ?? 0) <=> ($severityRank[$left['severity']] ?? 0));

if ($competitionId > 0) {
    $pdo->prepare('DELETE FROM conflict_flags WHERE competition_a_id = ? OR competition_b_id = ?')->execute([$competitionId, $competitionId]);
    $insert = $pdo->prepare('INSERT INTO conflict_flags (competition_a_id, competition_b_id, severity) VALUES (?, ?, ?)');
    foreach ($conflicts as $conflict) {
        $a = min($competitionId, $conflict['competition_id']);
        $b = max($competitionId, $conflict['competition_id']);
        $insert->execute([$a, $b, $conflict['severity']]);
    }
}

$message = $conflicts === [] ? 'No conflicts detected.' : count($conflicts) . ' potential conflict(s) detected.';
if ($conflicts !== []) {
    $message .= ' Highest severity: ' . strtoupper($conflicts[0]['severity']) . '.';
}

echo json_encode(['conflicts' => $conflicts, 'message' => $message], JSON_UNESCAPED_SLASHES);
