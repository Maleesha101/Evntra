<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
require_login();
$pdo = require __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$input = json_decode((string) file_get_contents('php://input'), true) ?: $_POST;
$competitionId = (int) ($input['competition_id'] ?? 0);
$userId = (int) $_SESSION['user_id'];

if ($competitionId <= 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Competition id is required.']);
    exit;
}

$stmt = $pdo->prepare('SELECT 1 FROM bookmarks WHERE user_id = ? AND competition_id = ? LIMIT 1');
$stmt->execute([$userId, $competitionId]);
$exists = (bool) $stmt->fetchColumn();

if ($exists) {
    $pdo->prepare('DELETE FROM bookmarks WHERE user_id = ? AND competition_id = ?')->execute([$userId, $competitionId]);
    $bookmarked = false;
} else {
    $pdo->prepare('INSERT INTO bookmarks (user_id, competition_id) VALUES (?, ?)')->execute([$userId, $competitionId]);
    $bookmarked = true;
}

$count = user_bookmark_count($pdo, $userId);

echo json_encode(['success' => true, 'bookmarked' => $bookmarked, 'count' => $count]);
