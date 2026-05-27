<?php
require_once __DIR__ . '/_helpers.php';

require_post('../pages/admin-settings.php');
require_admin();

function csv_headers(array $headers)
{
    return array_map(function ($header) {
        return strtolower(trim((string) $header, " \t\n\r\0\x0B\xEF\xBB\xBF"));
    }, $headers);
}

function csv_value(array $row, array $headers, array $keys)
{
    foreach ($keys as $key) {
        $index = array_search($key, $headers, true);
        if ($index !== false && isset($row[$index])) {
            return clean($row[$index]);
        }
    }

    return '';
}

function csv_has_any(array $headers, array $keys)
{
    foreach ($keys as $key) {
        if (in_array($key, $headers, true)) {
            return true;
        }
    }

    return false;
}

function csv_validate_headers($dataset, array $headers)
{
    $nameKeys = ['full_name', 'name', 'first_name', 'firstname'];
    $lastNameKeys = ['full_name', 'name', 'last_name', 'lastname'];

    $requirements = [
        'students' => [
            ['student_id', 'id_number', 'id'],
            $nameKeys,
            $lastNameKeys,
        ],
        'faculty' => [
            ['official_id', 'id_number', 'id'],
            $nameKeys,
            $lastNameKeys,
        ],
        'masterlist' => [
            ['user_type', 'type'],
            ['student_id', 'official_id', 'id_number', 'id'],
            $nameKeys,
            $lastNameKeys,
        ],
        'equipment' => [
            ['item_name', 'equipment_name', 'name'],
            ['quantity', 'qty'],
        ],
        'materials' => [
            ['item_name', 'material_name', 'name'],
            ['quantity', 'qty'],
        ],
    ];

    foreach ($requirements[$dataset] as $group) {
        if (!csv_has_any($headers, $group)) {
            throw new InvalidArgumentException('CSV headers do not match the required schema.');
        }
    }
}

function csv_row_is_blank(array $row)
{
    foreach ($row as $value) {
        if (clean($value) !== '') {
            return false;
        }
    }

    return true;
}

function save_masterlist_csv_row(PDO $pdo, array $row, array $headers, $forcedType = '')
{
    $userType = strtolower($forcedType ?: csv_value($row, $headers, ['user_type', 'type']));
    $firstName = csv_value($row, $headers, ['first_name', 'firstname']);
    $lastName = csv_value($row, $headers, ['last_name', 'lastname']);
    $fullName = csv_value($row, $headers, ['full_name', 'name']);

    if ($fullName !== '' && ($firstName === '' || $lastName === '')) {
        [$firstName, $lastName] = split_full_name($fullName);
    }

    if ($firstName === '' || $lastName === '') {
        throw new InvalidArgumentException('Missing first name or last name.');
    }

    if ($userType === 'faculty') {
        $officialId = csv_value($row, $headers, ['official_id', 'id_number', 'id']);
        if ($officialId === '') {
            throw new InvalidArgumentException('Missing official ID.');
        }

        $department = csv_value($row, $headers, ['department', 'position', 'course']);
        $positionCode = get_or_create_position($pdo, $department ?: 'Faculty');
        $userId = ensure_user_for_official($pdo, $officialId, $firstName, $lastName, $positionCode, null, 'faculty', 1);
        log_audit($pdo, 'masterlist_import_row', 'officials_masterlist', $officialId, ['user_id' => $userId]);
        return;
    }

    if ($forcedType === '' && $userType !== 'student') {
        throw new InvalidArgumentException('Invalid user type.');
    }

    $studentId = csv_value($row, $headers, ['student_id', 'id_number', 'id']);
    $studentId = require_positive_int($studentId, 'Student ID');
    $courseCode = get_or_create_course($pdo, csv_value($row, $headers, ['course', 'course_code', 'department']));
    $userId = ensure_user_for_student(
        $pdo,
        $studentId,
        $firstName,
        $lastName,
        $courseCode,
        normalized_year(csv_value($row, $headers, ['year_level', 'year'])),
        null,
        'student',
        1
    );
    log_audit($pdo, 'masterlist_import_row', 'master_list', $studentId, ['user_id' => $userId]);
}

