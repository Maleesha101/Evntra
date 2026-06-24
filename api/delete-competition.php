<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
$pdo = require __DIR__ . '/../config/db.php';
require_login(['admin']);

header('Content-Type: application/json; charset=utf-8');

$input = json_decode((string) file_get_contents('php://input'), true) ?: $_POST;
$competitionId = (int) ($input['competition_id'] ?? 0);
$csrfToken = (string) ($input['csrf_token'] ?? '');

if ($competitionId <= 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Invalid competition id.']);
    exit;
}

if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token.']);
    exit;
}

$competition = get_competition_by_id($pdo, $competitionId);
if (!$competition) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Competition not found.']);
    exit;
}

$deleteStmt = $pdo->prepare('DELETE FROM competitions WHERE id = ?');
$deleteStmt->execute([$competitionId]);

if ($deleteStmt->rowCount() === 0) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to delete competition.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Competition deleted successfully.']);
