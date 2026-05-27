<?php
require_once __DIR__ . '/_helpers.php';

require_post('../pages/courses.php');
require_admin();

$action = strtolower(post_value('action', 'save'));
$courseCode = substr(strtoupper(post_value('course_code')), 0, 20);
$courseName = substr(compact_spaces(post_value('course_name')), 0, 20);

try {
    if ($action === 'delete' && $courseCode !== '') {
        $pdo->beginTransaction();

        $studentCount = (int) db_exec($pdo, 'SELECT COUNT(*) FROM master_list WHERE course_code = ?', [$courseCode])->fetchColumn();
        if ($studentCount > 0) {
            throw new RuntimeException('course_in_use');
        }

        db_exec($pdo, 'DELETE FROM course WHERE course_code = ?', [$courseCode]);
        log_audit($pdo, 'course_delete', 'course', $courseCode);
        $pdo->commit();

        respond_success('../pages/courses.php', 'deleted');
    }

    if ($courseCode === '' || $courseName === '') {
        respond_error('../pages/courses.php', 'missing', 'Course code and name are required.');
    }

    $pdo->beginTransaction();
    db_exec(
        $pdo,
        'INSERT INTO course (course_code, course_name)
         VALUES (?, ?)
         ON DUPLICATE KEY UPDATE course_name = VALUES(course_name)',
        [$courseCode, $courseName]
    );
    log_audit($pdo, 'course_save', 'course', $courseCode, ['course_name' => $courseName]);
    $pdo->commit();

    respond_success('../pages/courses.php', 'saved');
} catch (Throwable $error) {
    rollback_if_active($pdo);
    log_internal_error('courses', $error);

    $code = $error->getMessage() === 'course_in_use' ? 'course_in_use' : 'course_failed';
    respond_error('../pages/courses.php', $code, 'The course could not be saved.');
}