function save_item_csv_row(PDO $pdo, array $row, array $headers, $dataset)
{
    $itemName = compact_spaces(csv_value($row, $headers, ['item_name', 'equipment_name', 'material_name', 'name']));
    if ($itemName === '') {
        throw new InvalidArgumentException('Missing item name.');
    }

    $categoryId = get_or_create_category(
        $pdo,
        csv_value($row, $headers, ['category']) ?: ($dataset === 'materials' ? 'Materials' : 'Equipment')
    );
    $unitId = get_or_create_unit($pdo, csv_value($row, $headers, ['unit']) ?: 'pcs');
    $quantity = require_non_negative_int(csv_value($row, $headers, ['quantity', 'qty']), 'Quantity');
    $description = csv_value($row, $headers, ['description']);
    $dateAdded = normalized_date_or_today(csv_value($row, $headers, ['date_added']));

    assert_item_not_duplicate($pdo, $itemName, $categoryId);

    if ($dataset === 'materials') {
        $unitPrice = csv_value($row, $headers, ['unit_price', 'price']);
        if ($unitPrice !== '' && !preg_match('/^\d+(\.\d{1,2})?$/', $unitPrice)) {
            throw new InvalidArgumentException('Invalid unit price.');
        }
        $description = trim($description . "\nType: Material" . ($unitPrice !== '' ? "\nUnit price: PHP " . $unitPrice : ''));
    } else {
        $itemCode = csv_value($row, $headers, ['item_code', 'code']);
        $condition = csv_value($row, $headers, ['condition']) ?: 'Good';
        $description = trim($description . "\nType: Equipment\nItem code: " . $itemCode . "\nCondition: " . $condition);
    }

    $stockStatus = inventory_stock_status($quantity);
    db_exec(
        $pdo,
        'INSERT INTO items
            (item_name, description, unit_id, category_id, total_quantity, available_quantity, date_added, status, stock_status)
         VALUES (?, ?, ?, ?, ?, ?, ?, "active", ?)',
        [$itemName, $description, $unitId, $categoryId, $quantity, $quantity, $dateAdded, $stockStatus]
    );

    $itemId = (int) $pdo->lastInsertId();
    log_audit($pdo, 'item_import_row', 'items', $itemId, ['item_name' => $itemName, 'dataset' => $dataset]);
}

function import_csv(PDO $pdo, $dataset, $filePath, $mode = 'safe')
{
    $handle = fopen($filePath, 'r');
    if (!$handle) {
        throw new RuntimeException('CSV file could not be opened.');
    }

    $rawHeaders = fgetcsv($handle);
    if (!$rawHeaders) {
        fclose($handle);
        throw new InvalidArgumentException('CSV file is empty.');
    }

    $headers = csv_headers($rawHeaders);
    csv_validate_headers($dataset, $headers);

    $strict = $mode === 'strict';
    $summary = [
        'total' => 0,
        'success' => 0,
        'failed' => 0,
        'errors' => [],
    ];

    if ($strict) {
        $pdo->beginTransaction();
    }

    try {
        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (csv_row_is_blank($row)) {
                continue;
            }

            $summary['total']++;

            try {
                if (!$strict) {
                    $pdo->beginTransaction();
                }

                if (in_array($dataset, ['students', 'faculty', 'masterlist'], true)) {
                    $forcedType = $dataset === 'masterlist' ? '' : rtrim($dataset, 's');
                    save_masterlist_csv_row($pdo, $row, $headers, $forcedType);
                } else {
                    save_item_csv_row($pdo, $row, $headers, $dataset);
                }

                if (!$strict) {
                    $pdo->commit();
                }

                $summary['success']++;
            } catch (Throwable $rowError) {
                if (!$strict) {
                    rollback_if_active($pdo);
                }

                $summary['failed']++;
                $summary['errors'][] = [
                    'row' => $rowNumber,
                    'reason' => $rowError->getMessage(),
                ];

                if ($strict) {
                    throw $rowError;
                }
            }
        }

        if ($strict) {
            $pdo->commit();
        }
    } catch (Throwable $error) {
        rollback_if_active($pdo);

        if ($strict) {
            $summary['success'] = 0;
        }
    } finally {
        fclose($handle);
    }

    log_audit(
        $pdo,
        'csv_import',
        null,
        $dataset,
        [
            'mode' => $strict ? 'strict' : 'safe',
            'total' => $summary['total'],
            'success' => $summary['success'],
            'failed' => $summary['failed'],
            'errors' => array_slice($summary['errors'], 0, 50),
        ]
    );

    return $summary;
}

