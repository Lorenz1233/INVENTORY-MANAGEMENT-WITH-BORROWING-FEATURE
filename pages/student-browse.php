<?php
require_once __DIR__ . '/../partials/page-data.php';
require_borrower();

$categoryFilter = clean($_GET['category'] ?? '');
$where = [equipment_condition('i', 'c'), 'i.status = "active"', 'i.available_quantity > 0'];
$params = [];

if ($categoryFilter !== '') {
    $where[] = 'i.category_id = ?';
    $params[] = $categoryFilter;
}

$equipmentRows = all_rows(
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
     ORDER BY i.item_name',
    $params
);
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
              $ownerName = trim((string) ($item['owner_name'] ?? ''));
              $appointmentText = str_replace('||', "\n", (string) ($item['appointment_text'] ?? ''));
            ?>
              <div class="border border-gray-200 rounded-md p-4 bg-white" data-searchable>
                <p class="font-semibold text-navy"><?php echo h($item['item_name']); ?></p>
                <p class="text-xs text-gray-500 mt-1"><?php echo h($item['category_name'] ?? 'Uncategorized'); ?> - <?php echo h($item['unit_name'] ?? 'pcs'); ?></p>
                <p class="text-xs text-gray-500 mt-1">Owner: <strong class="text-navy"><?php echo h($ownerName ?: 'Unassigned'); ?></strong></p>
                <p class="text-sm text-gray-600 mt-3 min-h-[2.5rem]"><?php echo h(plain_description($item['description']) ?: 'No description.'); ?></p>
                <div class="mt-3 rounded-md border border-gray-100 bg-gray-50 p-2 text-xs text-gray-600 min-h-[3.25rem]">
                  <p class="font-semibold text-navy">Appointments</p>
                  <?php if ($appointmentText !== ''): ?>
                    <?php foreach (explode("\n", $appointmentText) as $appointment): ?>
                      <p><?php echo h($appointment); ?></p>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <p class="text-gray-400">No accepted appointments yet.</p>
                  <?php endif; ?>
                </div>
                <div class="mt-4 flex items-center justify-between">
                  <span class="text-xs text-gray-500">Available: <strong class="text-navy"><?php echo h($item['available_quantity']); ?></strong></span>
                  <?php if ($ownerName !== ''): ?>
                    <button type="button" class="btn btn-primary btn-sm" data-modal-open="borrowModal" data-borrow-item data-id="<?php echo h($item['item_id']); ?>" data-name="<?php echo h($item['item_name']); ?>" data-owner="<?php echo h($ownerName); ?>" data-appointments="<?php echo h($appointmentText); ?>" data-available="<?php echo h($item['available_quantity']); ?>">Request</button>
                  <?php else: ?>
                    <button type="button" class="btn btn-outline btn-sm" disabled>Unassigned</button>
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
            <input class="input" name="owner_name" readonly value="" /></div>
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
    document.querySelectorAll('[data-borrow-item]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var form = document.querySelector('#borrowModal form');
        form.elements.equipment_id.value = btn.dataset.id || '';
        form.elements.equipment_name.value = btn.dataset.name || '';
        form.elements.owner_name.value = btn.dataset.owner || '';
        document.querySelector('[data-appointment-preview]').textContent = btn.dataset.appointments || 'No accepted appointments yet.';
        form.elements.quantity.max = btn.dataset.available || '';
        form.elements.quantity.value = 1;
        form.elements.borrow_date.value = new Date().toISOString().slice(0, 10);
        form.elements.days_to_borrow.value = 1;
      });
    });
  </script>
</body>
</html>
