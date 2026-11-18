<?php
declare(strict_types=1);
require __DIR__ . '/api/bootstrap.php';
if (current_user()) { header('Location: dashboard.php'); exit; }
$error = ''; $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim((string)($_POST['email'] ?? ''))); $password = (string)($_POST['password'] ?? '');
    if (!verify_csrf()) $error = 'Your session expired. Please refresh and try again.';
    else {
        $users = json_read(USERS_FILE); $match = null;
        foreach ($users as $user) if (strtolower($user['email'] ?? '') === $email) { $match = $user; break; }
        if ($match && password_verify($password, $match['password'] ?? '')) {
            session_regenerate_id(true); $_SESSION['user'] = ['id'=>$match['id'], 'name'=>$match['name'], 'email'=>$match['email']]; $_SESSION['csrf'] = bin2hex(random_bytes(32)); header('Location: dashboard.php'); exit;
        }
        $error = 'The email or password is incorrect.';
    }
}
$csrf = csrf_token();
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#101b19"><title>Log in · FitTrack</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="assets/style.css"><link rel="stylesheet" href="assets/auth.css"></head><body class="auth-page"><main class="auth-card login-card"><a class="brand auth-brand" href="index.php"><span class="brand-mark">f</span><span>fittrack<span class="brand-dot">.</span></span></a><span class="eyebrow">WELCOME BACK</span><h1>Good to see <em>you.</em></h1><p class="auth-intro">Your progress is right where you left it.</p><?php if ($error): ?><div class="auth-error" role="alert"><?=h($error)?></div><?php endif; ?><form method="post" class="auth-form"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><label>Email address<input name="email" type="email" autocomplete="email" placeholder="you@example.com" value="<?=h($email)?>" required></label><label>Password<input name="password" type="password" autocomplete="current-password" placeholder="Your password" required></label><button class="primary-button auth-submit" type="submit">Log in <span>→</span></button></form><p class="auth-switch">New to FitTrack? <a href="register.php">Create an account</a></p><a class="back-home" href="index.php">← Back to FitTrack</a></main></body></html>
