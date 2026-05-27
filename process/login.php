<?php
require_once __DIR__ . '/_helpers.php';

require_post('../pages/login.php');

$username = post_value('username');
$password = post_value('password');

if ($username === '' || $password === '') {
    redirect_to('../pages/login.php', ['error' => 'missing']);
}

$stmt = db_exec($pdo, 'SELECT * FROM users WHERE username = ? AND is_active = 1 LIMIT 1', [$username]);
$user = $stmt->fetch();

if (!$user || !password_matches($password, $user['password'])) {
    redirect_to('../pages/login.php', ['error' => 'invalid']);
}

$_SESSION['user_id'] = (int) $user['user_id'];
$_SESSION['student_id'] = (int) ($user['student_id'] ?? 0);
$_SESSION['official_id'] = $user['official_id'] ?? null;
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];
$_SESSION['full_name'] = user_display_name_by_id($pdo, (int) $user['user_id']);
$_SESSION['force_password_change'] = (int) $user['is_default_password'] === 1;

db_exec($pdo, 'UPDATE users SET last_login = NOW() WHERE user_id = ?', [$user['user_id']]);
log_audit($pdo, 'user_login', 'users', $user['user_id']);

if (hash_equals((string) $user['password'], $password)) {
    db_exec(
        $pdo,
        'UPDATE users SET password = ? WHERE user_id = ?',
        [password_hash($password, PASSWORD_DEFAULT), $user['user_id']]
    );
}

if (!empty($_SESSION['force_password_change'])) {
    $passwordPage = $user['role'] === 'student'
        ? '../pages/student-change-password.php'
        : '../pages/change-password.php';
    redirect_to($passwordPage, ['first_login' => '1']);
}

redirect_to(home_for_role($user['role']));
