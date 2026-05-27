<?php
require_once __DIR__ . '/../process/_helpers.php';

function h($value)
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function js($value)
{
    return json_encode((string) ($value ?? ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
}

function money($value)
{
    return number_format((float) $value, 2);
}

function one_value($sql, array $params = [])
{
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function all_rows($sql, array $params = [])
{
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function current_user()
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    return all_rows(
        'SELECT u.*, m.first_name, m.last_name, m.course_code, m.year_level
         FROM users u
         LEFT JOIN master_list m ON m.student_id = u.student_id
         WHERE u.user_id = ?
         LIMIT 1',
        [$_SESSION['user_id']]
    )[0] ?? null;
}

function user_full_name($user)
{
    $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
    return $name !== '' ? $name : ($user['username'] ?? 'User');
}

function initials($name)
{
    $parts = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY);
    $letters = '';

    foreach (array_slice($parts, 0, 2) as $part) {
        $letters .= strtoupper(substr($part, 0, 1));
    }

    return $letters ?: 'U';
}

function pending_request_count()
{
    return (int) one_value('SELECT COUNT(*) FROM borrow_request WHERE status = "PENDING"');
}

function material_condition($itemAlias = 'i', $categoryAlias = 'c')
{
    return "(LOWER(COALESCE({$categoryAlias}.category_name, '')) IN ('materials', 'material', 'campus materials', 'office supplies') OR {$itemAlias}.description LIKE '%Unit price:%' OR {$itemAlias}.description LIKE '%Type: Material%')";
}

function equipment_condition($itemAlias = 'i', $categoryAlias = 'c')
{
    return 'NOT ' . material_condition($itemAlias, $categoryAlias);
}

function unit_price_from_description($description)
{
    if (preg_match('/Unit price:\s*PHP\s*([0-9]+(?:\.[0-9]+)?)/i', (string) $description, $match)) {
        return (float) $match[1];
    }

    return 0.0;
}

function plain_description($description)
{
    $lines = preg_split('/\R/', (string) $description);
    $lines = array_filter($lines, function ($line) {
        return !preg_match('/^(Item code|Condition|Unit price|Type):/i', trim($line));
    });

    return trim(implode("\n", $lines));
}

function meta_value($description, $label)
{
    if (preg_match('/^' . preg_quote($label, '/') . ':\s*(.+)$/im', (string) $description, $match)) {
        return trim($match[1]);
    }

    return '';
}

function badge($status)
{
    $statusText = strtoupper((string) $status);
    $class = 'badge';

    if (in_array($statusText, ['PENDING'], true)) {
        $class .= ' badge-pending';
    } elseif (in_array($statusText, ['APPROVED', 'ONGOING'], true)) {
        $class .= ' badge-approved';
    } elseif (in_array($statusText, ['REJECTED', 'CANCELLED'], true)) {
        $class .= ' badge-rejected';
    } elseif ($statusText === 'RETURNED') {
        $class .= ' badge-returned';
    } elseif ($statusText === 'OVERDUE') {
        $class .= ' badge-rejected';
    }

    return '<span class="' . h($class) . '">' . h(ucfirst(strtolower($statusText))) . '</span>';
}

$currentUser = current_user();
$currentName = $currentUser ? user_full_name($currentUser) : 'User';
$currentInitials = initials($currentName);
$canManageBorrowWorkflow = can_manage_borrow_workflow($currentUser['role'] ?? ($_SESSION['role'] ?? ''));
$canManageUserRoles = can_manage_user_roles($currentUser['role'] ?? ($_SESSION['role'] ?? ''));
$pendingCount = pending_request_count();
