<?php
require_once __DIR__ . '/_helpers.php';

require_post('../pages/students.php');
require_admin();

$action = strtolower(post_value('action', 'save'));
$userType = strtolower(post_value('user_type', 'student')) === 'faculty' ? 'faculty' : 'student';
$idNumber = post_value('id_number') ?: post_value('student_id') ?: post_value('official_id');
$firstName = post_value('first_name');
$lastName = post_value('last_name');
$fullName = post_value('full_name');
$departmentOrCourse = post_value('department') ?: post_value('course');
$courseCodeInput = post_value('course_code') ?: post_value('course');
$positionCodeInput = post_value('position_code') ?: post_value('department');
$officialRole = db_role(post_value('official_role', 'faculty'));
if ($officialRole === 'student') {
    $officialRole = 'faculty';
}
$yearLevel = normalized_year(post_value('year_level'));

if ($fullName !== '' && ($firstName === '' || $lastName === '')) {
    [$firstName, $lastName] = split_full_name($fullName);
}

if ($idNumber === '') {
    respond_error('../pages/students.php', 'missing_id', 'ID number is required.');
}

if ($userType === 'faculty' && !can_manage_user_roles($_SESSION['role'] ?? '')) {
    respond_error('../pages/students.php', 'not_allowed', 'Only administrators can create or update faculty accounts.');
}

try {
    if ($action === 'delete') {
        $pdo->beginTransaction();

        if ($userType === 'faculty') {
            $dependents = (int) db_exec(
                $pdo,
                'SELECT COUNT(*) FROM items WHERE received_by_official_id = ?',
                [$idNumber]
            )->fetchColumn();

            if ($dependents > 0) {
                throw new RuntimeException('record_in_use');
            }

            db_exec($pdo, 'DELETE FROM users WHERE official_id = ? OR username = ?', [$idNumber, $idNumber]);
            db_exec($pdo, 'DELETE FROM officials_masterlist WHERE official_id = ?', [$idNumber]);
            log_audit($pdo, 'masterlist_delete', 'officials_masterlist', $idNumber, ['user_type' => 'faculty']);
        } else {
            $studentId = require_positive_int($idNumber, 'Student ID');
            $dependents = (int) db_exec(
                $pdo,
                'SELECT
                    (SELECT COUNT(*) FROM borrow_request WHERE student_id = ?) +
                    (SELECT COUNT(*) FROM transactions WHERE student_id = ?)',
                [$studentId, $studentId]
            )->fetchColumn();

            if ($dependents > 0) {
                throw new RuntimeException('record_in_use');
            }

            db_exec($pdo, 'DELETE FROM users WHERE student_id = ?', [$studentId]);
            db_exec($pdo, 'DELETE FROM master_list WHERE student_id = ?', [$studentId]);
            log_audit($pdo, 'masterlist_delete', 'master_list', $studentId, ['user_type' => 'student']);
        }

        $pdo->commit();
        respond_success('../pages/students.php', 'masterlist_deleted');
    }

    if ($firstName === '' || $lastName === '') {
        respond_error('../pages/students.php', 'missing_name', 'First name and last name are required.');
    }

    $pdo->beginTransaction();

    if ($userType === 'faculty') {
        $positionCode = require_existing_position_code($pdo, $positionCodeInput, $departmentOrCourse);
        $userId = ensure_user_for_official($pdo, $idNumber, $firstName, $lastName, $positionCode, null, $officialRole, 1);
        log_audit(
            $pdo,
            'masterlist_save',
            'officials_masterlist',
            $idNumber,
            ['user_type' => 'faculty', 'role' => $officialRole, 'user_id' => $userId]
        );
        $pdo->commit();
        respond_success('../pages/students.php', 'faculty_saved');
    }

    $studentId = require_positive_int($idNumber, 'Student ID');
    $courseCode = require_existing_course_code($pdo, $courseCodeInput, $departmentOrCourse);
    $userId = ensure_user_for_student($pdo, $studentId, $firstName, $lastName, $courseCode, $yearLevel, null, 'student', 1);
    log_audit(
        $pdo,
        'masterlist_save',
        'master_list',
        $studentId,
        ['user_type' => 'student', 'user_id' => $userId]
    );

    $pdo->commit();
    respond_success('../pages/students.php', 'student_saved');
} catch (Throwable $error) {
    rollback_if_active($pdo);
    log_internal_error('masterlist', $error);

    $code = $error->getMessage() === 'record_in_use' ? 'record_in_use' : 'masterlist_failed';
    respond_error('../pages/students.php', $code, 'The masterlist record could not be saved.');
}
