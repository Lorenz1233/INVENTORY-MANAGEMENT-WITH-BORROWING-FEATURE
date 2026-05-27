<?php
require_once __DIR__ . '/_helpers.php';

require_post('../pages/student-browse.php');
require_borrower();

$studentId = (int) ($_SESSION['student_id'] ?? 0);
$itemId = (int) post_value('equipment_id');
$purpose = post_value('purpose');

try {
    $quantity = require_positive_int(post_value('quantity'), 'Quantity');
    $borrowDate = normalized_date_or_today(post_value('borrow_date'));
    $postedDays = post_value('days_to_borrow');
    $dueDate = post_value('due_date');
    $days = $postedDays !== ''
        ? require_positive_int($postedDays, 'Days to borrow')
        : days_between($borrowDate, $dueDate ?: $borrowDate);

    if ($studentId <= 0 || $itemId <= 0 || $purpose === '') {
        respond_error('../pages/student-browse.php', 'missing', 'Item, quantity, borrow date, days, and purpose are required.');
    }

    $pdo->beginTransaction();

    $stmt = db_exec($pdo, 'SELECT student_id FROM master_list WHERE student_id = ? LIMIT 1', [$studentId]);
    if (!$stmt->fetch()) {
        throw new RuntimeException('student_not_found');
    }

    $stmt = db_exec(
        $pdo,
        'SELECT available_quantity
         FROM items
         WHERE item_id = ? AND status = "active"
         FOR UPDATE',
        [$itemId]
    );
    $item = $stmt->fetch();

    if (!$item || $quantity > (int) $item['available_quantity']) {
        throw new RuntimeException('insufficient_stock');
    }

    db_exec(
        $pdo,
        'INSERT INTO borrow_request
            (student_id, item_id, quantity_requested, request_date, days_to_borrow, remarks)
         VALUES (?, ?, ?, ?, ?, ?)',
        [$studentId, $itemId, $quantity, $borrowDate, $days, $purpose]
    );
    $requestId = (int) $pdo->lastInsertId();
    log_audit($pdo, 'borrow_request_create', 'borrow_request', $requestId, ['item_id' => $itemId, 'quantity' => $quantity]);

    $pdo->commit();
    respond_success('../pages/student-requests.php', 'requested');
} catch (Throwable $error) {
    rollback_if_active($pdo);
    log_internal_error('student_browse', $error);

    $code = in_array($error->getMessage(), ['insufficient_stock', 'student_not_found'], true)
        ? $error->getMessage()
        : 'request_failed';

    respond_error('../pages/student-browse.php', $code, 'The borrow request could not be submitted.');
}
