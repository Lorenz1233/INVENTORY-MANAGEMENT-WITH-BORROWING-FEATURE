<?php
require_once __DIR__ . '/_helpers.php';

require_post('../pages/materials.php');
require_admin();

$action = strtolower(post_value('action', 'save'));
$unitId = (int) post_value('unit_id');
$unitName = substr(compact_spaces(post_value('unit_name')), 0, 20);

try {
    if ($action === 'delete' && $unitId > 0) {
        $pdo->beginTransaction();

        $stmt = db_exec($pdo, 'SELECT unit_id, unit_name FROM unit WHERE unit_id = ? FOR UPDATE', [$unitId]);
        $unit = $stmt->fetch();

        if (!$unit) {
            throw new RuntimeException('Unit not found.');
        }

        $itemCount = (int) db_exec($pdo, 'SELECT COUNT(*) FROM items WHERE unit_id = ?', [$unitId])->fetchColumn();
        if ($itemCount > 0) {
            throw new RuntimeException('unit_in_use');
        }

        db_exec($pdo, 'DELETE FROM unit WHERE unit_id = ?', [$unitId]);
        log_audit($pdo, 'unit_delete', 'unit', $unitId, ['unit_name' => $unit['unit_name']]);
        $pdo->commit();

        respond_success('../pages/admin-settings.php', 'unit_deleted');
    }

    if ($unitName === '') {
        respond_error('../pages/admin-settings.php', 'missing_unit', 'Unit name is required.');
    }

    $pdo->beginTransaction();

    if ($unitId > 0) {
        $stmt = db_exec($pdo, 'SELECT unit_id, unit_name FROM unit WHERE unit_id = ? FOR UPDATE', [$unitId]);
        $current = $stmt->fetch();

        if (!$current) {
            throw new RuntimeException('Unit not found.');
        }

        $stmt = db_exec(
            $pdo,
            'SELECT unit_id FROM unit WHERE LOWER(unit_name) = LOWER(?) AND unit_id <> ? LIMIT 1',
            [$unitName, $unitId]
        );
        $duplicate = $stmt->fetch();

        if ($duplicate) {
            $targetId = (int) $duplicate['unit_id'];
            db_exec($pdo, 'UPDATE items SET unit_id = ? WHERE unit_id = ?', [$targetId, $unitId]);
            db_exec($pdo, 'DELETE FROM unit WHERE unit_id = ?', [$unitId]);
            log_audit($pdo, 'unit_merge', 'unit', $targetId, ['from_unit_id' => $unitId, 'unit_name' => $unitName]);
        } else {
            db_exec($pdo, 'UPDATE unit SET unit_name = ? WHERE unit_id = ?', [$unitName, $unitId]);
            log_audit($pdo, 'unit_update', 'unit', $unitId, ['unit_name' => $unitName]);
        }
    } else {
        $unitId = get_or_create_unit($pdo, $unitName);
    }

    $pdo->commit();
    respond_success('../pages/admin-settings.php', 'unit_saved');
} catch (Throwable $error) {
    rollback_if_active($pdo);
    log_internal_error('units', $error);

    $code = $error->getMessage() === 'unit_in_use' ? 'unit_in_use' : 'unit_failed';
    respond_error('../pages/admin-settings.php', $code, 'The unit could not be saved.');
}
