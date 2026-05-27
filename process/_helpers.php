<?php
require_once __DIR__ . '/../db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function clean($value)
{
    return trim((string) ($value ?? ''));
}

function compact_spaces($value)
{
    return preg_replace('/\s+/', ' ', clean($value));
}

function post_value($key, $default = '')
{
    return clean($_POST[$key] ?? $default);
}

function db_exec(PDO $pdo, $sql, array $params = [])
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function table_exists(PDO $pdo, $table)
{
    $stmt = db_exec(
        $pdo,
        'SELECT COUNT(*)
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
        [$table]
    );

    return (int) $stmt->fetchColumn() > 0;
}

function column_exists(PDO $pdo, $table, $column)
{
    $stmt = db_exec(
        $pdo,
        'SELECT COUNT(*)
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
        [$table, $column]
    );

    return (int) $stmt->fetchColumn() > 0;
}

function column_is_nullable(PDO $pdo, $table, $column)
{
    $stmt = db_exec(
        $pdo,
        'SELECT IS_NULLABLE
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
         LIMIT 1',
        [$table, $column]
    );

    return strtoupper((string) $stmt->fetchColumn()) === 'YES';
}

function index_exists(PDO $pdo, $table, $index)
{
    $stmt = db_exec(
        $pdo,
        'SELECT COUNT(*)
         FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?',
        [$table, $index]
    );

    return (int) $stmt->fetchColumn() > 0;
}

function ensure_system_schema(PDO $pdo)
{
    static $checked = false;

    if ($checked) {
        return;
    }

    if (!table_exists($pdo, 'audit_log')) {
        db_exec(
            $pdo,
            'CREATE TABLE IF NOT EXISTS audit_log (
                audit_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                actor_user_id INT NULL,
                action_type VARCHAR(80) NOT NULL,
                table_name VARCHAR(80) NULL,
                record_id VARCHAR(100) NULL,
                details TEXT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (audit_id),
                KEY idx_audit_actor (actor_user_id),
                KEY idx_audit_action (action_type),
                KEY idx_audit_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
        );
    }

    if (table_exists($pdo, 'users') && !column_exists($pdo, 'users', 'official_id')) {
        db_exec($pdo, 'ALTER TABLE users ADD COLUMN official_id VARCHAR(50) NULL AFTER student_id');
    }

    if (table_exists($pdo, 'users') && column_exists($pdo, 'users', 'student_id') && !column_is_nullable($pdo, 'users', 'student_id')) {
        db_exec($pdo, 'ALTER TABLE users MODIFY student_id INT(11) NULL');
    }

    if (table_exists($pdo, 'users') && column_exists($pdo, 'users', 'official_id') && !index_exists($pdo, 'users', 'idx_official_id')) {
        db_exec($pdo, 'ALTER TABLE users ADD UNIQUE KEY idx_official_id (official_id)');
    }

    if (table_exists($pdo, 'items') && !column_exists($pdo, 'items', 'stock_status')) {
        db_exec(
            $pdo,
            'ALTER TABLE items
             ADD COLUMN stock_status ENUM("out_of_stock","low_stock","available") NOT NULL DEFAULT "out_of_stock" AFTER status'
        );
    }

    if (table_exists($pdo, 'items') && column_exists($pdo, 'items', 'stock_status')) {
        db_exec(
            $pdo,
            'UPDATE items
             SET stock_status = CASE
                WHEN available_quantity <= 0 THEN "out_of_stock"
                WHEN available_quantity <= COALESCE(min_quantity_alert, 5) THEN "low_stock"
                ELSE "available"
             END'
        );
    }

    $checked = true;
}

function request_wants_json()
{
    $accept = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');
    $xhr = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');

    return strpos($accept, 'application/json') !== false || $xhr === 'xmlhttprequest';
}

function redirect_to($path, array $params = [])
{
    if ($params) {
        $path .= (strpos($path, '?') === false ? '?' : '&') . http_build_query($params);
    }

    header('Location: ' . $path);
    exit;
}

