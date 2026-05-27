<?php
require_once __DIR__ . '/_helpers.php';

require_post('../pages/users.php');
require_user_role_manager();

function update_user_role(PDO $pdo, $userId, $username, $newRole)
{
    $role = strtolower(clean($newRole)) === 'revoke' ? 'student' : db_role($newRole);

    if ($userId > 0) {
        $stmt = db_exec($pdo, 'SELECT user_id, role FROM users WHERE user_id = ? FOR UPDATE', [$userId]);
    } else {
        $stmt = db_exec($pdo, 'SELECT user_id, role FROM users WHERE username = ? FOR UPDATE', [$username]);
    }

    $user = $stmt->fetch();
    if (!$user) {
        throw new RuntimeException('User not found.');
    }

    db_exec($pdo, 'UPDATE users SET role = ?, is_active = 1 WHERE user_id = ?', [$role, $user['user_id']]);
    log_audit(
        $pdo,
        'user_role_update',
        'users',
        $user['user_id'],
        ['old_role' => $user['role'], 'new_role' => $role]
    );
}

$action = strtolower(post_value('action', 'save'));
$userId = (int) post_value('user_id');
$newRole = post_value('new_role');
$editUsername = post_value('username');

try {
    if ($action === 'delete' && $userId > 0) {
        if ($userId === (int) ($_SESSION['user_id'] ?? 0)) {
            respond_error('../pages/users.php', 'self_delete', 'You cannot delete your own account.');
        }

        $pdo->beginTransaction();
        $stmt = db_exec($pdo, 'SELECT user_id, username FROM users WHERE user_id = ? FOR UPDATE', [$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new RuntimeException('User not found.');
        }

        db_exec($pdo, 'DELETE FROM users WHERE user_id = ?', [$userId]);
        log_audit($pdo, 'user_delete', 'users', $userId, ['username' => $user['username']]);
        $pdo->commit();
        respond_success('../pages/users.php', 'deleted');
    }

    if ($action === 'deactivate' && $userId > 0) {
        if (!can_manage_user_roles($_SESSION['role'] ?? '')) {
            respond_error('../pages/users.php', 'not_allowed', 'Only administrators can deactivate user accounts.');
        }
        if ($userId === (int) ($_SESSION['user_id'] ?? 0)) {
            respond_error('../pages/users.php', 'self_deactivate', 'You cannot deactivate your own account.');
        }

        $pdo->beginTransaction();
        db_exec($pdo, 'UPDATE users SET is_active = 0 WHERE user_id = ?', [$userId]);
        log_audit($pdo, 'user_deactivate', 'users', $userId);
        $pdo->commit();
        respond_success('../pages/users.php', 'deactivated');
    }

    if ($action === 'reactivate' && $userId > 0) {
        if (!can_manage_user_roles($_SESSION['role'] ?? '')) {
            respond_error('../pages/users.php', 'not_allowed', 'Only administrators can reactivate user accounts.');
        }

        $pdo->beginTransaction();
        $stmt = db_exec($pdo, 'SELECT user_id, username FROM users WHERE user_id = ? FOR UPDATE', [$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new RuntimeException('User not found.');
        }

        db_exec($pdo, 'UPDATE users SET is_active = 1 WHERE user_id = ?', [$userId]);
        log_audit($pdo, 'user_reactivate', 'users', $userId, ['username' => $user['username']]);
        $pdo->commit();
        respond_success('../pages/users.php', 'reactivated');
    }

    if ($action === 'role_update' || ($newRole !== '' && ($userId > 0 || $editUsername !== ''))) {
        if (!can_manage_user_roles($_SESSION['role'] ?? '')) {
            respond_error('../pages/users.php', 'not_allowed', 'Only administrators can change user roles.');
        }

        if ($newRole === '' || ($userId <= 0 && $editUsername === '')) {
            respond_error('../pages/users.php', 'role_missing', 'User and role are required.');
        }

        $pdo->beginTransaction();
        update_user_role($pdo, $userId, $editUsername, $newRole);
        $pdo->commit();
        respond_success('../pages/users.php', 'role_updated');
    }

    if ($action !== 'save' && $action !== 'create') {
        respond_error('../pages/users.php', 'invalid_action', 'Invalid user action.');
    }

    $username = post_value('username');
    $fullName = post_value('full_name');
    $password = post_value('password');
    $role = db_role(post_value('role', 'student'));
    $isActive = strtolower(post_value('status', 'Active')) === 'active' ? 1 : 0;

    if ($role !== 'student' && !can_manage_user_roles($_SESSION['role'] ?? '')) {
        respond_error('../pages/users.php', 'not_allowed', 'Only administrators can create staff or administrator accounts.');
    }

    if ($username === '' || $fullName === '') {
        respond_error('../pages/users.php', 'missing', 'Username and full name are required.');
    }

    [$firstName, $lastName] = split_full_name($fullName);

    $pdo->beginTransaction();
    create_manual_user($pdo, $username, $firstName, $lastName, $role, $password, $isActive);
    $pdo->commit();

    respond_success('../pages/users.php', 'created');
} catch (Throwable $error) {
    rollback_if_active($pdo);
    log_internal_error('users', $error);
    respond_error('../pages/users.php', 'save_failed', 'The user could not be saved.');
}
