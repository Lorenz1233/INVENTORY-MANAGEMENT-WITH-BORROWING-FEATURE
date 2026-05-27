<?php
require_once __DIR__ . '/_helpers.php';

require_post('../pages/admin-settings.php');
require_admin();

$action = strtolower(post_value('action', 'save'));
$positionCode = substr(strtoupper(post_value('position_code')), 0, 20);
$positionName = substr(compact_spaces(post_value('position_name')), 0, 250);

try {
    if ($action === 'delete' && $positionCode !== '') {
        $pdo->beginTransaction();

        $stmt = db_exec($pdo, 'SELECT position_code, position_name FROM positions WHERE position_code = ? FOR UPDATE', [$positionCode]);
        $position = $stmt->fetch();

        if (!$position) {
            throw new RuntimeException('Position not found.');
        }

        $officialCount = (int) db_exec($pdo, 'SELECT COUNT(*) FROM officials_masterlist WHERE position_code = ?', [$positionCode])->fetchColumn();
        if ($officialCount > 0) {
            throw new RuntimeException('position_in_use');
        }

        db_exec($pdo, 'DELETE FROM positions WHERE position_code = ?', [$positionCode]);
        log_audit($pdo, 'position_delete', 'positions', $positionCode, ['position_name' => $position['position_name']]);
        $pdo->commit();

        respond_success('../pages/admin-settings.php', 'position_deleted');
    }

    if ($positionCode === '' || $positionName === '') {
        respond_error('../pages/admin-settings.php', 'missing_position', 'Position code and name are required.');
    }

    db_exec(
        $pdo,
        'INSERT INTO positions (position_code, position_name)
         VALUES (?, ?)
         ON DUPLICATE KEY UPDATE position_name = VALUES(position_name)',
        [$positionCode, $positionName]
    );
    log_audit($pdo, 'position_save', 'positions', $positionCode, ['position_name' => $positionName]);

    respond_success('../pages/admin-settings.php', 'position_saved');
} catch (Throwable $error) {
    rollback_if_active($pdo);
    log_internal_error('positions', $error);

    $code = $error->getMessage() === 'position_in_use' ? 'position_in_use' : 'position_failed';
    respond_error('../pages/admin-settings.php', $code, 'The position could not be saved.');
}
