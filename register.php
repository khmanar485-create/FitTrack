<?php
declare(strict_types=1);
require __DIR__ . '/api/bootstrap.php';
if (current_user()) { header('Location: dashboard.php'); exit; }
$error = '';
$name = ''; $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');
    if (!verify_csrf()) $error = 'Your session expired. Please refresh and try again.';
    elseif ($name === '' || strlen($name) > 80) $error = 'Please enter your name (up to 80 characters).';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) $error = 'Please enter a valid email address.';
    elseif (strlen($password) < 8 || strlen($password) > 128) $error = 'Your password must contain between 8 and 128 characters.';
    elseif ($password !== $confirm) $error = 'The passwords do not match.';
    else {
        $users = json_read(USERS_FILE);
        foreach ($users as $existing) if (strtolower($existing['email'] ?? '') === $email) { $error = 'An account with this email already exists. Please log in.'; break; }
        if (!$error) {
            $id = bin2hex(random_bytes(16));
            $users[] = ['id'=>$id, 'name'=>$name, 'email'=>$email, 'password'=>password_hash($password, PASSWORD_DEFAULT), 'weekly_target'=>4, 'created_at'=>date(DATE_ATOM)];
            if (!json_write(USERS_FILE, $users)) $error = 'We could not create your account. Please try again.';
            else {
                json_write(user_data_file($id), ['workouts'=>[]]);
                session_regenerate_id(true);
                $_SESSION['user'] = ['id'=>$id, 'name'=>$name, 'email'=>$email];
                $_SESSION['csrf'] = bin2hex(random_bytes(32));
                header('Location: dashboard.php'); exit;
            }
        }
    }
}
$csrf = csrf_token();
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#101b19"><title>Create your account · FitTrack</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="assets/style.css"><link rel="stylesheet" href="assets/auth.css"></head><body class="auth-page"><main class="auth-card"><a class="brand auth-brand" href="index.php"><span class="brand-mark">f</span><span>fittrack<span class="brand-dot">.</span></span></a><span class="eyebrow">YOUR NEXT CHAPTER STARTS HERE</span><h1>Create your <em>account.</em></h1><p class="auth-intro">Build better habits, one workout at a time.</p><?php if ($error): ?><div class="auth-error" role="alert"><?=h($error)?></div><?php endif; ?><form method="post" class="auth-form"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><label>Full name<input name="name" type="text" autocomplete="name" maxlength="80" placeholder="Your name" value="<?=h($name)?>" required></label><label>Email address<input name="email" type="email" autocomplete="email" placeholder="you@example.com" value="<?=h($email)?>" required></label><label>Password<input name="password" type="password" autocomplete="new-password" minlength="8" placeholder="At least 8 characters" required></label><label>Confirm password<input name="confirm_password" type="password" autocomplete="new-password" minlength="8" placeholder="Enter your password again" required></label><button class="primary-button auth-submit" type="submit">Create account <span>→</span></button></form><p class="auth-switch">Already have an account? <a href="login.php">Log in</a></p><a class="back-home" href="index.php">← Back to FitTrack</a></main></body></html>
