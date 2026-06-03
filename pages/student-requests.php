<?php
require_once __DIR__ . '/../partials/page-data.php';
require_borrower();

[$borrowerWhere, $borrowerParams] = current_borrower_filter_sql('br');
$myRequestRows = all_rows(
    'SELECT br.*,
            COALESCE(NULLIF(br.purpose, ""), NULLIF(br.remarks, "")) AS request_purpose,
            DATE_ADD(br.request_date, INTERVAL br.days_to_borrow DAY) AS due_date,
            i.item_name,
            CONCAT(o.first_name, " ", o.last_name) AS owner_name,
            t.status AS transaction_status
     FROM borrow_request br
     JOIN items i ON i.item_id = br.item_id
     LEFT JOIN officials_masterlist o ON o.official_id = COALESCE(br.owner_official_id, i.received_by_official_id)
     LEFT JOIN transactions t ON t.request_id = br.request_id
     WHERE ' . $borrowerWhere . '
     ORDER BY br.created_at DESC',
    $borrowerParams
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
  <title>My Requests • MSU-MCEST</title>
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
    <a href="student-browse.php" class="nav-link">Browse Equipment</a>
    <a href="student-requests.php" class="nav-link active">My Requests</a>
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
          <h1 class="text-lg font-semibold text-navy leading-tight whitespace-nowrap">My Requests</h1>
          <p class="text-xs text-gray-500">Track your borrow requests</p>
        </div>
        <div class="ml-auto flex items-center gap-2 flex-wrap justify-end"><input class="input max-w-xs" placeholder="Search my requests..." data-search-target="#myRequestsTable" /></div>
      </div>
    </header>

    <main class="p-6 space-y-6">
      <section class="card">
              <div data-tabs data-tabs-target="#myRequestsTable" class="flex flex-wrap gap-1 border-b border-gray-200 px-4">
        <button class="tab is-active" data-tab="all">All</button>
        <button class="tab" data-tab="pending">Pending</button>
        <button class="tab" data-tab="approved">Approved</button>
        <button class="tab" data-tab="rejected">Rejected</button>
        <button class="tab" data-tab="returned">Returned</button>
      </div>
        <div class="overflow-x-auto" id="myRequestsTable">
          <table class="table">
            <thead><tr>
              <th>Request #</th><th>Item</th><th>Qty</th><th>Requested</th>
              <th>Owner</th><th>Borrow Date</th><th>Due Date</th><th>Purpose</th><th>Status</th><th class="text-right">Actions</th>
            </tr></thead>
            <tbody>
              <?php if (!$myRequestRows): ?>
                <tr><td colspan="10"><div class="empty"><div class="icon">-</div><p class="font-medium text-gray-700">No requests yet</p><p class="text-sm">Browse equipment and submit a request to get started.</p></div></td></tr>
              <?php else: foreach ($myRequestRows as $request):
                $displayStatus = $request['transaction_status'] === 'RETURNED' ? 'RETURNED' : $request['status'];
                $statusKey = strtolower($displayStatus);
              ?>
                <tr data-status="<?php echo h($statusKey); ?>" data-searchable>
                  <td>#<?php echo h($request['request_id']); ?></td>
                  <td><?php echo h($request['item_name']); ?></td>
                  <td><?php echo h($request['quantity_requested']); ?></td>
                  <td><?php echo h(substr($request['created_at'], 0, 10)); ?></td>
                  <td><?php echo h($request['owner_name'] ?: 'Unassigned'); ?></td>
                  <td><?php echo h($request['request_date']); ?></td>
                  <td><?php echo h($request['due_date']); ?></td>
                  <td><?php echo h($request['request_purpose'] ?: '-'); ?></td>
                  <td><?php echo badge($displayStatus); ?></td>
                  <td class="text-right"><span class="text-xs text-gray-400">-</span></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>

  </div>
  <script src="../js/shared.js"></script>
</body>
</html>
