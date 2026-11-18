<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
$user = require_auth();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
$file = user_data_file($user['id']);
$users = json_read(USERS_FILE);
$userIndex = null;
foreach ($users as $i => $record) if (($record['id'] ?? '') === $user['id']) { $userIndex = $i; break; }
if ($userIndex === null) { http_response_code(401); echo json_encode(['error'=>'Account not found.']); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = json_read($file, ['workouts'=>[]]);
    echo json_encode(['workouts'=>array_values($data['workouts'] ?? []), 'weeklyTarget'=>(int)($users[$userIndex]['weekly_target'] ?? 4), 'csrf'=>csrf_token()]); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error'=>'Method not allowed.']); exit; }
if (!verify_csrf()) { http_response_code(403); echo json_encode(['error'=>'Security token expired. Refresh the page and try again.']); exit; }
$payload = json_decode(file_get_contents('php://input') ?: '', true);
if (!is_array($payload) || !isset($payload['workouts']) || !is_array($payload['workouts']) || count($payload['workouts']) > 3000) { http_response_code(422); echo json_encode(['error'=>'Invalid workout data.']); exit; }
$clean = [];
foreach ($payload['workouts'] as $w) {
    if (!is_array($w)) continue;
    $id = substr((string)($w['id'] ?? ''), 0, 80); $name = trim((string)($w['name'] ?? '')); $date = (string)($w['date'] ?? '');
    $duration = filter_var($w['duration'] ?? null, FILTER_VALIDATE_INT); $calories = filter_var($w['calories'] ?? null, FILTER_VALIDATE_INT); $pressure = filter_var($w['pressure'] ?? 6, FILTER_VALIDATE_INT);
    if ($id === '' || $name === '' || strlen($name) > 120 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $duration === false || $duration < 1 || $duration > 600 || $calories === false || $calories < 0 || $calories > 10000 || $pressure === false || $pressure < 1 || $pressure > 10) continue;
    $clean[] = ['id'=>$id,'name'=>$name,'type'=>substr((string)($w['type'] ?? 'Other'),0,30),'date'=>$date,'duration'=>$duration,'calories'=>$calories,'intensity'=>substr((string)($w['intensity'] ?? 'Moderate'),0,20),'pressure'=>$pressure,'notes'=>substr(trim((string)($w['notes'] ?? '')),0,1000)];
}
$target = filter_var($payload['weeklyTarget'] ?? ($users[$userIndex]['weekly_target'] ?? 4), FILTER_VALIDATE_INT);
if ($target === false || $target < 1 || $target > 14) $target = 4;
if (!json_write($file, ['workouts'=>$clean])) { http_response_code(500); echo json_encode(['error'=>'Could not save your workouts.']); exit; }
$users[$userIndex]['weekly_target'] = $target;
if (!json_write(USERS_FILE, $users)) { http_response_code(500); echo json_encode(['error'=>'Could not save your weekly goal.']); exit; }
echo json_encode(['ok'=>true, 'workouts'=>$clean, 'weeklyTarget'=>$target]);