function update_settings_user_role(PDO $pdo, $userId, $username, $newRole)
{
    $role = strtolower(clean($newRole)) === 'revoke' ? 'student' : db_role($newRole);

    if ($userId > 0) {
        $stmt = db_exec($pdo, 'SELECT user_id, role FROM users WHERE user_id = ? FOR UPDATE', [$userId]);
    } else {
        $stmt = db_exec($pdo, 'SELECT user_id, role FROM users WHERE username = ? FOR UPDATE', [$username]);
    }

    $user = $stmt->fetch();
    if (!$user) {
        throw new RuntimeException('User not found.');
    }

    db_exec($pdo, 'UPDATE users SET role = ?, is_active = 1 WHERE user_id = ?', [$role, $user['user_id']]);
    log_audit(
        $pdo,
        'user_role_update',
        'users',
        $user['user_id'],
        ['old_role' => $user['role'], 'new_role' => $role]
    );
}

try {
    if (!empty($_FILES['csv_file']['tmp_name'])) {
        $dataset = post_value('dataset', 'students');
        $mode = strtolower(post_value('import_mode', 'safe')) === 'strict' ? 'strict' : 'safe';

        if (!in_array($dataset, ['students', 'faculty', 'masterlist', 'equipment', 'materials'], true)) {
            respond_error('../pages/admin-settings.php', 'dataset', 'Invalid import dataset.');
        }

        if (in_array($dataset, ['faculty', 'masterlist'], true) && !can_manage_user_roles($_SESSION['role'] ?? '')) {
            respond_error('../pages/admin-settings.php', 'not_allowed', 'Only administrators can import staff or mixed masterlists.');
        }

        if (($_FILES['csv_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            respond_error('../pages/admin-settings.php', 'upload_failed', 'CSV upload failed.');
        }

        $originalName = $_FILES['csv_file']['name'] ?? '';
        if (strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) !== 'csv') {
            respond_error('../pages/admin-settings.php', 'file_type', 'Only CSV files are accepted.');
        }

        $summary = import_csv($pdo, $dataset, $_FILES['csv_file']['tmp_name'], $mode);
        respond_success(
            '../pages/admin-settings.php',
            'imported',
            [
                'total' => $summary['total'],
                'count' => $summary['success'],
                'failed' => $summary['failed'],
            ]
        );
    }

    $userId = (int) post_value('user_id');
    $username = post_value('username');
    $newRole = post_value('new_role');

    if (!can_manage_user_roles($_SESSION['role'] ?? '')) {
        respond_error('../pages/admin-settings.php', 'not_allowed', 'Only administrators can change user roles.');
    }

    if ($newRole === '' || ($userId <= 0 && $username === '')) {
        respond_error('../pages/admin-settings.php', 'missing', 'User and role are required.');
    }

    $pdo->beginTransaction();
    update_settings_user_role($pdo, $userId, $username, $newRole);
    $pdo->commit();

    respond_success('../pages/admin-settings.php', 'role_updated');
} catch (Throwable $error) {
    rollback_if_active($pdo);
    log_internal_error('admin_settings', $error);
    respond_error('../pages/admin-settings.php', 'settings_failed', 'The settings request could not be completed.');
}
