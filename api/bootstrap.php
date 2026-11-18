<?php
declare(strict_types=1);

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax'
]);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

const FITTRACK_DATA = __DIR__ . '/../data';
const USERS_FILE = FITTRACK_DATA . '/users.json';

function ensure_data_dir(): void {
    if (!is_dir(FITTRACK_DATA)) mkdir(FITTRACK_DATA, 0750, true);
    if (!is_dir(FITTRACK_DATA . '/users')) mkdir(FITTRACK_DATA . '/users', 0750, true);
    if (!file_exists(USERS_FILE)) file_put_contents(USERS_FILE, "[]", LOCK_EX);
}
function json_read(string $path, array $fallback = []): array {
    if (!is_file($path)) return $fallback;
    $raw = file_get_contents($path);
    $data = json_decode($raw ?: '', true);
    return is_array($data) ? $data : $fallback;
}
function json_write(string $path, array $data): bool {
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    return $json !== false && file_put_contents($path, $json, LOCK_EX) !== false;
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verify_csrf(): bool {
    $token = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return is_string($token) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function require_auth(): array {
    $user = current_user();
    if (!$user) { http_response_code(401); header('Content-Type: application/json'); echo json_encode(['error' => 'Authentication required.']); exit; }
    return $user;
}
function user_data_file(string $userId): string { return FITTRACK_DATA . '/users/' . preg_replace('/[^a-f0-9]/', '', $userId) . '.json'; }
function h(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
ensure_data_dir();
