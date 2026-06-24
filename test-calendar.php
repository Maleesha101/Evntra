<?php
// Simple test to verify competition status updates are working
// Access at: /test-calendar.php

require_once __DIR__ . '/includes/auth-guard.php';
require_once __DIR__ . '/config/db.php';

$pdo = app_pdo();

echo '<h1>Competition Calendar Diagnostic</h1>';
echo '<p style="color: #666;">This page helps debug why competitions don\'t appear in the calendar.</p>';
echo '<hr>';

// Check database connection
echo '<h2>1. Database Connection</h2>';
try {
    $stmt = $pdo->query('SELECT COUNT(*) FROM competitions');
    $count = $stmt->fetchColumn();
    echo '<p style="color: green;"><strong>✓ Database connected</strong> - Found ' . $count . ' total competitions</p>';
} catch (Exception $e) {
    echo '<p style="color: red;"><strong>✗ Database error:</strong> ' . $e->getMessage() . '</p>';
}

// Show competitions by status
echo '<h2>2. Competitions by Status</h2>';
$stmt = $pdo->query('SELECT status, COUNT(*) as count FROM competitions GROUP BY status ORDER BY status');
$statusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($statusCounts) === 0) {
    echo '<p style="color: red;">No competitions in database</p>';
} else {
    echo '<table border="1" cellpadding="10" style="border-collapse: collapse;">';
    echo '<tr style="background: #f0f0f0;"><th>Status</th><th>Count</th></tr>';
    foreach ($statusCounts as $row) {
        $color = $row['status'] === 'published' ? 'green' : ($row['status'] === 'pending' ? 'orange' : 'gray');
        echo '<tr><td style="color: ' . $color . ';"><strong>' . htmlspecialchars($row['status']) . '</strong></td><td>' . $row['count'] . '</td></tr>';
    }
    echo '</table>';
}

// Show published/ongoing competitions
echo '<h2>3. Calendar Eligible Competitions (published/ongoing)</h2>';
$stmt = $pdo->prepare('
    SELECT id, title, category, status, event_start, event_end
    FROM competitions
    WHERE status IN ("published", "ongoing")
    ORDER BY event_start ASC
');
$stmt->execute();
$calendarComps = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($calendarComps) === 0) {
    echo '<p style="color: red;"><strong>NO COMPETITIONS FOUND FOR CALENDAR!</strong></p>';
    echo '<p>This is likely the problem. Check:</p>';
    echo '<ul style="color: red;">';
    echo '<li>Did you approve any competitions in /admin/approve-competitions.php ?</li>';
    echo '<li>Is the admin approval actually updating the status to "published"?</li>';
    echo '<li>Check the recent competitions table below</li>';
    echo '</ul>';
} else {
    echo '<p style="color: green;"><strong>✓ Found ' . count($calendarComps) . ' competitions for calendar</strong></p>';
    echo '<table border="1" cellpadding="10" style="border-collapse: collapse;">';
    echo '<tr style="background: #f0f0f0;"><th>ID</th><th>Title</th><th>Category</th><th>Status</th><th>Event Start</th><th>Event End</th></tr>';
    foreach ($calendarComps as $comp) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($comp['id']) . '</td>';
        echo '<td>' . htmlspecialchars($comp['title']) . '</td>';
        echo '<td>' . htmlspecialchars($comp['category']) . '</td>';
        echo '<td style="color: green;"><strong>' . htmlspecialchars($comp['status']) . '</strong></td>';
        echo '<td>' . htmlspecialchars($comp['event_start']) . '</td>';
        echo '<td>' . htmlspecialchars($comp['event_end']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
}

// Test the actual API response
echo '<h2>4. Calendar API Response Test</h2>';
echo '<p>Testing /api/competitions.php?format=calendar</p>';
try {
    $events = competition_calendar_events($pdo);
    echo '<p style="color: green;"><strong>✓ API returned ' . count($events) . ' events</strong></p>';
    
    if (count($events) > 0) {
        echo '<pre style="background: #f5f5f5; padding: 10px; overflow: auto;">';
        echo json_encode($events, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        echo '</pre>';
    } else {
        echo '<p style="color: red;">API returned empty array. The database query is not finding published competitions.</p>';
    }
} catch (Exception $e) {
    echo '<p style="color: red;"><strong>API Error:</strong> ' . $e->getMessage() . '</p>';
}

// Show all recent competitions
echo '<h2>5. Recent Competitions (Last 10)</h2>';
$stmt = $pdo->query('
    SELECT id, title, status, event_start, event_end, created_at
    FROM competitions
    ORDER BY created_at DESC
    LIMIT 10
');
$allComps = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($allComps) === 0) {
    echo '<p>No competitions in database</p>';
} else {
    echo '<table border="1" cellpadding="10" style="border-collapse: collapse;">';
    echo '<tr style="background: #f0f0f0;"><th>ID</th><th>Title</th><th>Status</th><th>Event Start</th><th>Event End</th><th>Created</th></tr>';
    foreach ($allComps as $comp) {
        $statusColor = '';
        if ($comp['status'] === 'published' || $comp['status'] === 'ongoing') {
            $statusColor = 'color: green;';
        } elseif ($comp['status'] === 'pending') {
            $statusColor = 'color: orange;';
        }
        
        echo '<tr>';
        echo '<td>' . htmlspecialchars($comp['id']) . '</td>';
        echo '<td>' . htmlspecialchars($comp['title']) . '</td>';
        echo '<td style="' . $statusColor . '"><strong>' . htmlspecialchars($comp['status']) . '</strong></td>';
        echo '<td>' . htmlspecialchars($comp['event_start']) . '</td>';
        echo '<td>' . htmlspecialchars($comp['event_end']) . '</td>';
        echo '<td>' . htmlspecialchars($comp['created_at']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
}

// Instructions
echo '<h2>6. Troubleshooting Steps</h2>';
echo '<ol>';
echo '<li>Create a new competition as organizer</li>';
echo '<li>Go to <a href="/admin/approve-competitions.php">/admin/approve-competitions.php</a></li>';
echo '<li>Click "Approve" on the competition</li>';
echo '<li>Come back to this page and refresh (F5)</li>';
echo '<li>You should see the competition in section 3 with status "published"</li>';
echo '<li>Go to <a href="/student/browse.php">/student/browse.php</a> and click "Calendar View"</li>';
echo '<li>Open browser console (F12) and look for "✓ Fetched calendar events:" message</li>';
echo '<li>The event should appear on the calendar</li>';
echo '</ol>';

echo '<h2>7. Browser Console Logs</h2>';
echo '<p>Open Developer Tools (F12) → Console tab and check for these messages:</p>';
echo '<ul>';
echo '<li><span style="color: green;">✓</span> "Calendar DOM element found, starting initialization..."</li>';
echo '<li><span style="color: green;">✓</span> "✓ Fetched calendar events: X events"</li>';
echo '<li><span style="color: green;">✓</span> "✓ Calendar rendered successfully"</li>';
echo '</ul>';
echo '<p>If you see errors, copy them below for debugging.</p>';

echo '<hr>';
echo '<p><small>Page generated: ' . date('Y-m-d H:i:s') . ' | <a href="?">Refresh page</a></small></p>';
?>