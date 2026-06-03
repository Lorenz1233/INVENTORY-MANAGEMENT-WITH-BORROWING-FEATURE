<?php
require_once __DIR__ . '/../partials/page-data.php';
require_borrower();

$categoryFilter = clean($_GET['category'] ?? '');
$where = [equipment_condition('i', 'c'), 'i.status = "active"'];
$params = [];

if ($categoryFilter !== '') {
    $where[] = 'i.category_id = ?';
    $params[] = $categoryFilter;
}

$equipmentOwnerRows = all_rows(
    'SELECT i.*, c.category_name, u.unit_name,
            CONCAT(o.first_name, " ", o.last_name) AS owner_name,
            ap.appointment_text
     FROM items i
     LEFT JOIN category c ON c.category_id = i.category_id
     LEFT JOIN unit u ON u.unit_id = i.unit_id
     LEFT JOIN officials_masterlist o ON o.official_id = i.received_by_official_id
     LEFT JOIN (
        SELECT item_id,
               GROUP_CONCAT(
                    CONCAT(DATE_FORMAT(appointment_date, "%Y-%m-%d"), " to ", DATE_FORMAT(appointment_end_date, "%Y-%m-%d"))
                    ORDER BY appointment_date
                    SEPARATOR "||"
               ) AS appointment_text
        FROM borrow_appointments
        WHERE status = "SCHEDULED" AND appointment_end_date >= CURDATE()
        GROUP BY item_id
     ) ap ON ap.item_id = i.item_id
     WHERE ' . implode(' AND ', $where) . '
     ORDER BY i.item_name, o.last_name, o.first_name',
    $params
);
$equipmentRows = [];
foreach ($equipmentOwnerRows as $row) {
    $groupKey = strtolower((string) $row['item_name']) . '|' . (string) $row['category_id'];

    if (!isset($equipmentRows[$groupKey])) {
        $equipmentRows[$groupKey] = [
            'item_name' => $row['item_name'],
            'category_id' => $row['category_id'],
            'category_name' => $row['category_name'],
            'unit_name' => $row['unit_name'],
            'description' => $row['description'],
            'group_total_quantity' => 0,
            'group_available_quantity' => 0,
            'owner_options' => [],
        ];
    }

    $equipmentRows[$groupKey]['group_total_quantity'] += (int) $row['total_quantity'];
    $equipmentRows[$groupKey]['group_available_quantity'] += (int) $row['available_quantity'];

    $ownerName = trim((string) ($row['owner_name'] ?? ''));
    if ($ownerName !== '' && clean($row['received_by_official_id'] ?? '') !== '') {
        $equipmentRows[$groupKey]['owner_options'][] = [
            'item_id' => (int) $row['item_id'],
            'owner_name' => $ownerName,
            'owner_official_id' => $row['received_by_official_id'],
            'total_quantity' => (int) $row['total_quantity'],
            'available_quantity' => (int) $row['available_quantity'],
            'appointment_text' => str_replace('||', "\n", (string) ($row['appointment_text'] ?? '')),
        ];
    }
}
$equipmentRows = array_values(array_filter($equipmentRows, function ($group) {
    return (int) $group['group_available_quantity'] > 0 && !empty($group['owner_options']);
}));
$equipmentCategories = all_rows(
    'SELECT DISTINCT c.category_id, c.category_name
     FROM items i
     JOIN category c ON c.category_id = i.category_id
     WHERE ' . equipment_condition('i', 'c') . ' AND i.status = "active" AND i.available_quantity > 0
     ORDER BY c.category_name'
);
$portalLabel = ($currentUser['role'] ?? '') === 'faculty' ? 'Faculty Portal' : 'Student Portal';
$profileLine = ($currentUser['role'] ?? '') === 'faculty'
    ? ($currentUser['position_code'] ?? 'Faculty')
    : trim(($currentUser['course_code'] ?? 'Student') . (($currentUser['year_level'] ?? '') ? ' - ' . $currentUser['year_level'] : ''));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Browse Equipment • MSU-MCEST</title>
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
<!-- PHP: session check (student only) -->
<aside class="sidebar hidden md:flex md:flex-col w-64 bg-navy text-white fixed inset-y-0 left-0 z-30">
  <div class="px-5 py-5 border-b border-white/10 flex items-center gap-3">
    <img class="brand-logo" src="../assets/images/logo.png" width="44" height="44" alt="MSU-MCEST logo" />
    <div>
      <div class="text-sm font-semibold leading-tight">MSU-MCEST</div>
      <div class="text-xs text-white/60"><?php echo h($portalLabel); ?></div>
    </div>
  </div>
  <nav class="flex-1 py-4 text-sm">
    <p class="px-5 mt-3 first:mt-0 text-[11px] uppercase tracking-wider text-white/40 mb-2">Overview</p>
    <a href="student-dashboard.php" class="nav-link">Dashboard</a>
    <p class="px-5 mt-3 first:mt-0 text-[11px] uppercase tracking-wider text-white/40 mb-2">Borrow</p>
    <a href="student-browse.php" class="nav-link active">Browse Equipment</a>
    <a href="student-requests.php" class="nav-link">My Requests</a>
    <a href="student-change-password.php" class="nav-link">Change Password</a>
  </nav>
  <div class="p-4 border-t border-white/10 flex items-center gap-3">
    <div class="w-9 h-9 rounded-full bg-white/10 grid place-items-center text-sm font-semibold"><?php echo h($currentInitials); ?></div>
    <div class="text-sm leading-tight">
      <div class="font-medium"><?php echo h($currentName); ?></div>
      <div class="text-white/50 text-xs"><?php echo h($profileLine); ?></div>
    </div>
    <a href="../process/logout.php" class="ml-auto text-white/60 hover:text-gold text-xs" data-confirm="Log out now?">Logout</a>
  </div>
