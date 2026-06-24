<?php
$pdo = require __DIR__ . '/config/db.php';

$stmt = $pdo->query('SELECT id, title, registration_start, registration_end, event_start, event_end, status FROM competitions ORDER BY id DESC LIMIT 5');
$competitions = $stmt->fetchAll();

echo "<pre>";
echo "Current time: " . date('Y-m-d H:i:s') . "\n\n";
foreach ($competitions as $comp) {
    echo "ID: {$comp['id']} | Title: {$comp['title']}\n";
    echo "  Status: {$comp['status']}\n";
    echo "  Registration: {$comp['registration_start']} to {$comp['registration_end']}\n";
    echo "  Event: {$comp['event_start']} to {$comp['event_end']}\n";
    
    $now = new DateTimeImmutable('now');
    $regStart = new DateTimeImmutable($comp['registration_start']);
    $regEnd = new DateTimeImmutable($comp['registration_end']);
    
    echo "  Registration Open? " . ($now >= $regStart && $now <= $regEnd ? 'YES' : 'NO') . "\n";
    echo "  Now >= RegStart? " . ($now >= $regStart ? 'YES' : 'NO') . "\n";
    echo "  Now <= RegEnd? " . ($now <= $regEnd ? 'YES' : 'NO') . "\n\n";
}
echo "</pre>";