function redirect_back($fallback, array $params = [])
{
    redirect_to($_SERVER['HTTP_REFERER'] ?? $fallback, $params);
}

function respond_error($fallback, $code, $message = '', $status = 400, array $extra = [])
{
    $payload = [
        'ok' => false,
        'error' => [
            'code' => $code,
            'message' => $message !== '' ? $message : 'The request could not be completed.',
        ],
    ];

    if ($extra) {
        $payload['error']['details'] = $extra;
    }

    if (request_wants_json()) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }

    redirect_back($fallback, array_merge(['error' => $code], $extra));
}

function respond_success($target, $code, array $extra = [])
{
    $payload = ['ok' => true, 'success' => $code] + $extra;

    if (request_wants_json()) {
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }

    redirect_to($target, array_merge(['success' => $code], $extra));
}

function rollback_if_active(PDO $pdo)
{
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

function log_internal_error($context, Throwable $error)
{
    error_log('[InventorySystem][' . $context . '] ' . $error->getMessage());
}

function log_audit(PDO $pdo, $actionType, $tableName = null, $recordId = null, array $details = [])
{
    ensure_system_schema($pdo);

    $actorUserId = !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    db_exec(
        $pdo,
        'INSERT INTO audit_log (actor_user_id, action_type, table_name, record_id, details)
         VALUES (?, ?, ?, ?, ?)',
        [
            $actorUserId,
            substr(clean($actionType), 0, 80),
            $tableName !== null ? substr(clean($tableName), 0, 80) : null,
            $recordId !== null ? substr((string) $recordId, 0, 100) : null,
            $details ? json_encode($details) : null,
        ]
    );
}

function require_post($fallback)
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        redirect_to($fallback);
    }
}

function destroy_current_session()
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

function require_login()
{
    global $pdo;

    if (empty($_SESSION['user_id'])) {
        redirect_to('../pages/login.php', ['error' => 'login_required']);
    }

    $stmt = db_exec(
        $pdo,
        'SELECT user_id, role, is_default_password
         FROM users
         WHERE user_id = ? AND is_active = 1
         LIMIT 1',
        [(int) $_SESSION['user_id']]
    );
    $user = $stmt->fetch();

    if (!$user || !valid_role($user['role'])) {
        destroy_current_session();
        redirect_to('../pages/login.php', ['error' => 'login_required']);
    }

    $_SESSION['role'] = $user['role'];
    $_SESSION['force_password_change'] = (int) $user['is_default_password'] === 1;

    $currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
    $passwordPages = ['change_password.php', 'change-password.php', 'student-change-password.php'];
    if (!empty($_SESSION['force_password_change']) && !in_array($currentScript, $passwordPages, true)) {
        $passwordPage = ($_SESSION['role'] ?? '') === 'student'
            ? '../pages/student-change-password.php'
            : '../pages/change-password.php';
        redirect_to($passwordPage, ['first_login' => '1']);
    }
}

function valid_role($role)
{
    return in_array($role, ['student', 'faculty', 'admin'], true);
}

function is_admin_role($role)
{
    return in_array($role, ['admin', 'faculty'], true);
}

function can_manage_borrow_workflow($role)
{
    return $role === 'admin';
}

function can_manage_user_roles($role)
{
    return $role === 'admin';
}

function require_admin()
{
    require_login();

    if (!is_admin_role($_SESSION['role'] ?? '')) {
        redirect_to('../pages/student-dashboard.php', ['error' => 'not_allowed']);
    }
}

function require_borrow_workflow_manager()
{
    require_login();

    if (!can_manage_borrow_workflow($_SESSION['role'] ?? '')) {
        redirect_to(home_for_role($_SESSION['role'] ?? 'student'), ['error' => 'not_allowed']);
    }
}

