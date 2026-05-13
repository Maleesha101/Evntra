<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
require_login();
require_once __DIR__ . '/../includes/mailer.php';
$pdo = require __DIR__ . '/../config/db.php';

$acceptsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') || (($_SERVER['CONTENT_TYPE'] ?? '') !== '' && str_contains($_SERVER['CONTENT_TYPE'], 'application/json'));
$input = $acceptsJson ? (json_decode((string) file_get_contents('php://input'), true) ?: []) : $_POST;
$competitionId = (int) ($input['competition_id'] ?? 0);
$userId = (int) $_SESSION['user_id'];

if ($competitionId <= 0) {
    if ($acceptsJson) {
        http_response_code(422);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Competition id is required.']);
        exit;
    }
    flash('error', 'Competition id is required.');
    redirect('/student/browse.php');
}

$result = register_user_for_competition($pdo, $userId, $competitionId, $input);
$competition = $result['competition'] ?? get_competition_by_id($pdo, $competitionId);
$user = $result['user'] ?? get_user_by_id($pdo, $userId);

if ($result['success']) {
    create_notification($pdo, $userId, $result['message'] . ' for ' . $competition['title'], competition_url($competition));
    if ($competition) {
        create_notification($pdo, (int) $competition['organizer_id'], $user['full_name'] . ' registered for ' . $competition['title'], competition_url($competition));
    }
    send_registration_confirmation_mail($user, $competition, $result['status']);
}

if ($acceptsJson) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($result, JSON_UNESCAPED_SLASHES);
    exit;
}

if ($result['success']) {
    flash('success', $result['message']);
    $redirectTo = (string) ($input['redirect_to'] ?? '/student/my-registrations.php');
    redirect($redirectTo);
}

flash('error', $result['message']);
$redirectTo = (string) ($input['redirect_to'] ?? '/student/competition.php?id=' . $competitionId);
redirect($redirectTo);
