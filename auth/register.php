<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
require_guest();
$pdo = require __DIR__ . '/../config/db.php';
$pageTitle = 'Register | Evntra';

$fullName = '';
$email = '';
$university = '';
$role = 'student';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
    $university = trim((string) ($_POST['university'] ?? ''));
    $role = $_POST['role'] ?? 'student';

    if ($fullName === '' || $email === '' || $password === '' || $university === '') {
        flash('error', 'All fields are required.');
    } elseif (!in_array($role, ['student', 'organizer'], true)) {
        flash('error', 'Please select a valid role.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Please enter a valid email address.');
    } elseif (!password_is_strong($password)) {
        flash('error', 'Passwords must be at least 8 characters with upper, lower, number, and symbol.');
    } elseif ($password !== $confirmPassword) {
        flash('error', 'Passwords do not match.');
    } elseif (get_user_by_email($pdo, $email)) {
        flash('error', 'That email address is already registered.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, role, university, email_verified) VALUES (?, ?, ?, ?, ?, 0)');
        $stmt->execute([$fullName, $email, password_hash($password, PASSWORD_BCRYPT), $role, $university]);
        $userId = (int) $pdo->lastInsertId();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['role'] = $role;
        $_SESSION['full_name'] = $fullName;
        flash('success', 'Account created successfully.');
        redirect('/' . $role . '/dashboard.php');
    }
}

include __DIR__ . '/../includes/header.php';
?>
<section class="auth-layout">
    <div class="hero-panel">

        <span class="badge">Join the network</span>
        <h1>Create your <span class="gradient-text">Evntra</span> account</h1>
        <p>Students can browse and register for competitions. Organizers can create events and monitor participant activity in real-time.</p>

        <div class="auth-features">
            <div class="auth-feature">
                <span class="auth-feature-icon material-symbols-outlined">explore</span>
                <span class="auth-feature-label"><strong>Discover Events</strong> - browse hackathons, CTFs & more</span>
            </div>
            <div class="auth-feature">
                <span class="auth-feature-icon material-symbols-outlined">group_add</span>
                <span class="auth-feature-label"><strong>Build Teams</strong> - collaborate with university peers</span>
            </div>
            <div class="auth-feature">
                <span class="auth-feature-icon material-symbols-outlined">leaderboard</span>
                <span class="auth-feature-label"><strong>Track Progress</strong> - analytics & performance stats</span>
            </div>
        </div>
    </div>
    <div class="form-card">
        <h2>Get started</h2>
        <p class="auth-subtitle">Fill in your details to create a free account.</p>
        <form method="post" class="multi-step">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="full_name">Full name</label>
                <input type="text" id="full_name" name="full_name" required placeholder="Ex: John Doe" value="<?= htmlspecialchars($fullName) ?>">
            </div>
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" required placeholder="Ex: you@university.edu" value="<?= htmlspecialchars($email) ?>">
            </div>
            <div class="form-group">
                <label for="university">University</label>
                <input type="text" id="university" name="university" required placeholder="Ex: University of Colombo" value="<?= htmlspecialchars($university) ?>">
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Student</option>
                    <option value="organizer" <?= $role === 'organizer' ? 'selected' : '' ?>>Organizer</option>
                </select>
            </div>
            <div class="grid grid-2">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" minlength="8" required placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm password</label>
                    <input type="password" id="confirm_password" name="confirm_password" minlength="8" required placeholder="••••••••">
                </div>
            </div>
            <div class="form-actions" style="flex-direction:column; gap:0.75rem; margin-top:0.5rem;">
                <button class="btn btn-primary" type="submit">Create account</button>
            </div>
        </form>
        <div class="auth-divider">or</div>
        <p class="small-text">Already have an account? <a href="/auth/login.php">Sign in</a></p>
        <div class="auth-trust">
            <span class="auth-trust-item"><span class="material-symbols-outlined">lock</span> Encrypted</span>
            <span class="auth-trust-item"><span class="material-symbols-outlined">verified</span> Verified</span>
            <span class="auth-trust-item"><span class="material-symbols-outlined">speed</span> Instant</span>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