function require_user_role_manager()
{
    require_login();

    if (!can_manage_user_roles($_SESSION['role'] ?? '')) {
        redirect_to(home_for_role($_SESSION['role'] ?? 'student'), ['error' => 'not_allowed']);
    }
}

function require_borrower()
{
    require_login();

    if (($_SESSION['role'] ?? '') !== 'student') {
        redirect_to('../pages/admin-dashboard.php', ['error' => 'not_allowed']);
    }
}

function db_role($role)
{
    $role = strtolower(clean($role));

    if ($role === 'administrator' || $role === 'admin') {
        return 'admin';
    }

    if ($role === 'staff' || $role === 'faculty') {
        return 'faculty';
    }

    return 'student';
}

function home_for_role($role)
{
    return $role === 'student' ? '../pages/student-dashboard.php' : '../pages/admin-dashboard.php';
}

function split_full_name($fullName)
{
    $parts = preg_split('/\s+/', clean($fullName), -1, PREG_SPLIT_NO_EMPTY);

    if (!$parts) {
        return ['Unknown', 'User'];
    }

    $firstName = array_shift($parts);
    $lastName = $parts ? implode(' ', $parts) : 'User';

    return [$firstName, $lastName];
}

function normalized_year($value)
{
    $value = clean($value);
    return preg_match('/^\d{4}$/', $value) ? $value : null;
}

function require_non_negative_int($value, $fieldName)
{
    $value = clean($value);

    if ($value === '' || !preg_match('/^\d+$/', $value)) {
        throw new InvalidArgumentException($fieldName . ' must be a non-negative whole number.');
    }

    return (int) $value;
}

function require_positive_int($value, $fieldName)
{
    $number = require_non_negative_int($value, $fieldName);

    if ($number <= 0) {
        throw new InvalidArgumentException($fieldName . ' must be greater than zero.');
    }

    return $number;
}

function normalized_date_or_today($value)
{
    $value = clean($value);

    if ($value === '') {
        return date('Y-m-d');
    }

    $date = DateTime::createFromFormat('Y-m-d', $value);
    if (!$date || $date->format('Y-m-d') !== $value) {
        throw new InvalidArgumentException('Date must use YYYY-MM-DD format.');
    }

    return $value;
}

function get_or_create_course(PDO $pdo, $courseCode, $courseName = '')
{
    $courseCode = substr(strtoupper(clean($courseCode)), 0, 20);
    if ($courseCode === '') {
        return null;
    }

    $courseName = clean($courseName) ?: $courseCode;
    $courseName = substr($courseName, 0, 20);

    $stmt = db_exec($pdo, 'SELECT course_code FROM course WHERE course_code = ? LIMIT 1', [$courseCode]);

    if (!$stmt->fetch()) {
        db_exec($pdo, 'INSERT INTO course (course_code, course_name) VALUES (?, ?)', [$courseCode, $courseName]);
        log_audit($pdo, 'course_create', 'course', $courseCode, ['course_name' => $courseName]);
    }

    return $courseCode;
}

function get_or_create_category(PDO $pdo, $name)
{
    $name = substr(compact_spaces($name), 0, 200);
    if ($name === '') {
        return null;
    }

    $stmt = db_exec(
        $pdo,
        'SELECT category_id FROM category WHERE LOWER(category_name) = LOWER(?) LIMIT 1',
        [$name]
    );
    $row = $stmt->fetch();

    if ($row) {
        return (int) $row['category_id'];
    }

    try {
        db_exec($pdo, 'INSERT INTO category (category_name) VALUES (?)', [$name]);
        $categoryId = (int) $pdo->lastInsertId();
        log_audit($pdo, 'category_create', 'category', $categoryId, ['category_name' => $name]);
        return $categoryId;
    } catch (PDOException $error) {
        $stmt = db_exec(
            $pdo,
            'SELECT category_id FROM category WHERE LOWER(category_name) = LOWER(?) LIMIT 1',
            [$name]
        );
        $row = $stmt->fetch();

        if ($row) {
            return (int) $row['category_id'];
        }

        throw $error;
    }
}

