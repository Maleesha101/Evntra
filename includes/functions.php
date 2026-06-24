<?php

declare(strict_types=1);

function app_start_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function app_root_path(string $path = ''): string
{
    $root = dirname(__DIR__);
    return $path === '' ? $root : $root . '/' . ltrim($path, '/');
}

function app_env(string $key, mixed $default = null): mixed
{
    static $env = null;
    if ($env === null) {
        $env = [];
        $envFile = app_root_path('.env');
        if (is_file($envFile)) {
            foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }
                [$name, $value] = explode('=', $line, 2);
                $env[trim($name)] = trim($value, "\"' ");
            }
        }
    }

    return $_ENV[$key] ?? $_SERVER[$key] ?? $env[$key] ?? getenv($key) ?: $default;
}

function app_config(): array
{
    return [
        'db_host' => app_env('DB_HOST', '127.0.0.1'),
        'db_port' => app_env('DB_PORT', '3306'),
        'db_name' => app_env('DB_NAME', 'unicompete_hub'),
        'db_user' => app_env('DB_USER', 'root'),
        'db_pass' => app_env('DB_PASS', 'Mk17628!'),
        'app_url' => rtrim((string) app_env('APP_URL', 'http://localhost/evntra'), '/'),
        'smtp_host' => app_env('SMTP_HOST', ''),
        'smtp_port' => (int) app_env('SMTP_PORT', 587),
        'smtp_user' => app_env('SMTP_USER', ''),
        'smtp_pass' => app_env('SMTP_PASS', ''),
        'smtp_encryption' => app_env('SMTP_ENCRYPTION', 'tls'),
        'mail_from' => app_env('MAIL_FROM', 'no-reply@evntra.test'),
        'mail_from_name' => app_env('MAIL_FROM_NAME', 'Evntra'),
    ];
}

function app_pdo(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = app_config();
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $config['db_host'], $config['db_port'], $config['db_name']);
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function flash(string $key, ?string $message = null): ?string
{
    app_start_session();
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    if (!empty($_SESSION['flash'][$key])) {
        $value = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $value;
    }

    return null;
}

function render_flash_messages(): string
{
    $html = '';
    foreach (['success', 'error', 'info'] as $type) {
        $message = flash($type);
        if ($message !== null) {
            $html .= '<div class="flash ' . e($type) . '">' . e($message) . '</div>';
        }
    }
    return $html;
}

function csrf_token(): string
{
    app_start_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    app_start_session();
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || $token === '' || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }
}

function is_logged_in(): bool
{
    app_start_session();
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }

    $pdo = app_pdo();
    $stmt = $pdo->prepare('SELECT id, full_name, email, role, university, profile_picture, email_verified, is_active FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function require_login(array $roles = []): void
{
    app_start_session();
    if (empty($_SESSION['user_id'])) {
        flash('error', 'Please log in to continue.');
        redirect('/auth/login.php');
    }

    if ($roles !== []) {
        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, $roles, true)) {
            http_response_code(403);
            exit('You do not have permission to access this page.');
        }
    }
}

function require_guest(): void
{
    if (is_logged_in()) {
        $role = $_SESSION['role'] ?? 'student';
        redirect('/' . $role . '/dashboard.php');
    }
}

function slugify(string $value): string
{
    $value = iconv('UTF-8', 'ASCII//TRANSLIT', $value) ?: $value;
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim($value, '-');
    return $value !== '' ? $value : 'competition-' . substr(bin2hex(random_bytes(4)), 0, 8);
}

function password_is_strong(string $password): bool
{
    return strlen($password) >= 8
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/[0-9]/', $password)
        && preg_match('/[^A-Za-z0-9]/', $password);
}

function category_colors(): array
{
    return [
        'CTF' => '#ff4757',
        'Hackathon' => '#6c63ff',
        'Robotics' => '#00d4aa',
        'Gaming' => '#ffa502',
        'Coding' => '#2ed573',
        'AI/ML' => '#ff6b81',
        'Other' => '#a4b0be',
    ];
}

function category_badge_style(string $category): string
{
    $color = category_colors()[$category] ?? category_colors()['Other'];
    return 'style="background:' . e($color) . ';color:#fff;"';
}

function adjacent_categories(): array
{
    return [
        'CTF' => ['Coding', 'Hackathon'],
        'Hackathon' => ['CTF', 'AI/ML', 'Coding'],
        'Robotics' => ['Coding', 'AI/ML'],
        'Gaming' => ['Coding'],
        'Coding' => ['CTF', 'Hackathon', 'Robotics', 'Gaming', 'AI/ML'],
        'AI/ML' => ['Hackathon', 'Robotics', 'Coding'],
        'Other' => [],
    ];
}

