<?php
require_once __DIR__ . '/_helpers.php';

require_post('../pages/equipment.php');
require_admin();

$action = strtolower(post_value('action', 'save'));
$categoryId = (int) post_value('category_id');
$categoryName = compact_spaces(post_value('category_name'));

try {
    if ($action === 'delete' && $categoryId > 0) {
        $pdo->beginTransaction();

        $stmt = db_exec($pdo, 'SELECT category_id, category_name FROM category WHERE category_id = ? FOR UPDATE', [$categoryId]);
        $category = $stmt->fetch();

        if (!$category) {
            throw new RuntimeException('Category not found.');
        }

        $itemCount = (int) db_exec($pdo, 'SELECT COUNT(*) FROM items WHERE category_id = ?', [$categoryId])->fetchColumn();
        if ($itemCount > 0) {
            throw new RuntimeException('category_in_use');
        }

        db_exec($pdo, 'DELETE FROM category WHERE category_id = ?', [$categoryId]);
        log_audit($pdo, 'category_delete', 'category', $categoryId, ['category_name' => $category['category_name']]);
        $pdo->commit();

        respond_success('../pages/admin-settings.php', 'category_deleted');
    }

    if ($categoryName === '') {
        respond_error('../pages/admin-settings.php', 'missing_category', 'Category name is required.');
    }

    $pdo->beginTransaction();

    if ($categoryId > 0) {
        $stmt = db_exec($pdo, 'SELECT category_id, category_name FROM category WHERE category_id = ? FOR UPDATE', [$categoryId]);
        $current = $stmt->fetch();

        if (!$current) {
            throw new RuntimeException('Category not found.');
        }

        $stmt = db_exec(
            $pdo,
            'SELECT category_id FROM category WHERE LOWER(category_name) = LOWER(?) AND category_id <> ? LIMIT 1',
            [$categoryName, $categoryId]
        );
        $duplicate = $stmt->fetch();

        if ($duplicate) {
            $targetId = (int) $duplicate['category_id'];
            db_exec($pdo, 'UPDATE items SET category_id = ? WHERE category_id = ?', [$targetId, $categoryId]);
            db_exec($pdo, 'DELETE FROM category WHERE category_id = ?', [$categoryId]);
            log_audit(
                $pdo,
                'category_merge',
                'category',
                $targetId,
                ['from_category_id' => $categoryId, 'category_name' => $categoryName]
            );
        } else {
            db_exec($pdo, 'UPDATE category SET category_name = ? WHERE category_id = ?', [$categoryName, $categoryId]);
            log_audit($pdo, 'category_update', 'category', $categoryId, ['category_name' => $categoryName]);
        }
    } else {
        $categoryId = get_or_create_category($pdo, $categoryName);
    }

    $pdo->commit();
    respond_success('../pages/admin-settings.php', 'category_saved');
} catch (Throwable $error) {
    rollback_if_active($pdo);
    log_internal_error('categories', $error);

    $code = $error->getMessage() === 'category_in_use' ? 'category_in_use' : 'category_failed';
    respond_error('../pages/admin-settings.php', $code, 'The category could not be saved.');
}