function require_existing_category_id(PDO $pdo, $categoryId, $categoryName = '')
{
    $categoryId = (int) clean($categoryId);

    if ($categoryId > 0) {
        $stmt = db_exec($pdo, 'SELECT category_id FROM category WHERE category_id = ? LIMIT 1', [$categoryId]);
        if ($stmt->fetch()) {
            return $categoryId;
        }
    }

    $categoryName = compact_spaces($categoryName);
    if ($categoryName !== '') {
        $stmt = db_exec(
            $pdo,
            'SELECT category_id FROM category WHERE LOWER(category_name) = LOWER(?) LIMIT 1',
            [$categoryName]
        );
        $row = $stmt->fetch();
        if ($row) {
            return (int) $row['category_id'];
        }
    }

    throw new InvalidArgumentException('Please choose a valid category.');
}

function get_or_create_unit(PDO $pdo, $name)
{
    $name = substr(compact_spaces($name) ?: 'pcs', 0, 20);

    $stmt = db_exec(
        $pdo,
        'SELECT unit_id FROM unit WHERE LOWER(unit_name) = LOWER(?) LIMIT 1',
        [$name]
    );
    $row = $stmt->fetch();

    if ($row) {
        return (int) $row['unit_id'];
    }

    try {
        db_exec($pdo, 'INSERT INTO unit (unit_name) VALUES (?)', [$name]);
        $unitId = (int) $pdo->lastInsertId();
        log_audit($pdo, 'unit_create', 'unit', $unitId, ['unit_name' => $name]);
        return $unitId;
    } catch (PDOException $error) {
        $stmt = db_exec(
            $pdo,
            'SELECT unit_id FROM unit WHERE LOWER(unit_name) = LOWER(?) LIMIT 1',
            [$name]
        );
        $row = $stmt->fetch();

        if ($row) {
            return (int) $row['unit_id'];
        }

        throw $error;
    }
}

function require_existing_unit_id(PDO $pdo, $unitId, $unitName = '')
{
    $unitId = (int) clean($unitId);

    if ($unitId > 0) {
        $stmt = db_exec($pdo, 'SELECT unit_id FROM unit WHERE unit_id = ? LIMIT 1', [$unitId]);
        if ($stmt->fetch()) {
            return $unitId;
        }
    }

    $unitName = compact_spaces($unitName);
    if ($unitName !== '') {
        $stmt = db_exec(
            $pdo,
            'SELECT unit_id FROM unit WHERE LOWER(unit_name) = LOWER(?) LIMIT 1',
            [$unitName]
        );
        $row = $stmt->fetch();
        if ($row) {
            return (int) $row['unit_id'];
        }
    }

    throw new InvalidArgumentException('Please choose a valid unit.');
}

function get_or_create_position(PDO $pdo, $name)
{
    $name = compact_spaces($name) ?: 'FACULTY';
    $code = substr(strtoupper(preg_replace('/[^A-Z0-9]+/i', '', $name)), 0, 20);

    if ($code === '') {
        $code = 'FACULTY';
    }

    $stmt = db_exec($pdo, 'SELECT position_code FROM positions WHERE position_code = ? LIMIT 1', [$code]);

    if (!$stmt->fetch()) {
        db_exec(
            $pdo,
            'INSERT INTO positions (position_code, position_name) VALUES (?, ?)',
            [$code, substr($name, 0, 250)]
        );
        log_audit($pdo, 'position_create', 'positions', $code, ['position_name' => $name]);
    }

    return $code;
}