function competition_url(array $competition): string
{
    if (!empty($competition['slug'])) {
        return '/student/competition.php?slug=' . urlencode((string) $competition['slug']);
    }
    return '/student/competition.php?id=' . (int) $competition['id'];
}

function competition_registration_open(array $competition): bool
{
    $now = new DateTimeImmutable('now');
    return $now >= new DateTimeImmutable($competition['registration_start']) && $now <= new DateTimeImmutable($competition['registration_end']);
}

function competition_is_full(PDO $pdo, int $competitionId): bool
{
    $competition = get_competition_by_id($pdo, $competitionId);
    if (!$competition || $competition['max_participants'] === null) {
        return false;
    }
    return competition_registered_count($pdo, $competitionId) >= (int) $competition['max_participants'];
}

function get_competition_by_id(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT c.*, u.full_name AS organizer_name, u.email AS organizer_email FROM competitions c INNER JOIN users u ON c.organizer_id = u.id WHERE c.id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function get_competition_by_slug(PDO $pdo, string $slug): ?array
{
    $stmt = $pdo->prepare('SELECT c.*, u.full_name AS organizer_name, u.email AS organizer_email FROM competitions c INNER JOIN users u ON c.organizer_id = u.id WHERE c.slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function competition_registered_count(PDO $pdo, int $competitionId): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM registrations WHERE competition_id = ? AND status IN ("registered", "waitlisted")');
    $stmt->execute([$competitionId]);
    return (int) $stmt->fetchColumn();
}

function user_registration_count(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM registrations WHERE user_id = ? AND status <> "cancelled"');
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

function user_bookmark_count(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM bookmarks WHERE user_id = ?');
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

function competitions_this_month_count(PDO $pdo): int
{
    $stmt = $pdo->query('SELECT COUNT(*) FROM competitions WHERE status IN ("published", "ongoing") AND MONTH(event_start) = MONTH(CURRENT_DATE()) AND YEAR(event_start) = YEAR(CURRENT_DATE())');
    return (int) $stmt->fetchColumn();
}

function unread_notification_count(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

function latest_notifications(PDO $pdo, int $userId, int $limit = 5): array
{
    $stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ' . (int) $limit);
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function create_notification(PDO $pdo, int $userId, string $message, ?string $link = null): void
{
    $stmt = $pdo->prepare('INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $message, $link]);
}

function mark_notification_read(PDO $pdo, int $notificationId, int $userId): void
{
    $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
    $stmt->execute([$notificationId, $userId]);
}

function competition_views_throttled(PDO $pdo, int $competitionId): void
{
    app_start_session();
    $_SESSION['viewed_competitions'] ??= [];
    $key = (string) $competitionId;
    if (!in_array($key, $_SESSION['viewed_competitions'], true)) {
        $stmt = $pdo->prepare('UPDATE competitions SET views = views + 1 WHERE id = ?');
        $stmt->execute([$competitionId]);
        $_SESSION['viewed_competitions'][] = $key;
    }
}

function normalize_registration_window(string $start, string $end): bool
{
    return new DateTimeImmutable($start) <= new DateTimeImmutable($end);
}

function competition_conflict_severity(array $competitionA, array $competitionB): ?string
{
    $aStart = new DateTimeImmutable($competitionA['event_start']);
    $aEnd = new DateTimeImmutable($competitionA['event_end']);
    $bStart = new DateTimeImmutable($competitionB['event_start']);
    $bEnd = new DateTimeImmutable($competitionB['event_end']);

    $sameCategory = $competitionA['category'] === $competitionB['category'];
    $overlaps = $aStart <= $bEnd && $bStart <= $aEnd;
    if (!$overlaps && !competition_within_days($aStart, $bStart, 3)) {
        return null;
    }

    if ($sameCategory && $aStart->format('Y-m-d') === $bStart->format('Y-m-d')) {
        return 'high';
    }

    if ($sameCategory && competition_within_days($aStart, $bStart, 3)) {
        return 'medium';
    }

    if (in_array($competitionB['category'], adjacent_categories()[$competitionA['category']] ?? [], true) && competition_within_days($aStart, $bStart, 3)) {
        return 'low';
    }

    return null;
}

function competition_within_days(DateTimeImmutable $first, DateTimeImmutable $second, int $days): bool
{
    $diff = abs($first->setTime(0, 0)->getTimestamp() - $second->setTime(0, 0)->getTimestamp());
    return $diff <= $days * 86400;
}

function run_conflict_scan(PDO $pdo): array
{
    $competitions = $pdo->query('SELECT * FROM competitions WHERE status IN ("published", "ongoing") ORDER BY event_start ASC')->fetchAll();
    $pdo->exec('DELETE FROM conflict_flags');
    $insert = $pdo->prepare('INSERT INTO conflict_flags (competition_a_id, competition_b_id, severity) VALUES (?, ?, ?)');
    $results = [];

    $count = count($competitions);
    for ($i = 0; $i < $count; $i++) {
        for ($j = $i + 1; $j < $count; $j++) {
            $severity = competition_conflict_severity($competitions[$i], $competitions[$j]);
            if ($severity !== null) {
                $insert->execute([(int) $competitions[$i]['id'], (int) $competitions[$j]['id'], $severity]);
                $results[] = [
                    'competition_a_id' => (int) $competitions[$i]['id'],
                    'competition_b_id' => (int) $competitions[$j]['id'],
                    'severity' => $severity,
                ];
            }
        }
    }

    return $results;
}

function handle_banner_upload(array $file): ?string
{
    if (empty($file['name']) || !is_uploaded_file($file['tmp_name'])) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Banner upload failed.');
    }

    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        throw new RuntimeException('Banner must be smaller than 2MB.');
    }

    $allowedMimeTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mimeType = mime_content_type($file['tmp_name']) ?: '';
    if (!array_key_exists($mimeType, $allowedMimeTypes)) {
        throw new RuntimeException('Only JPG, PNG, or WEBP files are allowed.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowedMimeTypes[$mimeType];
    $targetDir = app_root_path('uploads/banners');
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0775, true);
    }

    $targetPath = $targetDir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException('Unable to save uploaded banner.');
    }

    return '/uploads/banners/' . $filename;
}

function login_rate_limited(): bool
{
    app_start_session();
    $attempts = $_SESSION['login_attempts'] ?? [];
    $now = time();
    $attempts = array_values(array_filter($attempts, fn ($timestamp) => ($now - (int) $timestamp) < 600));
    $_SESSION['login_attempts'] = $attempts;
    return count($attempts) >= 5;
}

function record_login_attempt(): void
{
    app_start_session();
    $_SESSION['login_attempts'] ??= [];
    $_SESSION['login_attempts'][] = time();
}

function clear_login_attempts(): void
{
    app_start_session();
    unset($_SESSION['login_attempts']);
}

function competition_team_mode(array $competition): bool
{
    return (int) $competition['team_size_max'] > 1;
}

function competition_member_count(PDO $pdo, int $competitionId): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM registrations WHERE competition_id = ? AND status IN ("registered", "waitlisted")');
    $stmt->execute([$competitionId]);
    return (int) $stmt->fetchColumn();
}

function competition_available_spots(PDO $pdo, int $competitionId): ?int
{
    $competition = get_competition_by_id($pdo, $competitionId);
    if (!$competition || $competition['max_participants'] === null) {
        return null;
    }
    return max(0, (int) $competition['max_participants'] - competition_member_count($pdo, $competitionId));
}

function registration_exists(PDO $pdo, int $competitionId, int $userId): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM registrations WHERE competition_id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$competitionId, $userId]);
    return (bool) $stmt->fetchColumn();
}

