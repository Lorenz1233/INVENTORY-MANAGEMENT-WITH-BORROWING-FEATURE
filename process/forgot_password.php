<?php
require_once __DIR__ . '/_helpers.php';

require_post('../pages/forgot-password.php');

$username = post_value('username');
$lastName = compact_spaces(post_value('last_name'));
$newPassword = post_value('new_password');
$confirmPassword = post_value('confirm_password');

if ($username === '' || $lastName === '' || $newPassword === '' || $confirmPassword === '') {
    redirect_to('../pages/forgot-password.php', ['error' => 'missing']);
}

if ($newPassword !== $confirmPassword) {
    redirect_to('../pages/forgot-password.php', ['error' => 'password_match']);
}

if (strlen($newPassword) < 6) {
    redirect_to('../pages/forgot-password.php', ['error' => 'weak_password']);
}

try {
    $pdo->beginTransaction();

    $stmt = db_exec(
        $pdo,
        'SELECT u.user_id,
                COALESCE(m.last_name, o.last_name) AS registered_last_name
         FROM users u
         LEFT JOIN master_list m ON m.student_id = u.student_id
         LEFT JOIN officials_masterlist o ON o.official_id = u.official_id
         WHERE u.username = ?
            AND u.is_active = 1
            AND COALESCE(u.approval_status, "approved") = "approved"
         LIMIT 1
         FOR UPDATE',
        [$username]
    );
    $user = $stmt->fetch();

    if (!$user || strtolower(compact_spaces($user['registered_last_name'] ?? '')) !== strtolower($lastName)) {
        throw new RuntimeException('invalid_identity');
    }

    db_exec(
        $pdo,
        'UPDATE users SET password = ?, is_default_password = 0 WHERE user_id = ?',
        [password_hash($newPassword, PASSWORD_DEFAULT), $user['user_id']]
    );
    log_audit($pdo, 'password_reset', 'users', $user['user_id']);

    $pdo->commit();
    redirect_to('../pages/login.php', ['reset' => 'success']);
} catch (Throwable $error) {
    rollback_if_active($pdo);
    log_internal_error('forgot_password', $error);

    $code = $error->getMessage() === 'invalid_identity' ? 'invalid_identity' : 'reset_failed';
    redirect_to('../pages/forgot-password.php', ['error' => $code]);
}