function require_existing_course_code(PDO $pdo, $courseCode, $courseName = '')
{
    $courseCode = substr(strtoupper(clean($courseCode)), 0, 20);

    if ($courseCode !== '') {
        $stmt = db_exec($pdo, 'SELECT course_code FROM course WHERE course_code = ? LIMIT 1', [$courseCode]);
        $row = $stmt->fetch();
        if ($row) {
            return $row['course_code'];
        }
    }

    $courseName = compact_spaces($courseName);
    if ($courseName !== '') {
        $stmt = db_exec(
            $pdo,
            'SELECT course_code FROM course WHERE LOWER(course_name) = LOWER(?) LIMIT 1',
            [$courseName]
        );
        $row = $stmt->fetch();
        if ($row) {
            return $row['course_code'];
        }
    }

    throw new InvalidArgumentException('Please choose a valid course.');
}

function require_existing_position_code(PDO $pdo, $positionCode, $positionName = '')
{
    $positionCode = substr(clean($positionCode), 0, 20);

    if ($positionCode !== '') {
        $stmt = db_exec($pdo, 'SELECT position_code FROM positions WHERE position_code = ? LIMIT 1', [$positionCode]);
        $row = $stmt->fetch();
        if ($row) {
            return $row['position_code'];
        }
    }

    $positionName = compact_spaces($positionName);
    if ($positionName !== '') {
        $stmt = db_exec(
            $pdo,
            'SELECT position_code FROM positions WHERE LOWER(position_name) = LOWER(?) LIMIT 1',
            [$positionName]
        );
        $row = $stmt->fetch();
        if ($row) {
            return $row['position_code'];
        }
    }

    throw new InvalidArgumentException('Please choose a valid faculty position.');
}

function next_student_id(PDO $pdo, $preferred = null)
{
    if ($preferred && $preferred > 0) {
        $stmt = db_exec($pdo, 'SELECT student_id FROM master_list WHERE student_id = ? LIMIT 1', [$preferred]);

        if (!$stmt->fetch()) {
            return (int) $preferred;
        }
    }

    do {
        $studentId = random_int(100000, 2147483647);
        $stmt = db_exec($pdo, 'SELECT student_id FROM master_list WHERE student_id = ? LIMIT 1', [$studentId]);
    } while ($stmt->fetch());

    return $studentId;
}

function save_master_record(PDO $pdo, $studentId, $firstName, $lastName, $courseCode = null, $yearLevel = null)
{
    $studentId = require_positive_int($studentId, 'Student ID');
    $firstName = substr(compact_spaces($firstName), 0, 100);
    $lastName = substr(compact_spaces($lastName), 0, 255);

    if ($firstName === '' || $lastName === '') {
        throw new InvalidArgumentException('First name and last name are required.');
    }

    db_exec(
        $pdo,
        'INSERT INTO master_list (student_id, first_name, last_name, course_code, year_level)
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            first_name = VALUES(first_name),
            last_name = VALUES(last_name),
            course_code = VALUES(course_code),
            year_level = VALUES(year_level)',
        [$studentId, $firstName, $lastName, $courseCode, $yearLevel]
    );
}

function save_official_record(PDO $pdo, $officialId, $firstName, $lastName, $positionCode = null)
{
    $officialId = substr(compact_spaces($officialId), 0, 50);
    $firstName = substr(compact_spaces($firstName), 0, 100);
    $lastName = substr(compact_spaces($lastName), 0, 100);

    if ($officialId === '' || $firstName === '' || $lastName === '') {
        throw new InvalidArgumentException('Official ID, first name, and last name are required.');
    }

    db_exec(
        $pdo,
        'INSERT INTO officials_masterlist (official_id, first_name, last_name, position_code, is_active)
         VALUES (?, ?, ?, ?, 1)
         ON DUPLICATE KEY UPDATE
            first_name = VALUES(first_name),
            last_name = VALUES(last_name),
            position_code = VALUES(position_code),
            is_active = 1',
        [$officialId, $firstName, $lastName, $positionCode]
    );
}

