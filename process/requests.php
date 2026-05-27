<?php
require_once __DIR__ . '/_helpers.php';

require_post('../pages/requests.php');
require_borrow_workflow_manager();

$requestId = (int) post_value('request_id');
$action = strtolower(post_value('action'));
$remarks = post_value('remarks');

if ($requestId <= 0 || !in_array($action, ['approve', 'reject'], true)) {
    respond_error('../pages/requests.php', 'missing', 'Request and action are required.');
}

try {
    if ($action === 'reject') {
        $pdo->beginTransaction();

        $stmt = db_exec($pdo, 'SELECT request_id, status FROM borrow_request WHERE request_id = ? FOR UPDATE', [$requestId]);
        $request = $stmt->fetch();

        if (!$request || $request['status'] !== 'PENDING') {
            throw new RuntimeException('request_not_pending');
        }

        db_exec(
            $pdo,
            'UPDATE borrow_request SET status = "REJECTED", remarks = ? WHERE request_id = ?',
            [$remarks, $requestId]
        );
        log_audit($pdo, 'borrow_request_reject', 'borrow_request', $requestId, ['remarks' => $remarks]);
        $pdo->commit();

        respond_success('../pages/requests.php', 'rejected');
    }

    $pdo->beginTransaction();

    $stmt = db_exec(
        $pdo,
        'SELECT br.*, i.available_quantity, i.status AS item_status, i.min_quantity_alert
         FROM borrow_request br
         JOIN items i ON i.item_id = br.item_id
         WHERE br.request_id = ?
         FOR UPDATE',
        [$requestId]
    );
    $request = $stmt->fetch();

    if (!$request || $request['status'] !== 'PENDING') {
        throw new RuntimeException('request_not_pending');
    }

    if ($request['item_status'] !== 'active') {
        throw new RuntimeException('item_unavailable');
    }

    if ((int) $request['quantity_requested'] > (int) $request['available_quantity']) {
        throw new RuntimeException('insufficient_quantity');
    }

    $expectedReturn = date(
        'Y-m-d',
        strtotime($request['request_date'] . ' +' . (int) $request['days_to_borrow'] . ' days')
    );

    db_exec(
        $pdo,
        'UPDATE borrow_request SET status = "APPROVED", remarks = ? WHERE request_id = ?',
        [$remarks, $requestId]
    );

    $stmt = db_exec($pdo, 'SELECT transaction_id FROM transactions WHERE request_id = ? LIMIT 1 FOR UPDATE', [$requestId]);

    if (!$stmt->fetch()) {
        db_exec(
            $pdo,
            'INSERT INTO transactions
                (request_id, student_id, item_id, quantity_borrowed, borrow_date, expected_return_date, status)
             VALUES (?, ?, ?, ?, ?, ?, "ONGOING")',
            [
                $requestId,
                $request['student_id'],
                $request['item_id'],
                $request['quantity_requested'],
                $request['request_date'],
                $expectedReturn,
            ]
        );

        $remainingQuantity = (int) $request['available_quantity'] - (int) $request['quantity_requested'];
        $stockStatus = inventory_stock_status($remainingQuantity, (int) $request['min_quantity_alert']);

        db_exec(
            $pdo,
            'UPDATE items
             SET available_quantity = available_quantity - ?, stock_status = ?
             WHERE item_id = ? AND available_quantity >= ?',
            [
                $request['quantity_requested'],
                $stockStatus,
                $request['item_id'],
                $request['quantity_requested'],
            ]
        );
    }

    log_audit($pdo, 'borrow_request_approve', 'borrow_request', $requestId, ['remarks' => $remarks]);
    $pdo->commit();

    respond_success('../pages/requests.php', 'approved');
} catch (Throwable $error) {
    rollback_if_active($pdo);
    log_internal_error('requests', $error);

    $knownCodes = ['request_not_pending', 'item_unavailable', 'insufficient_quantity'];
    $code = in_array($error->getMessage(), $knownCodes, true) ? $error->getMessage() : 'approve_failed';

    respond_error('../pages/requests.php', $code, 'The borrow request could not be processed.');
}
