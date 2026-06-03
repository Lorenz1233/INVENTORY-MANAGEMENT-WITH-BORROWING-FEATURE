<?php
require_once __DIR__ . '/_helpers.php';

require_post('../pages/requests.php');
require_borrow_workflow_manager();

$requestId = (int) post_value('request_id');
$action = strtolower(post_value('action'));
$remarks = post_value('remarks');
$officialId = current_official_id();

if ($requestId <= 0 || !in_array($action, ['approve', 'reject'], true)) {
    respond_error('../pages/requests.php', 'missing', 'Request and action are required.');
}

try {
    if ($action === 'reject') {
        $pdo->beginTransaction();

        $stmt = db_exec(
            $pdo,
            'SELECT br.request_id,
                    br.status,
                    COALESCE(br.owner_official_id, i.received_by_official_id) AS effective_owner
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

        if (!is_admin_user() && ($officialId === '' || $request['effective_owner'] !== $officialId)) {
            throw new RuntimeException('not_allowed');
        }

        db_exec(
            $pdo,
            'UPDATE borrow_request SET status = "REJECTED", process_remarks = ? WHERE request_id = ?',
            [$remarks, $requestId]
        );
        db_exec($pdo, 'UPDATE borrow_appointments SET status = "CANCELLED", notes = ? WHERE request_id = ?', [$remarks, $requestId]);
        log_audit($pdo, 'borrow_request_reject', 'borrow_request', $requestId, ['remarks' => $remarks]);
        $pdo->commit();

        respond_success('../pages/requests.php', 'rejected');
    }

    $pdo->beginTransaction();

    $stmt = db_exec(
        $pdo,
        'SELECT br.*,
                i.available_quantity,
                i.status AS item_status,
                i.min_quantity_alert,
                COALESCE(br.owner_official_id, i.received_by_official_id) AS effective_owner
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

    if (!is_admin_user() && ($officialId === '' || $request['effective_owner'] !== $officialId)) {
        throw new RuntimeException('not_allowed');
    }

    if (clean($request['effective_owner'] ?? '') === '') {
        throw new RuntimeException('owner_required');
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
        'UPDATE borrow_request SET status = "APPROVED", process_remarks = ?, owner_official_id = ? WHERE request_id = ?',
        [$remarks, $request['effective_owner'], $requestId]
    );

    $startsTodayOrEarlier = $request['request_date'] <= date('Y-m-d');
    $stmt = db_exec($pdo, 'SELECT transaction_id FROM transactions WHERE request_id = ? LIMIT 1 FOR UPDATE', [$requestId]);

    if ($startsTodayOrEarlier && !$stmt->fetch()) {
        db_exec(
            $pdo,
            'INSERT INTO transactions
                (request_id, student_id, borrower_user_id, item_id, quantity_borrowed, borrow_date, expected_return_date, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, "ONGOING")',
            [
                $requestId,
                $request['student_id'],
                $request['borrower_user_id'],
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

    db_exec(
        $pdo,
        'INSERT INTO borrow_appointments
            (request_id, student_id, borrower_user_id, item_id, owner_official_id, appointment_date, appointment_end_date, status, notes)
         VALUES (?, ?, ?, ?, ?, ?, ?, "SCHEDULED", ?)
         ON DUPLICATE KEY UPDATE
            borrower_user_id = VALUES(borrower_user_id),
            owner_official_id = VALUES(owner_official_id),
            appointment_date = VALUES(appointment_date),
            appointment_end_date = VALUES(appointment_end_date),
            status = "SCHEDULED",
            notes = VALUES(notes)',
        [
            $requestId,
            $request['student_id'],
            $request['borrower_user_id'],
            $request['item_id'],
            $request['effective_owner'],
            $request['request_date'],
            $expectedReturn,
            $remarks,
        ]
    );

    log_audit($pdo, 'borrow_request_approve', 'borrow_request', $requestId, ['remarks' => $remarks]);
    $pdo->commit();

    respond_success('../pages/requests.php', 'approved');
} catch (Throwable $error) {
    rollback_if_active($pdo);
    log_internal_error('requests', $error);

    $knownCodes = ['request_not_pending', 'item_unavailable', 'insufficient_quantity', 'not_allowed', 'owner_required'];
    $code = in_array($error->getMessage(), $knownCodes, true) ? $error->getMessage() : 'approve_failed';

    respond_error('../pages/requests.php', $code, 'The borrow request could not be processed.');
}