function user_display_name_by_id(PDO $pdo, $userId)
{
    $stmt = db_exec(
        $pdo,
        'SELECT COALESCE(NULLIF(TRIM(CONCAT(COALESCE(m.first_name, ""), " ", COALESCE(m.last_name, ""))), ""),
                        NULLIF(TRIM(CONCAT(COALESCE(o.first_name, ""), " ", COALESCE(o.last_name, ""))), ""),
                        u.username) AS full_name
         FROM users u
         LEFT JOIN master_list m ON m.student_id = u.student_id
         LEFT JOIN officials_masterlist o ON o.official_id = u.official_id
         WHERE u.user_id = ?
         LIMIT 1',
        [$userId]
    );

    return (string) ($stmt->fetchColumn() ?: 'User');
}

function default_account_password($identifier, $lastName)
{
    return compact_spaces($identifier) . compact_spaces($lastName);
}

function ensure_user_for_student(PDO $pdo, $studentId, $firstName, $lastName, $courseCode = null, $yearLevel = null, $password = null, $role = 'student', $isActive = 1)
{
    $studentId = require_positive_int($studentId, 'Student ID');
    $role = db_role($role);
    $username = (string) $studentId;

    save_master_record($pdo, $studentId, $firstName, $lastName, $courseCode, $yearLevel);

    $stmt = db_exec(
        $pdo,
        'SELECT * FROM users WHERE username = ? OR student_id = ? FOR UPDATE',
        [$username, $studentId]
    );
    $existing = $stmt->fetchAll();

    if (count($existing) > 1) {
        throw new RuntimeException('Duplicate user identifier.');
    }

    foreach ($existing as $user) {
        if ($user['username'] !== $username && (int) $user['student_id'] !== $studentId) {
            throw new RuntimeException('Duplicate user identifier.');
        }
    }

    $user = $existing[0] ?? null;
    if ($user) {
        db_exec(
            $pdo,
            'UPDATE users
             SET username = ?, student_id = ?, official_id = NULL, is_active = ?
             WHERE user_id = ?',
            [$username, $studentId, (int) $isActive, $user['user_id']]
        );
        return (int) $user['user_id'];
    }

    db_exec(
        $pdo,
        'INSERT INTO users (student_id, official_id, username, password, role, is_default_password, is_active)
         VALUES (?, NULL, ?, ?, ?, 1, ?)',
        [
            $studentId,
            $username,
            password_hash($password ?: default_account_password($username, $lastName), PASSWORD_DEFAULT),
            $role,
            (int) $isActive,
        ]
    );
    $userId = (int) $pdo->lastInsertId();
    log_audit($pdo, 'user_create', 'users', $userId, ['username' => $username, 'role' => $role]);

    return $userId;
}

function ensure_user_for_official(PDO $pdo, $officialId, $firstName, $lastName, $positionCode = null, $password = null, $role = 'faculty', $isActive = 1)
{
    $officialId = substr(compact_spaces($officialId), 0, 50);
    $role = db_role($role);

    if ($role === 'student') {
        $role = 'faculty';
    }

    save_official_record($pdo, $officialId, $firstName, $lastName, $positionCode);

    $stmt = db_exec(
        $pdo,
        'SELECT * FROM users WHERE username = ? OR official_id = ? FOR UPDATE',
        [$officialId, $officialId]
    );
    $existing = $stmt->fetchAll();

    if (count($existing) > 1) {
        throw new RuntimeException('Duplicate official identifier.');
    }

    foreach ($existing as $user) {
        if ($user['username'] !== $officialId && $user['official_id'] !== $officialId) {
            throw new RuntimeException('Duplicate official identifier.');
        }
    }

    $user = $existing[0] ?? null;
    if ($user) {
        db_exec(
            $pdo,
            'UPDATE users
             SET username = ?, official_id = ?, role = ?, is_active = ?
             WHERE user_id = ?',
            [$officialId, $officialId, $role, (int) $isActive, $user['user_id']]
        );
        return (int) $user['user_id'];
    }

    db_exec(
        $pdo,
        'INSERT INTO users (student_id, official_id, username, password, role, is_default_password, is_active)
         VALUES (NULL, ?, ?, ?, ?, 1, ?)',
        [
            $officialId,
            $officialId,
            password_hash($password ?: default_account_password($officialId, $lastName), PASSWORD_DEFAULT),
            $role,
            (int) $isActive,
        ]
    );
    $userId = (int) $pdo->lastInsertId();
    log_audit($pdo, 'user_create', 'users', $userId, ['username' => $officialId, 'role' => $role]);

    return $userId;
}

