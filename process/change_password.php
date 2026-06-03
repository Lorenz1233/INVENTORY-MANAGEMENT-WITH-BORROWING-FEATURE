<?php
require_once __DIR__ . '/_helpers.php';

require_post('../pages/change-password.php');
require_login();

$currentPassword = post_value('current_password');
$newPassword = post_value('new_password');
$confirmPassword = post_value('confirm_password');
$fallback = ($_SESSION['role'] ?? '') === 'student'
    ? '../pages/student-change-password.php'
    : '../pages/change-password.php';

if ($newPassword === '' || $newPassword !== $confirmPassword) {
    redirect_back($fallback, ['error' => 'password_match']);
}

if (strlen($newPassword) < 6) {
    redirect_back($fallback, ['error' => 'weak_password']);
}

$stmt = db_exec($pdo, 'SELECT password FROM users WHERE user_id = ? LIMIT 1', [$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || !password_matches($currentPassword, $user['password'])) {
    redirect_back($fallback, ['error' => 'current_password']);
}

try {
    $pdo->beginTransaction();
    db_exec(
        $pdo,
        'UPDATE users SET password = ?, is_default_password = 0 WHERE user_id = ?',
        [password_hash($newPassword, PASSWORD_DEFAULT), $_SESSION['user_id']]
    );
    log_audit($pdo, 'password_change', 'users', $_SESSION['user_id']);
    $pdo->commit();
    unset($_SESSION['force_password_change']);
} catch (Throwable $error) {
    rollback_if_active($pdo);
    log_internal_error('change_password', $error);
    redirect_back($fallback, ['error' => 'password_update_failed']);
}

redirect_back($fallback, ['success' => 'password_updated']);
