<?php
require_once __DIR__ . '/../partials/page-data.php';
require_admin();

$categoryFilter = clean($_GET['category'] ?? '');
$statusFilter = clean($_GET['status'] ?? '');
$sort = clean($_GET['sort'] ?? 'name');
$where = [equipment_condition('i', 'c')];
$params = [];

if ($categoryFilter !== '') {
    $where[] = 'i.category_id = ?';
    $params[] = $categoryFilter;
}

if ($statusFilter === 'Available') {
    $where[] = 'i.status = "active" AND i.available_quantity > 0';
} elseif ($statusFilter === 'Borrowed') {
    $where[] = 'i.available_quantity < i.total_quantity';
} elseif ($statusFilter === 'Maintenance') {
    $where[] = 'i.status = "inactive"';
}

$orderBy = 'i.item_name ASC';
if ($sort === 'recent') {
    $orderBy = 'i.created_at DESC';
} elseif ($sort === 'qty') {
    $orderBy = 'i.total_quantity DESC, i.item_name ASC';
}

$equipmentRows = all_rows(
    'SELECT i.*, c.category_name, u.unit_name,
            CONCAT(o.first_name, " ", o.last_name) AS owner_name,
            COALESCE(
                NULLIF(TRIM(CONCAT(COALESCE(cm.first_name, ""), " ", COALESCE(cm.last_name, ""))), ""),
                NULLIF(TRIM(CONCAT(COALESCE(co.first_name, ""), " ", COALESCE(co.last_name, ""))), ""),
                creator.username
            ) AS added_by_name,
            item_totals.group_total_quantity,
            item_totals.group_available_quantity
     FROM items i
     LEFT JOIN category c ON c.category_id = i.category_id
     LEFT JOIN unit u ON u.unit_id = i.unit_id
     LEFT JOIN officials_masterlist o ON o.official_id = i.received_by_official_id
     LEFT JOIN users creator ON creator.user_id = i.created_by_user_id
     LEFT JOIN master_list cm ON cm.student_id = creator.student_id
     LEFT JOIN officials_masterlist co ON co.official_id = creator.official_id
     LEFT JOIN (
        SELECT LOWER(gi.item_name) AS item_key,
               gi.category_id,
               SUM(gi.total_quantity) AS group_total_quantity,
               SUM(gi.available_quantity) AS group_available_quantity
        FROM items gi
        LEFT JOIN category gc ON gc.category_id = gi.category_id
        WHERE ' . equipment_condition('gi', 'gc') . '
        GROUP BY LOWER(gi.item_name), gi.category_id
     ) item_totals ON item_totals.item_key = LOWER(i.item_name) AND item_totals.category_id = i.category_id
     WHERE ' . implode(' AND ', $where) . '
     ORDER BY ' . $orderBy,
    $params
);
$equipmentCategories = all_rows(
    'SELECT DISTINCT c.category_id, c.category_name
     FROM items i
     JOIN category c ON c.category_id = i.category_id
     WHERE ' . equipment_condition('i', 'c') . '
     ORDER BY c.category_name'
);
$categoryRows = all_rows('SELECT * FROM category ORDER BY category_name');
$unitRows = all_rows('SELECT * FROM unit ORDER BY unit_name');
$facultyOwnerRows = all_rows(
    'SELECT o.official_id, CONCAT(o.first_name, " ", o.last_name) AS full_name
     FROM officials_masterlist o
     ORDER BY o.last_name, o.first_name'
);
$ownerOptionsJson = json_encode($facultyOwnerRows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Equipment • MSU-MCEST CEMS</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../css/app.css" />
  <script>
    tailwind.config = { theme: { extend: { colors: {
      navy:'#0B2545','navy-dark':'#061A33','navy-soft':'#13335E',
      gold:'#D4A017','gold-dark':'#B8860B', surface:'#F7F8FB',
    }}}}
  </script>
</head>
<body class="min-h-screen bg-surface">
<!-- PHP: session check (admin/staff only) -->
<aside class="sidebar hidden md:flex md:flex-col w-64 bg-navy text-white fixed inset-y-0 left-0 z-30">
  <div class="px-5 py-5 border-b border-white/10 flex items-center gap-3">
    <img class="brand-logo" src="../assets/images/logo.png" width="44" height="44" alt="MSU-MCEST logo" />
    <div>
      <div class="text-sm font-semibold leading-tight">MSU-MCEST</div>
      <div class="text-xs text-white/60">Inventory System</div>
    </div>
  </div>
  <nav class="flex-1 py-4 text-sm">
    <p class="px-5 mt-3 first:mt-0 text-[11px] uppercase tracking-wider text-white/40 mb-2">Overview</p>
    <a href="admin-dashboard.php" class="nav-link">Dashboard</a>
    <p class="px-5 mt-3 first:mt-0 text-[11px] uppercase tracking-wider text-white/40 mb-2">Catalog</p>
    <a href="equipment.php" class="nav-link active">Equipment</a>
    <a href="materials.php" class="nav-link">Campus Materials</a>
    <p class="px-5 mt-3 first:mt-0 text-[11px] uppercase tracking-wider text-white/40 mb-2">Academics</p>
    <a href="courses.php" class="nav-link">Courses</a>
    <a href="students.php" class="nav-link">Students</a>
    <?php if ($canManageBorrowWorkflow): ?>
    <p class="px-5 mt-3 first:mt-0 text-[11px] uppercase tracking-wider text-white/40 mb-2">Transactions</p>
    <a href="requests.php" class="nav-link flex items-center">Borrow Requests<span class="ml-auto text-[10px] bg-gold text-navy-dark font-semibold px-2 py-0.5 rounded-full"><?php echo $pendingCount; ?></span></a>
    <a href="transactions.php" class="nav-link">Transactions</a>
    <?php endif; ?>
    <p class="px-5 mt-3 first:mt-0 text-[11px] uppercase tracking-wider text-white/40 mb-2">Admin</p>
    <?php if ($canManageUserRoles): ?>
    <a href="users.php" class="nav-link">System Users</a>
    <?php endif; ?>
    <a href="admin-settings.php" class="nav-link">Settings</a>
    <a href="change-password.php" class="nav-link">Change Password</a>
  </nav>
  <div class="p-4 border-t border-white/10 flex items-center gap-3">
    <div class="w-9 h-9 rounded-full bg-white/10 grid place-items-center text-sm font-semibold"><?php echo h($currentInitials); ?></div>
    <div class="text-sm leading-tight">
      <div class="font-medium"><?php echo h($currentName); ?></div>
      <div class="text-white/50 text-xs"><?php echo h(ucfirst($currentUser['role'] ?? 'user')); ?></div>
    </div>
    <a href="../process/logout.php" class="ml-auto text-white/60 hover:text-gold text-xs" data-confirm="Log out now?">Logout</a>
  </div>
</aside>
  <div class="md:ml-64 main-shift page-fade">
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
      <div class="px-6 py-4 flex flex-wrap items-center gap-4">
        <button data-sidebar-toggle class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-md border border-gray-200 text-navy">☰</button>
        <div class="min-w-0">
          <h1 class="text-lg font-semibold text-navy leading-tight whitespace-nowrap">Equipment Items</h1>
          <p class="text-xs text-gray-500">Manage campus equipment catalog</p>
        </div>
        <div class="ml-auto flex items-center gap-2 flex-wrap justify-end"><input class="input max-w-xs" placeholder="Search equipment..." data-search-target="#equipmentTable" /><button class="btn btn-primary btn-sm" data-modal-open="addEquipmentModal">+ Add Equipment</button></div>
      </div>
    </header>

    <main class="p-6 space-y-6">
      <section class="card">
        <div class="card-header gap-3 flex-wrap">
          <form method="GET" class="flex items-center gap-2">
            <select class="select max-w-[10rem]" name="category" onchange="this.form.submit()">
              <option value="">All categories</option>
              <?php foreach ($equipmentCategories as $category): ?>
                <option value="<?php echo h($category['category_id']); ?>" <?php echo $categoryFilter == $category['category_id'] ? 'selected' : ''; ?>><?php echo h($category['category_name']); ?></option>
              <?php endforeach; ?>
            </select>
            <select class="select max-w-[10rem]" name="status" onchange="this.form.submit()">
              <option value="">All status</option>
              <?php foreach (['Available', 'Borrowed', 'Maintenance'] as $statusOption): ?>
                <option <?php echo $statusFilter === $statusOption ? 'selected' : ''; ?>><?php echo h($statusOption); ?></option>
              <?php endforeach; ?>
            </select>
            <select class="select max-w-[10rem]" name="sort" onchange="this.form.submit()">
              <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Sort: Name</option>
              <option value="recent" <?php echo $sort === 'recent' ? 'selected' : ''; ?>>Recently added</option>
              <option value="qty" <?php echo $sort === 'qty' ? 'selected' : ''; ?>>Quantity</option>
            </select>
          </form>
        </div>
        <!-- PHP: if no equipment, show empty state -->   
        <div class="overflow-x-auto" id="equipmentTable">
          <table class="table">
            <thead><tr>
              <th>Item Code</th><th>Item Name</th><th>Category</th><th>Owner Qty</th><th>Owner Available</th>
              <th>Total Qty</th><th>Total Available</th>
              <th>Owner</th><th>Added By</th><th>Condition</th><th>Status</th><th class="text-right">Actions</th>
            </tr></thead>
            <tbody>
              <?php if (!$equipmentRows): ?>
                <tr><td colspan="12"><div class="empty"><div class="icon">-</div><p class="font-medium text-gray-700">No equipment yet</p><p class="text-sm">Click Add Equipment to add your first item.</p></div></td></tr>
              <?php else: foreach ($equipmentRows as $item):
                $itemCode = meta_value($item['description'], 'Item code') ?: $item['item_id'];
                $condition = meta_value($item['description'], 'Condition') ?: 'Good';
                $displayStatus = $item['status'] === 'inactive' ? 'Maintenance' : ((int) $item['available_quantity'] < (int) $item['total_quantity'] ? 'Borrowed' : 'Available');
                $description = plain_description($item['description']);
                $groupTotal = (int) ($item['group_total_quantity'] ?? $item['total_quantity']);
                $groupAvailable = (int) ($item['group_available_quantity'] ?? $item['available_quantity']);
              ?>
                <tr data-searchable>
                  <td><?php echo h($itemCode); ?></td>
                  <td><?php echo h($item['item_name']); ?></td>
                  <td><?php echo h($item['category_name'] ?? 'Uncategorized'); ?></td>
                  <td><?php echo h($item['total_quantity']); ?></td>
                  <td><?php echo h($item['available_quantity']); ?></td>
                  <td><?php echo h($groupTotal); ?></td>
                  <td><?php echo h($groupAvailable); ?></td>
                  <td><?php echo h($item['owner_name'] ?: 'Unassigned'); ?></td>
                  <td><?php echo h($item['added_by_name'] ?: 'Unknown'); ?></td>
                  <td><?php echo h($condition); ?></td>
                  <td><?php echo h($displayStatus); ?></td>
                  <td class="text-right space-x-2">
                    <button type="button" class="text-blue-600 hover:text-blue-800 text-xs font-medium"
                      data-modal-open="addEquipmentModal"
                      data-edit-equipment
                      data-id="<?php echo h($item['item_id']); ?>"
                      data-code="<?php echo h($itemCode); ?>"
                      data-name="<?php echo h($item['item_name']); ?>"
                      data-category-id="<?php echo h($item['category_id']); ?>"
                      data-unit-id="<?php echo h($item['unit_id']); ?>"
                      data-owner-official-id="<?php echo h($item['received_by_official_id']); ?>"
                      data-quantity="<?php echo h($item['total_quantity']); ?>"
                      data-condition="<?php echo h($condition); ?>"
                      data-status="<?php echo h($displayStatus); ?>"
                      data-description="<?php echo h($description); ?>">Edit</button>
                    <form method="POST" action="../process/equipment.php" class="inline" onsubmit="return confirm('Delete this equipment item?');">
                      <input type="hidden" name="action" value="delete" />
                      <input type="hidden" name="id" value="<?php echo h($item['item_id']); ?>" />
                      <button class="text-red-600 hover:text-red-800 text-xs font-medium">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>

    <!-- Add Equipment Modal -->
    <div class="modal" id="addEquipmentModal" role="dialog" aria-modal="true">
      <div class="modal-card">
        <div class="card-header">
          <h3 class="font-semibold text-navy">Add Equipment</h3>
          <button data-modal-close class="text-gray-400 hover:text-gray-700">✕</button>
        </div>
        <form method="POST" action="../process/equipment.php" class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
          <input type="hidden" name="id" value="" />
          <div><label class="label">Item code</label><input class="input" name="item_code" required /></div>
          <div><label class="label">Item name</label><input class="input" name="item_name" required /></div>
          <div><label class="label">Category</label>
            <select class="select" name="category_id" required>
              <option value="">Select category</option>
              <?php foreach ($categoryRows as $category): ?>
                <option value="<?php echo h($category['category_id']); ?>"><?php echo h($category['category_name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div><label class="label">Unit</label>
            <select class="select" name="unit_id" required>
              <option value="">Select unit</option>
              <?php foreach ($unitRows as $unit): ?>
                <option value="<?php echo h($unit['unit_id']); ?>"><?php echo h($unit['unit_name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="md:col-span-2 space-y-2">
            <div class="flex items-center justify-between gap-2">
              <label class="label mb-0">Owner allocations</label>
              <button type="button" class="btn btn-outline btn-sm" data-add-owner-allocation>+ Add Owner</button>
            </div>
            <div class="space-y-2" data-owner-allocations></div>
          </div>
          <div><label class="label">Condition</label>
            <select class="select" name="condition"><option>Good</option><option>Fair</option><option>Needs repair</option></select>
          </div>
          <div><label class="label">Status</label>
            <select class="select" name="status"><option>Available</option><option>Borrowed</option><option>Maintenance</option></select>
          </div>
          <div class="md:col-span-2"><label class="label">Description</label><textarea class="textarea" name="description" rows="3"></textarea></div>
          <div class="md:col-span-2 flex justify-end gap-2">
            <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
            <button type="submit" class="btn btn-primary">Save Equipment</button>
          </div>
        </form>
      </div>
    </div>

  </div>
  <script src="../js/shared.js"></script>
  <script>
    var equipmentOwnerOptions = <?php echo $ownerOptionsJson ?: '[]'; ?>;

    function createOwnerAllocationRow(container, allocation) {
      var row = document.createElement('div');
      row.className = 'grid grid-cols-1 md:grid-cols-[1fr_9rem_auto] gap-2 rounded-md border border-gray-200 bg-gray-50 p-3';

      var ownerWrap = document.createElement('div');
      var ownerLabel = document.createElement('label');
      ownerLabel.className = 'label';
      ownerLabel.textContent = 'Owner';
      var search = document.createElement('input');
      search.className = 'input mb-2';
      search.type = 'search';
      search.placeholder = 'Search owner by name or ID...';
      var select = document.createElement('select');
      select.className = 'select';
      select.name = 'owner_official_ids[]';
      select.required = true;
      var placeholder = document.createElement('option');
      placeholder.value = '';
      placeholder.textContent = 'Select official owner';
      select.appendChild(placeholder);
      equipmentOwnerOptions.forEach(function (owner) {
        var option = document.createElement('option');
        option.value = owner.official_id;
        option.textContent = owner.full_name + ' (' + owner.official_id + ')';
        select.appendChild(option);
      });
      ownerWrap.appendChild(ownerLabel);
      ownerWrap.appendChild(search);
      ownerWrap.appendChild(select);

      var qtyWrap = document.createElement('div');
      var qtyLabel = document.createElement('label');
      qtyLabel.className = 'label';
      qtyLabel.textContent = 'Quantity';
      var quantity = document.createElement('input');
      quantity.className = 'input';
      quantity.type = 'number';
      quantity.name = 'owner_quantities[]';
      quantity.min = '0';
      quantity.required = true;
      qtyWrap.appendChild(qtyLabel);
      qtyWrap.appendChild(quantity);

      var actionWrap = document.createElement('div');
      actionWrap.className = 'flex md:items-end';
      var removeButton = document.createElement('button');
      removeButton.type = 'button';
      removeButton.className = 'btn btn-outline btn-sm w-full md:w-auto';
      removeButton.textContent = 'Remove';
      actionWrap.appendChild(removeButton);

      var itemIdInput = document.createElement('input');
      itemIdInput.type = 'hidden';
      itemIdInput.name = 'allocation_item_ids[]';

      row.appendChild(ownerWrap);
      row.appendChild(qtyWrap);
      row.appendChild(actionWrap);
      row.appendChild(itemIdInput);
      container.appendChild(row);

      select.value = allocation && allocation.owner_official_id ? allocation.owner_official_id : '';
      quantity.value = allocation && allocation.quantity !== undefined ? allocation.quantity : '';
      itemIdInput.value = allocation && allocation.item_id ? allocation.item_id : '';

      var optionEntries = Array.prototype.slice.call(select.options).map(function (option) {
        return {
          option: option,
          text: (option.textContent + ' ' + option.value).toLowerCase(),
          isPlaceholder: option.value === ''
        };
      });

      search.addEventListener('input', function () {
        var term = search.value.trim().toLowerCase();
        optionEntries.forEach(function (entry) {
          entry.option.hidden = !entry.isPlaceholder && term !== '' && entry.text.indexOf(term) === -1;
        });

        if (select.value && select.selectedOptions[0] && select.selectedOptions[0].hidden) {
          select.value = '';
        }
      });

      removeButton.addEventListener('click', function () {
        if (container.children.length > 1) {
          row.remove();
          return;
        }

        select.value = '';
        quantity.value = '';
        itemIdInput.value = '';
        search.value = '';
        search.dispatchEvent(new Event('input'));
      });
    }

    function resetOwnerAllocations(form, allocations) {
      var container = form.querySelector('[data-owner-allocations]');
      container.innerHTML = '';
      (allocations && allocations.length ? allocations : [{}]).forEach(function (allocation) {
        createOwnerAllocationRow(container, allocation);
      });
    }

    var equipmentForm = document.querySelector('#addEquipmentModal form');
    resetOwnerAllocations(equipmentForm);
    document.querySelector('#addEquipmentModal [data-add-owner-allocation]').addEventListener('click', function () {
      createOwnerAllocationRow(equipmentForm.querySelector('[data-owner-allocations]'), {});
    });

    document.querySelectorAll('[data-edit-equipment]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var form = document.querySelector('#addEquipmentModal form');
        document.querySelector('#addEquipmentModal h3').textContent = 'Edit Equipment';
        form.elements.id.value = btn.dataset.id || '';
        form.elements.item_code.value = btn.dataset.code || '';
        form.elements.item_name.value = btn.dataset.name || '';
        form.elements.category_id.value = btn.dataset.categoryId || '';
        form.elements.unit_id.value = btn.dataset.unitId || '';
        resetOwnerAllocations(form, [{
          item_id: btn.dataset.id || '',
          owner_official_id: btn.dataset.ownerOfficialId || '',
          quantity: btn.dataset.quantity || 0
        }]);
        form.elements.condition.value = btn.dataset.condition || 'Good';
        form.elements.status.value = btn.dataset.status || 'Available';
        form.elements.description.value = btn.dataset.description || '';
      });
    });
    document.querySelectorAll('[data-modal-open="addEquipmentModal"]:not([data-edit-equipment])').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var form = document.querySelector('#addEquipmentModal form');
        document.querySelector('#addEquipmentModal h3').textContent = 'Add Equipment';
        form.reset();
        form.elements.id.value = '';
        resetOwnerAllocations(form);
      });
    });
  </script>
</body>
</html>