function get_user_by_email(PDO $pdo, string $email): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function get_user_by_id(PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function create_password_reset_token(PDO $pdo, int $userId): string
{
    $token = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))');
    $stmt->execute([$userId, $token]);
    return $token;
}

function consume_password_reset_token(PDO $pdo, string $token): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM password_resets WHERE token = ? AND used_at IS NULL AND expires_at > NOW() LIMIT 1');
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    if (!$reset) {
        return null;
    }

    $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?')->execute([(int) $reset['id']]);
    return $reset;
}

function competition_registration_status_label(array $competition): string
{
    $open = competition_registration_open($competition);
    $full = $competition['max_participants'] !== null && competition_registered_count(app_pdo(), (int) $competition['id']) >= (int) $competition['max_participants'];
    if (!$open) {
        return 'Closed';
    }
    return $full ? 'Full / Waitlist' : 'Open';
}

function competition_card_payload(array $competition, PDO $pdo, int $userId = 0): array
{
    $colors = category_colors();
    return [
        'id' => (int) $competition['id'],
        'slug' => $competition['slug'],
        'title' => $competition['title'],
        'description' => $competition['description'],
        'category' => $competition['category'],
        'category_color' => $colors[$competition['category']] ?? $colors['Other'],
        'banner_image' => $competition['banner_image'],
        'registration_start' => $competition['registration_start'],
        'registration_end' => $competition['registration_end'],
        'event_start' => $competition['event_start'],
        'event_end' => $competition['event_end'],
        'venue' => $competition['venue'],
        'status' => $competition['status'],
        'registration_status' => competition_registration_status($competition),
        'views' => (int) $competition['views'],
        'is_bookmarked' => $userId > 0 ? competition_is_bookmarked($pdo, (int) $competition['id'], $userId) : false,
        'registered_count' => competition_registered_count($pdo, (int) $competition['id']),
        'max_participants' => $competition['max_participants'] !== null ? (int) $competition['max_participants'] : null,
        'url' => competition_url($competition),
    ];
}

