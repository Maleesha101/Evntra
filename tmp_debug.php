<?php
require_once __DIR__ . '/includes/functions.php';
$filters = [
  'search' => 'sec',
  'category' => '',
  'status' => 'all',
  'venue' => 'all',
  'date_from' => '',
  'date_to' => '',
  'sort' => 'newest',
  'page' => 1,
  'per_page' => 10,
  'user_id' => 0,
];

$conditions = ['c.status IN ("published", "ongoing")'];
$params = [':user_id' => (int) ($filters['user_id'] ?? 0)];
if (!empty($filters['search'])) {
    $conditions[] = '(c.title LIKE :search OR c.description LIKE :search OR c.venue LIKE :search)';
    $params[':search'] = '%' . $filters['search'] . '%';
}
if (!empty($filters['category'])) {
    $categories = array_filter(array_map('trim', explode(',', (string) $filters['category'])));
    if ($categories !== []) {
        $placeholders = [];
        foreach ($categories as $index => $category) {
            $placeholder = ':category_' . $index;
            $placeholders[] = $placeholder;
            $params[$placeholder] = $category;
        }
        $conditions[] = 'c.category IN (' . implode(',', $placeholders) . ')';
    }
}
if (!empty($filters['date_from'])) {
    $conditions[] = 'c.event_start >= :date_from';
    $params[':date_from'] = $filters['date_from'];
}
if (!empty($filters['date_to'])) {
    $conditions[] = 'c.event_end <= :date_to';
    $params[':date_to'] = $filters['date_to'];
}
$whereSql = implode(' AND ', $conditions);
$countSql = 'SELECT COUNT(*) FROM competitions c INNER JOIN users u ON c.organizer_id = u.id WHERE ' . $whereSql;
$countParams = [];
foreach ($params as $key => $value) {
    if ($key !== ':user_id' && strpos($countSql, $key) !== false) {
        $countParams[$key] = $value;
    }
}
echo "countSql=\n" . $countSql . "\n";
echo "params=\n";
var_export($params);
echo "\ncountParams=\n";
var_export($countParams);
echo "\n";
try {
    $pdo = app_pdo();
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($countParams);
    echo "rowcount=" . $stmt->fetchColumn() . "\n";
} catch (Exception $e) {
    echo get_class($e) . ': ' . $e->getMessage() . "\n";
    echo "SQL=" . $countSql . "\n";
    echo "countParams=\n";
    var_export($countParams);
    echo "\n";
}
?>
