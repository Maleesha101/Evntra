<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
require_login();
$pdo = require __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$input = json_decode((string) file_get_contents('php://input'), true) ?: $_POST;
$action = (string) ($input['action'] ?? 'read');
$userId = (int) $_SESSION['user_id'];

if ($action === 'read-all') {
    $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')->execute([$userId]);
    echo json_encode(['success' => true, 'unread_count' => 0]);
    exit;
}

$notificationId = (int) ($input['id'] ?? 0);
if ($notificationId > 0) {
    mark_notification_read($pdo, $notificationId, $userId);
}

echo json_encode(['success' => true, 'unread_count' => unread_notification_count($pdo, $userId)]);
