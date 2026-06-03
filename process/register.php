<?php
require_once __DIR__ . '/_helpers.php';

require_post('../pages/register.php');

$idNumber = post_value('id_number');
$fullName = post_value('full_name');
$role = post_value('role', 'student');
$password = post_value('password');
$confirmPassword = post_value('confirm_password');

if ($idNumber === '' || $fullName === '' || $password === '' || $confirmPassword === '') {
    redirect_to('../pages/register.php', ['error' => 'missing']);
}

if ($password !== $confirmPassword) {
    redirect_to('../pages/register.php', ['error' => 'password_mismatch']);
}

try {
    [$firstName, $lastName] = split_full_name($fullName);

    $pdo->beginTransaction();
    $userId = create_pending_user_registration($pdo, $idNumber, $firstName, $lastName, $role, $password);
    $stmt = db_exec($pdo, 'SELECT approval_status FROM users WHERE user_id = ? LIMIT 1', [$userId]);
    $approvalStatus = (string) $stmt->fetchColumn();
    $pdo->commit();

    redirect_to('../pages/login.php', ['registered' => $approvalStatus === 'approved' ? 'approved' : 'pending']);
} catch (Throwable $error) {
    rollback_if_active($pdo);
    log_internal_error('register', $error);

    $knownCodes = ['duplicate', 'weak_password', 'missing'];
    $code = in_array($error->getMessage(), $knownCodes, true) ? $error->getMessage() : 'failed';
    redirect_to('../pages/register.php', ['error' => $code]);
}
