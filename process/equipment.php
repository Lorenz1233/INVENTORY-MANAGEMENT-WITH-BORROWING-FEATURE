<?php
require_once __DIR__ . '/_helpers.php';

require_post('../pages/equipment.php');
require_admin();

$itemId = (int) post_value('id');
$action = strtolower(post_value('action', 'save'));
$itemCode = post_value('item_code');
$itemName = compact_spaces(post_value('item_name'));
$condition = post_value('condition');
$postedStatus = strtolower(post_value('status'));
$status = $postedStatus === 'maintenance' ? 'inactive' : 'active';
$description = post_value('description');
$ownerOfficialId = post_value('owner_official_id');
$actorUserId = (int) ($_SESSION['user_id'] ?? 0);

try {
    if ($action === 'delete' && $itemId > 0) {
        $pdo->beginTransaction();

        $stmt = db_exec($pdo, 'SELECT item_id, item_name FROM items WHERE item_id = ? FOR UPDATE', [$itemId]);
        $item = $stmt->fetch();

        if (!$item) {
            throw new RuntimeException('Item not found.');
        }

        if (item_dependency_count($pdo, $itemId) > 0) {
            throw new RuntimeException('item_in_use');
        }

        db_exec($pdo, 'DELETE FROM items WHERE item_id = ?', [$itemId]);
        log_audit($pdo, 'item_delete', 'items', $itemId, ['item_name' => $item['item_name'], 'type' => 'equipment']);
        $pdo->commit();

        respond_success('../pages/equipment.php', 'deleted');
    }

    if ($itemName === '') {
        respond_error('../pages/equipment.php', 'missing', 'Item name is required.');
    }

    $pdo->beginTransaction();

    $categoryId = require_existing_category_id($pdo, post_value('category_id'), post_value('category'));
    $unitId = require_existing_unit_id($pdo, post_value('unit_id'), post_value('unit') ?: 'pcs');
    $allocations = owner_allocations_from_post($pdo, 'owner_official_id', 'quantity', $itemId);

    $notes = trim($description . "\nType: Equipment\nItem code: " . $itemCode . "\nCondition: " . $condition);

    foreach ($allocations as $allocation) {
        $allocationItemId = (int) $allocation['item_id'];
        if ($allocationItemId <= 0 && $itemId > 0 && count($allocations) === 1) {
            $allocationItemId = $itemId;
        }

        $ownerOfficialId = $allocation['owner_official_id'];
        $quantity = (int) $allocation['quantity'];

        if ($allocationItemId > 0) {
            assert_item_not_duplicate($pdo, $itemName, $categoryId, $allocationItemId, $ownerOfficialId);

            $stmt = db_exec(
                $pdo,
                'SELECT total_quantity, available_quantity, min_quantity_alert
                 FROM items
                 WHERE item_id = ?
                 FOR UPDATE',
                [$allocationItemId]
            );
            $currentItem = $stmt->fetch();

            if (!$currentItem) {
                throw new RuntimeException('Item not found.');
            }

            $borrowedQuantity = max(0, (int) $currentItem['total_quantity'] - (int) $currentItem['available_quantity']);
            if ($quantity < $borrowedQuantity) {
                throw new RuntimeException('quantity_below_borrowed');
            }

            $availableQuantity = $quantity - $borrowedQuantity;
            $stockStatus = inventory_stock_status($availableQuantity, (int) $currentItem['min_quantity_alert']);

            db_exec(
                $pdo,
                'UPDATE items
                 SET item_name = ?, description = ?, unit_id = ?, category_id = ?,
                     total_quantity = ?, available_quantity = ?, status = ?, stock_status = ?,
                     received_by_official_id = ?, updated_by_user_id = ?
                 WHERE item_id = ?',
                [$itemName, $notes, $unitId, $categoryId, $quantity, $availableQuantity, $status, $stockStatus, $ownerOfficialId, $actorUserId, $allocationItemId]
            );
            log_audit($pdo, 'item_update', 'items', $allocationItemId, ['item_name' => $itemName, 'type' => 'equipment', 'owner_official_id' => $ownerOfficialId]);
            continue;
        }

        assert_item_not_duplicate($pdo, $itemName, $categoryId, 0, $ownerOfficialId);
        $stockStatus = inventory_stock_status($quantity);

        db_exec(
            $pdo,
            'INSERT INTO items
                (item_name, description, unit_id, category_id, total_quantity, available_quantity, received_by_official_id, created_by_user_id, updated_by_user_id, date_added, status, stock_status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?, ?)',
            [$itemName, $notes, $unitId, $categoryId, $quantity, $quantity, $ownerOfficialId, $actorUserId, $actorUserId, $status, $stockStatus]
        );
        $newItemId = (int) $pdo->lastInsertId();
        log_audit($pdo, 'item_create', 'items', $newItemId, ['item_name' => $itemName, 'type' => 'equipment', 'owner_official_id' => $ownerOfficialId]);
    }

    $pdo->commit();
    respond_success('../pages/equipment.php', 'saved');
} catch (Throwable $error) {
    rollback_if_active($pdo);
    log_internal_error('equipment', $error);

    $code = $error->getMessage() === 'item_in_use'
        ? 'item_in_use'
        : ($error->getMessage() === 'quantity_below_borrowed' ? 'quantity_below_borrowed' : 'save_failed');

    respond_error('../pages/equipment.php', $code, 'The equipment item could not be saved.');
}