</aside>
  <div class="md:ml-64 main-shift page-fade">
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
      <div class="px-6 py-4 flex flex-wrap items-center gap-4">
        <button data-sidebar-toggle class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-md border border-gray-200 text-navy">☰</button>
        <div class="min-w-0">
          <h1 class="text-lg font-semibold text-navy leading-tight whitespace-nowrap">Browse Equipment</h1>
          <p class="text-xs text-gray-500">Find and request available campus equipment</p>
        </div>
        <div class="ml-auto flex items-center gap-2 flex-wrap justify-end"><input class="input max-w-xs" placeholder="Search equipment..." data-search-target="#browseGrid" /></div>
      </div>
    </header>

    <main class="p-6 space-y-6">
      <section class="card">
        <div class="card-header flex-wrap gap-2">
          <div class="flex flex-wrap gap-2">
            <a class="btn <?php echo $categoryFilter === '' ? 'btn-primary' : 'btn-outline'; ?> btn-sm" href="student-browse.php">All</a>
            <?php foreach ($equipmentCategories as $category): ?><a class="btn <?php echo $categoryFilter == $category['category_id'] ? 'btn-primary' : 'btn-outline'; ?> btn-sm" href="student-browse.php?category=<?php echo h($category['category_id']); ?>"><?php echo h($category['category_name']); ?></a><?php endforeach; ?>
          </div>
        </div>
        <div class="card-body">
          <div id="browseGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <?php if (!$equipmentRows): ?>
              <div class="empty col-span-full" id="browseEmpty">
                <div class="icon">-</div>
                <p class="font-medium text-gray-700">No equipment available</p>
                <p class="text-sm">Check back later or contact the equipment office.</p>
              </div>
            <?php else: foreach ($equipmentRows as $item):
              $groupTotal = (int) $item['group_total_quantity'];
              $groupAvailable = (int) $item['group_available_quantity'];
              $availableOwners = array_values(array_filter($item['owner_options'], function ($owner) {
                  return (int) $owner['available_quantity'] > 0;
              }));
              $ownerSummary = implode(', ', array_map(function ($owner) {
                  return $owner['owner_name'] . ' (' . $owner['available_quantity'] . '/' . $owner['total_quantity'] . ')';
              }, $item['owner_options']));
              $ownerOptionsJson = json_encode($item['owner_options'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
            ?>
              <div class="border border-gray-200 rounded-md p-4 bg-white" data-searchable>
                <p class="font-semibold text-navy"><?php echo h($item['item_name']); ?></p>
                <p class="text-xs text-gray-500 mt-1"><?php echo h($item['category_name'] ?? 'Uncategorized'); ?> - <?php echo h($item['unit_name'] ?? 'pcs'); ?></p>
                <p class="text-xs text-gray-500 mt-1">Catalog total: <strong class="text-navy"><?php echo h($groupTotal); ?></strong> total, <strong class="text-navy"><?php echo h($groupAvailable); ?></strong> available</p>
                <p class="text-xs text-gray-500 mt-1">Owners: <strong class="text-navy"><?php echo h($ownerSummary); ?></strong></p>
                <p class="text-sm text-gray-600 mt-3 min-h-[2.5rem]"><?php echo h(plain_description($item['description']) ?: 'No description.'); ?></p>
                <div class="mt-3 rounded-md border border-gray-100 bg-gray-50 p-2 text-xs text-gray-600 min-h-[3.25rem]">
                  <p class="font-semibold text-navy">Owner Availability</p>
                  <?php foreach ($item['owner_options'] as $owner): ?>
                    <p><?php echo h($owner['owner_name']); ?>: <?php echo h($owner['available_quantity']); ?> available of <?php echo h($owner['total_quantity']); ?></p>
                  <?php endforeach; ?>
                </div>
                <div class="mt-4 flex items-center justify-between">
                  <span class="text-xs text-gray-500">Available: <strong class="text-navy"><?php echo h($groupAvailable); ?></strong></span>
                  <?php if ($availableOwners): ?>
                    <button type="button" class="btn btn-primary btn-sm"
                      data-modal-open="borrowModal"
                      data-borrow-item
                      data-name="<?php echo h($item['item_name']); ?>"
                      data-owner-options="<?php echo h($ownerOptionsJson); ?>">Request</button>
                  <?php else: ?>
                    <button type="button" class="btn btn-outline btn-sm" disabled>Unavailable</button>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; endif; ?>          </div>
        </div>
      </section>
    </main>

    <!-- Borrow request modal -->
    <div class="modal" id="borrowModal">
      <div class="modal-card">
        <div class="card-header"><h3 class="font-semibold text-navy">Request to Borrow</h3>
          <button data-modal-close class="text-gray-400 hover:text-gray-700">✕</button></div>
        <form method="POST" action="../process/student_browse.php" class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
          <input type="hidden" name="equipment_id" value="" />
          <div class="md:col-span-2"><label class="label">Item</label>
            <input class="input" name="equipment_name" readonly value="" /></div>
          <div class="md:col-span-2"><label class="label">Owner</label>
            <select class="select" name="owner_item_id" data-owner-choice required></select></div>
          <div class="md:col-span-2 rounded-md border border-gray-100 bg-gray-50 p-3 text-sm text-gray-600">
            <p class="font-semibold text-navy text-xs uppercase tracking-wider">Existing appointments</p>
            <div class="mt-1 whitespace-pre-line" data-appointment-preview>No accepted appointments yet.</div>
          </div>
          <div><label class="label">Quantity</label><input class="input" type="number" name="quantity" min="1" required /></div>
          <div><label class="label">Borrow date</label><input class="input" type="date" name="borrow_date" required /></div>
          <div><label class="label">Days to borrow</label><input class="input" type="number" name="days_to_borrow" min="1" required /></div>
          <div class="md:col-span-2"><label class="label">Purpose</label><textarea class="textarea" name="purpose" rows="3" required></textarea></div>
          <div class="md:col-span-2 flex justify-end gap-2">
            <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
            <button type="submit" class="btn btn-primary">Submit Request</button>
          </div>
        </form>
      </div>
    </div>

  </div>
  <script src="../js/shared.js"></script>
  <script>
    function syncOwnerChoice(form) {
      var ownerSelect = form.elements.owner_item_id;
      var selectedOption = ownerSelect.options[ownerSelect.selectedIndex];
      if (!selectedOption) {
        form.elements.equipment_id.value = '';
        form.elements.quantity.max = '';
        document.querySelector('[data-appointment-preview]').textContent = 'No accepted appointments yet.';
        return;
      }

      var available = selectedOption.dataset.available || '';
      form.elements.equipment_id.value = selectedOption.value || '';
      form.elements.quantity.max = available;
      form.elements.quantity.value = available && Number(available) > 0 ? 1 : '';
      document.querySelector('[data-appointment-preview]').textContent = selectedOption.dataset.appointments || 'No accepted appointments yet.';
    }

    document.querySelectorAll('[data-borrow-item]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var form = document.querySelector('#borrowModal form');
        var ownerSelect = form.elements.owner_item_id;
        var ownerOptions = [];

        try {
          ownerOptions = JSON.parse(btn.dataset.ownerOptions || '[]');
        } catch (error) {
          ownerOptions = [];
        }

        ownerSelect.innerHTML = '';
        ownerOptions.forEach(function (owner) {
          var option = document.createElement('option');
          option.value = owner.item_id || '';
          option.textContent = owner.owner_name + ' - ' + owner.available_quantity + ' available of ' + owner.total_quantity;
          option.dataset.available = owner.available_quantity || 0;
          option.dataset.appointments = owner.appointment_text || '';
          option.disabled = Number(owner.available_quantity || 0) <= 0;
          ownerSelect.appendChild(option);
        });

        var firstAvailable = Array.prototype.find.call(ownerSelect.options, function (option) {
          return !option.disabled;
        });
        if (firstAvailable) {
          ownerSelect.value = firstAvailable.value;
        }

        form.elements.equipment_name.value = btn.dataset.name || '';
        form.elements.borrow_date.value = new Date().toISOString().slice(0, 10);
        form.elements.days_to_borrow.value = 1;
        syncOwnerChoice(form);
      });
    });

    document.querySelector('[data-owner-choice]').addEventListener('change', function () {
      syncOwnerChoice(document.querySelector('#borrowModal form'));
    });
  </script>
</body>
</html>
