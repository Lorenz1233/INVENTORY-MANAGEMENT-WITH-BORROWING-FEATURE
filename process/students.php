<?php
require_once __DIR__ . '/_helpers.php';

require_post('../pages/students.php');
require_admin();

$action = strtolower(post_value('action', 'save'));
$studentId = (int) post_value('student_id');
$fullName = post_value('full_name');
$courseCode = post_value('course');
$yearLevel = normalized_year(post_value('year_level'));

try {
    if ($action === 'delete' && $studentId > 0) {
        $dependents = (int) db_exec(
            $pdo,
            'SELECT
                (SELECT COUNT(*) FROM borrow_request WHERE student_id = ?) +
                (SELECT COUNT(*) FROM transactions WHERE student_id = ?)',
            [$studentId, $studentId]
        )->fetchColumn();

        if ($dependents > 0) {
            respond_error('../pages/students.php', 'record_in_use', 'This student has borrowing history and cannot be deleted.');
        }

        $pdo->beginTransaction();
        db_exec($pdo, 'DELETE FROM users WHERE student_id = ?', [$studentId]);
        db_exec($pdo, 'DELETE FROM master_list WHERE student_id = ?', [$studentId]);
        log_audit($pdo, 'masterlist_delete', 'master_list', $studentId, ['user_type' => 'student']);
        $pdo->commit();

        respond_success('../pages/students.php', 'deleted');
    }

    if ($studentId <= 0 || $fullName === '') {
        respond_error('../pages/students.php', 'missing', 'Student ID and full name are required.');
    }

    [$firstName, $lastName] = split_full_name($fullName);
    $courseCode = get_or_create_course($pdo, $courseCode);

    $pdo->beginTransaction();
    $userId = ensure_user_for_student($pdo, $studentId, $firstName, $lastName, $courseCode, $yearLevel, null, 'student', 1);
    log_audit($pdo, 'masterlist_save', 'master_list', $studentId, ['user_type' => 'student', 'user_id' => $userId]);
    $pdo->commit();

    respond_success('../pages/students.php', 'saved');
} catch (Throwable $error) {
    rollback_if_active($pdo);
    log_internal_error('students', $error);
    respond_error('../pages/students.php', 'student_save_failed', 'The student record could not be saved.');
}
