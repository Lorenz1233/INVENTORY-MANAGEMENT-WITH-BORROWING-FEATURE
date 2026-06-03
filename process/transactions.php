<?php
require_once __DIR__ . '/_helpers.php';

require_post('../pages/transactions.php');
require_borrow_workflow_manager();

$transactionId = (int) post_value('transaction_id');
$action = strtolower(post_value('action'));
$officialId = current_official_id();

if ($transactionId <= 0 || $action !== 'return') {
    respond_error('../pages/transactions.php', 'missing', 'Transaction and action are required.');
}

try {
    $pdo->beginTransaction();

    $stmt = db_exec(
        $pdo,
        'SELECT t.*,
                i.total_quantity,
                i.available_quantity,
                i.min_quantity_alert,
                COALESCE(br.owner_official_id, i.received_by_official_id) AS effective_owner
         FROM transactions t
         JOIN borrow_request br ON br.request_id = t.request_id
         JOIN items i ON i.item_id = t.item_id
         WHERE t.transaction_id = ?
         FOR UPDATE',
        [$transactionId]
    );
    $transaction = $stmt->fetch();

    if (!$transaction || $transaction['status'] === 'RETURNED') {
        throw new RuntimeException('transaction_not_returnable');
    }

    if (!is_admin_user() && ($officialId === '' || $transaction['effective_owner'] !== $officialId)) {
        throw new RuntimeException('not_allowed');
    }

    $newAvailable = min(
        (int) $transaction['total_quantity'],
        (int) $transaction['available_quantity'] + (int) $transaction['quantity_borrowed']
    );
    $stockStatus = inventory_stock_status($newAvailable, (int) $transaction['min_quantity_alert']);

    db_exec($pdo, 'UPDATE transactions SET status = "RETURNED" WHERE transaction_id = ?', [$transactionId]);
    db_exec($pdo, 'UPDATE borrow_appointments SET status = "COMPLETED" WHERE request_id = ?', [$transaction['request_id']]);
    db_exec(
        $pdo,
        'UPDATE items
         SET available_quantity = ?, stock_status = ?
         WHERE item_id = ?',
        [$newAvailable, $stockStatus, $transaction['item_id']]
    );
    log_audit(
        $pdo,
        'transaction_return',
        'transactions',
        $transactionId,
        ['item_id' => $transaction['item_id'], 'quantity' => $transaction['quantity_borrowed']]
    );

    $pdo->commit();
    respond_success('../pages/transactions.php', 'returned');
} catch (Throwable $error) {
    rollback_if_active($pdo);
    log_internal_error('transactions', $error);

    $code = in_array($error->getMessage(), ['transaction_not_returnable', 'not_allowed'], true) ? $error->getMessage() : 'return_failed';
    respond_error('../pages/transactions.php', $code, 'The item could not be returned.');
}