function competition_is_bookmarked(PDO $pdo, int $competitionId, int $userId): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM bookmarks WHERE competition_id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$competitionId, $userId]);
    return (bool) $stmt->fetchColumn();
}

function bookmarks_for_user(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare('SELECT c.* FROM bookmarks b INNER JOIN competitions c ON b.competition_id = c.id WHERE b.user_id = ? ORDER BY b.created_at DESC');
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function competitions_for_user_dashboard(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare('SELECT c.* FROM registrations r INNER JOIN competitions c ON r.competition_id = c.id WHERE r.user_id = ? AND r.status <> "cancelled" ORDER BY c.event_start ASC LIMIT 5');
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function upcoming_public_competitions(PDO $pdo, int $limit = 4): array
{
    $stmt = $pdo->prepare('SELECT c.*, u.full_name AS organizer_name FROM competitions c INNER JOIN users u ON c.organizer_id = u.id WHERE c.status IN ("published", "ongoing") ORDER BY c.created_at DESC LIMIT ?');
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function recent_registrations(PDO $pdo, int $organizerId, int $limit = 8): array
{
    $sql = 'SELECT r.registered_at, u.full_name AS student_name, c.title AS competition_title
            FROM registrations r
            INNER JOIN competitions c ON r.competition_id = c.id
            INNER JOIN users u ON r.user_id = u.id
            WHERE c.organizer_id = ?
            ORDER BY r.registered_at DESC
            LIMIT ?';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, $organizerId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function organizer_competitions(PDO $pdo, int $organizerId): array
{
    $stmt = $pdo->prepare('SELECT * FROM competitions WHERE organizer_id = ? ORDER BY created_at DESC');
    $stmt->execute([$organizerId]);
    return $stmt->fetchAll();
}

function dashboard_stats_for_organizer(PDO $pdo, int $organizerId): array
{
    $summary = [
        'total_competitions' => 0,
        'total_registrations' => 0,
        'upcoming_events' => 0,
        'pending_approval' => 0,
        'total_views' => 0,
    ];

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM competitions WHERE organizer_id = ?');
    $stmt->execute([$organizerId]);
    $summary['total_competitions'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM registrations r INNER JOIN competitions c ON r.competition_id = c.id WHERE c.organizer_id = ? AND r.status <> "cancelled"');
    $stmt->execute([$organizerId]);
    $summary['total_registrations'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM competitions WHERE organizer_id = ? AND event_start >= NOW()');
    $stmt->execute([$organizerId]);
    $summary['upcoming_events'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM competitions WHERE organizer_id = ? AND status = "pending"');
    $stmt->execute([$organizerId]);
    $summary['pending_approval'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COALESCE(SUM(views), 0) FROM competitions WHERE organizer_id = ?');
    $stmt->execute([$organizerId]);
    $summary['total_views'] = (int) $stmt->fetchColumn();

    return $summary;
}

function all_users(PDO $pdo): array
{
    return $pdo->query('SELECT id, full_name, email, role, university, email_verified, is_active, created_at FROM users ORDER BY created_at DESC')->fetchAll();
}

function pending_competitions(PDO $pdo): array
{
    $sql = 'SELECT c.*, u.full_name AS organizer_name, u.email AS organizer_email
            FROM competitions c
            INNER JOIN users u ON c.organizer_id = u.id
            WHERE c.status = "pending"
            ORDER BY c.created_at DESC';
    return $pdo->query($sql)->fetchAll();
}

function conflict_report_rows(PDO $pdo): array
{
    $sql = 'SELECT cf.*, ca.title AS competition_a_title, cb.title AS competition_b_title, ca.slug AS competition_a_slug, cb.slug AS competition_b_slug
            FROM conflict_flags cf
            INNER JOIN competitions ca ON cf.competition_a_id = ca.id
            INNER JOIN competitions cb ON cf.competition_b_id = cb.id
            ORDER BY FIELD(cf.severity, "high", "medium", "low"), cf.flagged_at DESC';
    return $pdo->query($sql)->fetchAll();
}

function competition_search_query(array $filters): array
{
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

    if (!empty($filters['status']) && $filters['status'] !== 'all') {
        $conditions[] = match ($filters['status']) {
            'open' => 'c.registration_start <= NOW() AND c.registration_end >= NOW()',
            'upcoming' => 'c.event_start >= NOW()',
            'ongoing' => 'c.event_start <= NOW() AND c.event_end >= NOW()',
            default => '1=1',
        };
    }

    if (!empty($filters['venue'])) {
        if ($filters['venue'] === 'online') {
            $conditions[] = 'LOWER(c.venue) = "online"';
        } elseif ($filters['venue'] === 'offline') {
            $conditions[] = 'LOWER(c.venue) <> "online"';
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
    $sort = $filters['sort'] ?? 'newest';
    $orderSql = match ($sort) {
        'oldest' => ' ORDER BY c.created_at ASC',
        'soonest' => ' ORDER BY c.event_end ASC',
        'popular' => ' ORDER BY c.views DESC, c.created_at DESC',
        default => ' ORDER BY c.created_at DESC',
    };

    $page = max(1, (int) ($filters['page'] ?? 1));
    $perPage = max(1, min(50, (int) ($filters['per_page'] ?? 10)));
    $offset = ($page - 1) * $perPage;

    $countSql = 'SELECT COUNT(*) FROM competitions c INNER JOIN users u ON c.organizer_id = u.id WHERE ' . $whereSql;
    $countParams = $params;
    unset($countParams[':user_id']);
    $countStmt = app_pdo()->prepare($countSql);
    foreach ($countParams as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $total = (int) $countStmt->fetchColumn();

    $listSql = 'SELECT c.*, u.full_name AS organizer_name,
                       EXISTS(SELECT 1 FROM bookmarks b WHERE b.competition_id = c.id AND b.user_id = :user_id) AS is_bookmarked,
                       (SELECT COUNT(*) FROM registrations r WHERE r.competition_id = c.id AND r.status IN ("registered", "waitlisted")) AS registered_count
                FROM competitions c
                INNER JOIN users u ON c.organizer_id = u.id
                WHERE ' . $whereSql . $orderSql . ' LIMIT :limit OFFSET :offset';
    $stmt = app_pdo()->prepare($listSql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return [
        'items' => $stmt->fetchAll(),
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'pages' => max(1, (int) ceil($total / $perPage)),
    ];
}

function competition_calendar_events(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT id, title, category, event_start, event_end, slug FROM competitions WHERE status IN ("published", "ongoing") ORDER BY event_start ASC');
    $events = [];
    foreach ($stmt->fetchAll() as $row) {
        $events[] = [
            'id' => (int) $row['id'],
            'title' => $row['title'],
            'start' => $row['event_start'],
            'end' => $row['event_end'],
            'url' => competition_url($row),
            'color' => category_colors()[$row['category']] ?? category_colors()['Other'],
            'extendedProps' => ['category' => $row['category']],
        ];
    }
    return $events;
}

function competition_registrations_for_organizer(PDO $pdo, int $organizerId): array
{
    $sql = 'SELECT r.*, u.full_name AS student_name, u.email AS student_email, c.title AS competition_title
            FROM registrations r
            INNER JOIN competitions c ON r.competition_id = c.id
            INNER JOIN users u ON r.user_id = u.id
            WHERE c.organizer_id = ?
            ORDER BY r.registered_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$organizerId]);
    return $stmt->fetchAll();
}

function avg_team_size(PDO $pdo, int $organizerId): float
{
    $sql = 'SELECT AVG(team_counts.team_size) FROM (
                SELECT COUNT(*) AS team_size
                FROM registrations r
                INNER JOIN competitions c ON r.competition_id = c.id
                WHERE c.organizer_id = ? AND r.team_id IS NOT NULL
                GROUP BY r.team_id
            ) team_counts';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$organizerId]);
    $value = $stmt->fetchColumn();
    return $value !== null ? (float) $value : 0.0;
}

function conversion_rate(PDO $pdo, int $organizerId): float
{
    $stmt = $pdo->prepare('SELECT COALESCE(SUM(views), 0) FROM competitions WHERE organizer_id = ?');
    $stmt->execute([$organizerId]);
    $views = (int) $stmt->fetchColumn();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM registrations r INNER JOIN competitions c ON r.competition_id = c.id WHERE c.organizer_id = ?');
    $stmt->execute([$organizerId]);
    $regs = (int) $stmt->fetchColumn();
    return $views > 0 ? round(($regs / $views) * 100, 1) : 0.0;
}

function organizer_chart_data(PDO $pdo, int $organizerId): array
{
    $competitions = organizer_competitions($pdo, $organizerId);
    $labels = [];
    $registrations = [];
    foreach ($competitions as $competition) {
        $labels[] = $competition['title'];
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM registrations WHERE competition_id = ? AND status <> "cancelled"');
        $stmt->execute([(int) $competition['id']]);
        $registrations[] = (int) $stmt->fetchColumn();
    }

    $overTimeStmt = $pdo->prepare('SELECT DATE(registered_at) AS day, COUNT(*) AS total FROM registrations r INNER JOIN competitions c ON r.competition_id = c.id WHERE c.organizer_id = ? AND r.registered_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY) GROUP BY DATE(registered_at) ORDER BY day ASC');
    $overTimeStmt->execute([$organizerId]);
    $overTime = $overTimeStmt->fetchAll();

    $days = [];
    $dayTotals = [];
    $period = new DatePeriod(new DateTimeImmutable('-29 days'), new DateInterval('P1D'), new DateTimeImmutable('+1 day'));
    $map = [];
    foreach ($overTime as $row) {
        $map[$row['day']] = (int) $row['total'];
    }
    foreach ($period as $date) {
        $key = $date->format('Y-m-d');
        $days[] = $date->format('M d');
        $dayTotals[] = $map[$key] ?? 0;
    }

    $categoryStmt = $pdo->prepare('SELECT c.category, COUNT(*) AS total FROM registrations r INNER JOIN competitions c ON r.competition_id = c.id WHERE c.organizer_id = ? GROUP BY c.category');
    $categoryStmt->execute([$organizerId]);
    $categoryRows = $categoryStmt->fetchAll();

    return [
        'bar' => ['labels' => $labels, 'values' => $registrations],
        'line' => ['labels' => $days, 'values' => $dayTotals],
        'doughnut' => [
            'labels' => array_column($categoryRows, 'category'),
            'values' => array_map('intval', array_column($categoryRows, 'total')),
        ],
    ];
}

function notification_link_or_default(?string $link): string
{
    return $link ?: '#';
}

function user_public_competitions(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare('SELECT c.* FROM competitions c INNER JOIN registrations r ON c.id = r.competition_id WHERE r.user_id = ? AND r.status <> "cancelled" ORDER BY c.event_start ASC');
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function users_count(PDO $pdo): int
{
    return (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
}

function competitions_count(PDO $pdo): int
{
    return (int) $pdo->query('SELECT COUNT(*) FROM competitions')->fetchColumn();
}

function pending_competitions_count(PDO $pdo): int
{
    return (int) $pdo->query('SELECT COUNT(*) FROM competitions WHERE status = "pending"')->fetchColumn();
}

function conflict_flags_count(PDO $pdo): int
{
    return (int) $pdo->query('SELECT COUNT(*) FROM conflict_flags')->fetchColumn();
}

function competition_totals_by_category(PDO $pdo): array
{
    return $pdo->query('SELECT category, COUNT(*) AS total FROM competitions GROUP BY category')->fetchAll();
}

function competition_registration_status(array $competition): string
{
    if ($competition['status'] === 'cancelled') {
        return 'Cancelled';
    }

    $now = new DateTimeImmutable('now');
    if ($now < new DateTimeImmutable($competition['registration_start'])) {
        return 'Opening soon';
    }

    if ($now > new DateTimeImmutable($competition['registration_end'])) {
        return 'Closed';
    }

    return 'Open';
}

function competition_action_label(array $competition): string
{
    if ((int) $competition['team_size_max'] > 1) {
        return 'Join Team';
    }

    return 'Register Now';
}

function render_badge(string $label, string $color): string
{
    return '<span class="badge" style="background:' . e($color) . ';">' . e($label) . '</span>';
}

function create_team(PDO $pdo, int $competitionId, int $leaderId, string $teamName): array
{
    $inviteCode = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    $stmt = $pdo->prepare('INSERT INTO teams (competition_id, team_name, leader_id, invite_code) VALUES (?, ?, ?, ?)');
    $stmt->execute([$competitionId, $teamName, $leaderId, $inviteCode]);
    return ['id' => (int) $pdo->lastInsertId(), 'invite_code' => $inviteCode];
}

function find_team_by_invite_code(PDO $pdo, int $competitionId, string $inviteCode): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM teams WHERE competition_id = ? AND invite_code = ? LIMIT 1');
    $stmt->execute([$competitionId, strtoupper(trim($inviteCode))]);
    $team = $stmt->fetch();
    return $team ?: null;
}

function team_members_count(PDO $pdo, int $teamId): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM registrations WHERE team_id = ? AND status <> "cancelled"');
    $stmt->execute([$teamId]);
    return (int) $stmt->fetchColumn();
}

function user_has_competition_role(PDO $pdo, int $competitionId, int $userId): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM registrations WHERE competition_id = ? AND user_id = ? AND status <> "cancelled" LIMIT 1');
    $stmt->execute([$competitionId, $userId]);
    return (bool) $stmt->fetchColumn();
}

function register_user_for_competition(PDO $pdo, int $userId, int $competitionId, array $data = []): array
{
    $competition = get_competition_by_id($pdo, $competitionId);
    $user = get_user_by_id($pdo, $userId);

    if (!$competition || !$user) {
        return ['success' => false, 'message' => 'Competition or user not found.'];
    }

    if (!in_array($competition['status'], ['published', 'ongoing'], true)) {
        return ['success' => false, 'message' => 'This competition is not open for registration.'];
    }

    if (!competition_registration_open($competition)) {
        return ['success' => false, 'message' => 'Registration window is closed.'];
    }

    if (registration_exists($pdo, $competitionId, $userId)) {
        return ['success' => false, 'message' => 'You are already registered for this competition.'];
    }

    $existingCount = competition_member_count($pdo, $competitionId);
    $maxParticipants = $competition['max_participants'] !== null ? (int) $competition['max_participants'] : null;
    $allowWaitlist = !empty($data['waitlist']);
    if ($maxParticipants !== null && $existingCount >= $maxParticipants) {
        if (!$allowWaitlist) {
            return ['success' => false, 'message' => 'This competition is full. Join the waitlist to continue.'];
        }
        $registrationStatus = 'waitlisted';
    } else {
        $registrationStatus = 'registered';
    }

    $teamId = null;
    if ((int) $competition['team_size_max'] > 1) {
        $teamName = trim((string) ($data['team_name'] ?? ''));
        $inviteCode = trim((string) ($data['invite_code'] ?? ''));

        if ($teamName !== '') {
            $team = create_team($pdo, $competitionId, $userId, $teamName);
            $teamId = (int) $team['id'];
        } elseif ($inviteCode !== '') {
            $team = find_team_by_invite_code($pdo, $competitionId, $inviteCode);
            if (!$team) {
                return ['success' => false, 'message' => 'Invite code not found.'];
            }
            $teamCapacity = (int) $competition['team_size_max'];
            if (team_members_count($pdo, (int) $team['id']) >= $teamCapacity) {
                return ['success' => false, 'message' => 'That team is already full.'];
            }
            $teamId = (int) $team['id'];
        } else {
            return ['success' => false, 'message' => 'Please create a team or provide an invite code.'];
        }
    }

    $stmt = $pdo->prepare('INSERT INTO registrations (competition_id, user_id, team_id, status) VALUES (?, ?, ?, ?)');
    $stmt->execute([$competitionId, $userId, $teamId, $registrationStatus]);

    return [
        'success' => true,
        'message' => $registrationStatus === 'waitlisted' ? 'You were added to the waitlist.' : 'Registration successful.',
        'status' => $registrationStatus,
        'competition' => $competition,
        'user' => $user,
        'team_id' => $teamId,
    ];
}

function unique_competition_slug(PDO $pdo, string $title, ?int $excludeId = null): string
{
    $base = slugify($title);
    $slug = $base;
    $suffix = 2;

    while (true) {
        if ($excludeId !== null) {
            $stmt = $pdo->prepare('SELECT id FROM competitions WHERE slug = ? AND id <> ? LIMIT 1');
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $pdo->prepare('SELECT id FROM competitions WHERE slug = ? LIMIT 1');
            $stmt->execute([$slug]);
        }

        if (!$stmt->fetchColumn()) {
            return $slug;
        }

        $slug = $base . '-' . $suffix;
        $suffix++;
    }
}

function save_competition_from_input(PDO $pdo, int $organizerId, array $input, array $files = [], ?array $existingCompetition = null): array
{
    $title = trim((string) ($input['title'] ?? ''));
    $category = (string) ($input['category'] ?? 'Other');
    $description = trim((string) ($input['description'] ?? ''));
    $eligibility = trim((string) ($input['eligibility'] ?? ''));
    $prizePool = trim((string) ($input['prize_pool'] ?? ''));
    $registrationStart = trim((string) ($input['registration_start'] ?? ''));
    $registrationEnd = trim((string) ($input['registration_end'] ?? ''));
    $eventStart = trim((string) ($input['event_start'] ?? ''));
    $eventEnd = trim((string) ($input['event_end'] ?? ''));
    $venue = trim((string) ($input['venue'] ?? ''));
    $maxParticipants = trim((string) ($input['max_participants'] ?? ''));
    $teamSizeMin = max(1, (int) ($input['team_size_min'] ?? 1));
    $teamSizeMax = max($teamSizeMin, (int) ($input['team_size_max'] ?? 1));
    $bannerImage = $existingCompetition['banner_image'] ?? null;

    $allowedCategories = ['CTF', 'Hackathon', 'Robotics', 'Gaming', 'Coding', 'AI/ML', 'Other'];
    if ($title === '' || $description === '' || $registrationStart === '' || $registrationEnd === '' || $eventStart === '' || $eventEnd === '' || $venue === '') {
        return ['success' => false, 'message' => 'Please fill in all required fields.'];
    }

    if (!in_array($category, $allowedCategories, true)) {
        return ['success' => false, 'message' => 'Invalid competition category.'];
    }

    if (!normalize_registration_window($registrationStart, $registrationEnd) || !normalize_registration_window($eventStart, $eventEnd)) {
        return ['success' => false, 'message' => 'End dates must be after start dates.'];
    }

    try {
        $uploaded = $files['banner_image'] ?? null;
        if (is_array($uploaded) && !empty($uploaded['name'])) {
            $bannerImage = handle_banner_upload($uploaded);
        }
    } catch (Throwable $throwable) {
        return ['success' => false, 'message' => $throwable->getMessage()];
    }

    $maxParticipantsValue = $maxParticipants === '' ? null : max(1, (int) $maxParticipants);
    $slug = unique_competition_slug($pdo, $title, $existingCompetition['id'] ?? null);
    $status = $existingCompetition['status'] ?? 'pending';

    if ($existingCompetition) {
        $stmt = $pdo->prepare('UPDATE competitions SET title = ?, slug = ?, description = ?, category = ?, banner_image = ?, registration_start = ?, registration_end = ?, event_start = ?, event_end = ?, venue = ?, max_participants = ?, team_size_min = ?, team_size_max = ?, prize_pool = ?, eligibility = ?, status = ? WHERE id = ? AND organizer_id = ?');
        $stmt->execute([$title, $slug, $description, $category, $bannerImage, $registrationStart, $registrationEnd, $eventStart, $eventEnd, $venue, $maxParticipantsValue, $teamSizeMin, $teamSizeMax, $prizePool !== '' ? $prizePool : null, $eligibility !== '' ? $eligibility : null, $status, (int) $existingCompetition['id'], $organizerId]);
        $competitionId = (int) $existingCompetition['id'];
    } else {
        $stmt = $pdo->prepare('INSERT INTO competitions (organizer_id, title, slug, description, category, banner_image, registration_start, registration_end, event_start, event_end, venue, max_participants, team_size_min, team_size_max, prize_pool, eligibility, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "pending")');
        $stmt->execute([$organizerId, $title, $slug, $description, $category, $bannerImage, $registrationStart, $registrationEnd, $eventStart, $eventEnd, $venue, $maxParticipantsValue, $teamSizeMin, $teamSizeMax, $prizePool !== '' ? $prizePool : null, $eligibility !== '' ? $eligibility : null]);
        $competitionId = (int) $pdo->lastInsertId();
    }

    run_conflict_scan($pdo);

    return [
        'success' => true,
        'message' => $existingCompetition ? 'Competition updated successfully.' : 'Competition submitted for approval.',
        'competition_id' => $competitionId,
        'slug' => $slug,
    ];
}