function create_manual_user(PDO $pdo, $identifier, $firstName, $lastName, $role, $password, $isActive = 1)
{
    $identifier = compact_spaces($identifier);
    $role = db_role($role);

    if ($identifier === '' || compact_spaces($firstName) === '' || compact_spaces($lastName) === '') {
        throw new InvalidArgumentException('Identifier and name are required.');
    }

    $stmt = db_exec($pdo, 'SELECT user_id FROM users WHERE username = ? LIMIT 1', [$identifier]);
    if ($stmt->fetch()) {
        throw new RuntimeException('Duplicate user identifier.');
    }

    if ($role === 'student') {
        $studentId = ctype_digit($identifier) ? (int) $identifier : next_student_id($pdo);
        save_master_record($pdo, $studentId, $firstName, $lastName);

        $stmt = db_exec(
            $pdo,
            'SELECT user_id FROM users WHERE username = ? OR student_id = ? LIMIT 1',
            [$identifier, $studentId]
        );
        if ($stmt->fetch()) {
            throw new RuntimeException('Duplicate user identifier.');
        }

        db_exec(
            $pdo,
            'INSERT INTO users (student_id, official_id, username, password, role, is_default_password, is_active)
             VALUES (?, NULL, ?, ?, "student", 1, ?)',
            [$studentId, $identifier, password_hash($password ?: default_account_password($identifier, $lastName), PASSWORD_DEFAULT), (int) $isActive]
        );
        $userId = (int) $pdo->lastInsertId();
        log_audit($pdo, 'user_create', 'users', $userId, ['username' => $identifier, 'role' => 'student']);

        return $userId;
    }

    $positionCode = get_or_create_position($pdo, $role === 'admin' ? 'Administrator' : 'Faculty');
    return ensure_user_for_official($pdo, $identifier, $firstName, $lastName, $positionCode, $password, $role, $isActive);
}

function inventory_stock_status($availableQuantity, $threshold = 5)
{
    $availableQuantity = (int) $availableQuantity;
    $threshold = max(0, (int) $threshold);

    if ($availableQuantity <= 0) {
        return 'out_of_stock';
    }

    return $availableQuantity <= $threshold ? 'low_stock' : 'available';
}

function assert_item_not_duplicate(PDO $pdo, $itemName, $categoryId, $itemId = 0)
{
    $stmt = db_exec(
        $pdo,
        'SELECT item_id
         FROM items
         WHERE LOWER(item_name) = LOWER(?) AND category_id = ? AND item_id <> ?
         LIMIT 1',
        [$itemName, $categoryId, (int) $itemId]
    );

    if ($stmt->fetch()) {
        throw new RuntimeException('Duplicate item under the same category.');
    }
}

function item_dependency_count(PDO $pdo, $itemId)
{
    $requests = (int) db_exec($pdo, 'SELECT COUNT(*) FROM borrow_request WHERE item_id = ?', [$itemId])->fetchColumn();
    $transactions = (int) db_exec($pdo, 'SELECT COUNT(*) FROM transactions WHERE item_id = ?', [$itemId])->fetchColumn();

    return $requests + $transactions;
}

function password_matches($plain, $stored)
{
    return password_verify($plain, $stored) || hash_equals((string) $stored, (string) $plain);
}

function days_between($startDate, $endDate)
{
    try {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $days = (int) $start->diff($end)->format('%r%a');
        return max(1, $days);
    } catch (Exception $e) {
        return 1;
    }
}

ensure_system_schema($pdo);
