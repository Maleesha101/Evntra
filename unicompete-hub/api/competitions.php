<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
$pdo = require __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

if (($_GET['format'] ?? '') === 'calendar') {
    echo json_encode(competition_calendar_events($pdo), JSON_UNESCAPED_SLASHES);
    exit;
}

$userId = (int) ($_SESSION['user_id'] ?? 0);
$result = competition_search_query([
    'search' => (string) ($_GET['search'] ?? ''),
    'category' => (string) ($_GET['category'] ?? ''),
    'status' => (string) ($_GET['status'] ?? 'all'),
    'venue' => (string) ($_GET['venue'] ?? 'all'),
    'date_from' => (string) ($_GET['date_from'] ?? ''),
    'date_to' => (string) ($_GET['date_to'] ?? ''),
    'sort' => (string) ($_GET['sort'] ?? 'newest'),
    'page' => (int) ($_GET['page'] ?? 1),
    'per_page' => (int) ($_GET['per_page'] ?? 10),
    'user_id' => $userId,
]);

$items = [];
foreach ($result['items'] as $competition) {
    $items[] = competition_card_payload($competition, $pdo, $userId);
}

echo json_encode([
    'items' => $items,
    'total' => $result['total'],
    'page' => $result['page'],
    'per_page' => $result['per_page'],
    'pages' => $result['pages'],
], JSON_UNESCAPED_SLASHES);
