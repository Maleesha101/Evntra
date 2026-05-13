<?php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

app_start_session();

if (is_logged_in()) {
    $activeUser = current_user();
    if (!$activeUser || (int) $activeUser['is_active'] !== 1) {
        session_unset();
        session_destroy();
        redirect('/auth/login.php');
    }
}
