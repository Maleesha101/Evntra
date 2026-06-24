<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
require_guest();
$pdo = require __DIR__ . '/../config/db.php';
$pageTitle = 'Login | Evntra';

$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if (login_rate_limited()) {
        flash('error', 'Too many login attempts. Please wait 10 minutes and try again.');
    } else {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $user = get_user_by_email($pdo, $email);

        if ($user && (int) $user['is_active'] === 1 && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            clear_login_attempts();
            flash('success', 'Welcome back, ' . $user['full_name'] . '.');
            redirect('/' . $user['role'] . '/dashboard.php');
        }

        record_login_attempt();
        flash('error', 'Invalid email or password.');
    }
}

include __DIR__ . '/../includes/header.php';
?>
<section class="auth-layout">
    <div class="hero-panel">
        <span class="badge" style="background:rgba(108,99,255,0.2);">Centralized competition hub</span>
        <h1>Sign in to manage every university competition in one place.</h1>
        <p>Track registrations, bookmarks, conflict warnings, and organizer analytics from a single account.</p>
    </div>
    <div class="form-card">
        <h2>Welcome back</h2>
        <p class="auth-subtitle">Enter your credentials to access your dashboard.</p>
        <form method="post" class="multi-step">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" required autocomplete="email" placeholder="Ex: you@university.edu" value="<?= htmlspecialchars($email) ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-input-wrapper">
                    <input type="password" id="password" name="password" required autocomplete="current-password" minlength="8" placeholder="••••••••">
                    <button type="button" class="password-toggle" data-toggle-password aria-label="Toggle password visibility">
                        <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="eye-off-icon hidden" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="form-actions" style="flex-direction:column; gap:0.75rem; margin-top:0.5rem;">
                <button class="btn btn-primary" type="submit">Sign in</button>
                <a class="btn btn-outline" href="/auth/forgot-password.php" style="width:100%; text-align:center;">Forgot password?</a>
            </div>
        </form>
        <p class="small-text">No account yet? <a href="/auth/register.php">Create one here</a>.</p>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
