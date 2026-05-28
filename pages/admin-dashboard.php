<?php
require_once __DIR__ . '/../partials/page-data.php';
require_admin();

$equipmentWhere = equipment_condition('i', 'c');
$totalEquipment = (int) one_value("SELECT COUNT(*) FROM items i LEFT JOIN category c ON c.category_id = i.category_id WHERE {$equipmentWhere}");
$pendingRequests = (int) one_value('SELECT COUNT(*) FROM borrow_request WHERE status = "PENDING"');
$activeBorrowings = (int) one_value('SELECT COUNT(*) FROM transactions WHERE status IN ("PENDING", "ONGOING")');
$returnedItems = (int) one_value('SELECT COUNT(*) FROM transactions WHERE status = "RETURNED"');
$totalStudents = (int) one_value('SELECT COUNT(*) FROM master_list');
$systemUsers = (int) one_value('SELECT COUNT(*) FROM users WHERE role IN ("admin", "faculty")');
$recentRequests = all_rows(
    'SELECT br.request_id, br.request_date, br.status, br.created_at, i.item_name,
            CONCAT(m.first_name, " ", m.last_name) AS borrower_name
     FROM borrow_request br
     JOIN items i ON i.item_id = br.item_id
     JOIN master_list m ON m.student_id = br.student_id
     ORDER BY br.created_at DESC
     LIMIT 5'
);
$recentTransactions = all_rows(
    'SELECT t.transaction_id, t.status, t.created_at, i.item_name,
            CONCAT(m.first_name, " ", m.last_name) AS borrower_name
     FROM transactions t
     JOIN items i ON i.item_id = t.item_id
     JOIN master_list m ON m.student_id = t.student_id
     ORDER BY t.created_at DESC
     LIMIT 5'
);
$quickActivity = [];
foreach ($recentRequests as $request) {
    $quickActivity[] = [
        'date' => $request['created_at'],
        'text' => 'Request #' . $request['request_id'] . ' for ' . $request['item_name'] . ' is ' . ucfirst(strtolower($request['status'])),
    ];
}
foreach ($recentTransactions as $transaction) {
    $quickActivity[] = [
        'date' => $transaction['created_at'],
        'text' => 'Transaction #' . $transaction['transaction_id'] . ' for ' . $transaction['item_name'] . ' is ' . ucfirst(strtolower($transaction['status'])),
    ];
}
usort($quickActivity, function ($a, $b) {
    return strcmp($b['date'], $a['date']);
});
$quickActivity = array_slice($quickActivity, 0, 6);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard • MSU-MCEST CEMS</title>
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
      <div class="text-xs text-white/60">Equipment Mgmt</div>
    </div>
  </div>
  <nav class="flex-1 py-4 text-sm">
    <p class="px-5 mt-3 first:mt-0 text-[11px] uppercase tracking-wider text-white/40 mb-2">Overview</p>
    <a href="admin-dashboard.php" class="nav-link active">Dashboard</a>
    <p class="px-5 mt-3 first:mt-0 text-[11px] uppercase tracking-wider text-white/40 mb-2">Catalog</p>
    <a href="equipment.php" class="nav-link">Equipment</a>
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
          <h1 class="text-lg font-semibold text-navy leading-tight whitespace-nowrap">Admin Dashboard</h1>
          <p class="text-xs text-gray-500">Overview of campus equipment activity</p>
        </div>
        <div class="ml-auto flex items-center gap-2 flex-wrap justify-end"></div>
      </div>
    </header>

    <main class="p-6 space-y-6">
      <section class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <div class="card">
        <div class="card-body">
          <p class="text-xs uppercase tracking-wider text-gray-500">Total Equipment</p>
          <p class="mt-2 text-2xl font-semibold text-navy"><?php echo $totalEquipment; ?></p>
          <p class="text-xs text-gray-400 mt-1">Items in catalog</p>
        </div>
      </div>
        <div class="card">
        <div class="card-body">
          <p class="text-xs uppercase tracking-wider text-gray-500">Pending Requests</p>
          <p class="mt-2 text-2xl font-semibold text-navy"><?php echo $pendingRequests; ?></p>
          <p class="text-xs text-gray-400 mt-1">Awaiting approval</p>
        </div>
      </div>
        <div class="card">
        <div class="card-body">
          <p class="text-xs uppercase tracking-wider text-gray-500">Active Borrowings</p>
          <p class="mt-2 text-2xl font-semibold text-navy"><?php echo $activeBorrowings; ?></p>
          <p class="text-xs text-gray-400 mt-1">Out with borrowers</p>
        </div>
      </div>
        <div class="card">
        <div class="card-body">
          <p class="text-xs uppercase tracking-wider text-gray-500">Returned Items</p>
          <p class="mt-2 text-2xl font-semibold text-navy"><?php echo $returnedItems; ?></p>
          <p class="text-xs text-gray-400 mt-1">Completed transactions</p>
        </div>
      </div>
        <div class="card">
        <div class="card-body">
          <p class="text-xs uppercase tracking-wider text-gray-500">Total Students</p>
          <p class="mt-2 text-2xl font-semibold text-navy"><?php echo $totalStudents; ?></p>
          <p class="text-xs text-gray-400 mt-1">Registered students</p>
        </div>
      </div>
      <?php if ($canManageUserRoles): ?>
        <div class="card">
        <div class="card-body">
          <p class="text-xs uppercase tracking-wider text-gray-500">System Users</p>
          <p class="mt-2 text-2xl font-semibold text-navy"><?php echo $systemUsers; ?></p>
          <p class="text-xs text-gray-400 mt-1">All staff/admin accounts</p>
        </div>
      </div>
      <?php endif; ?>
      </section>

      <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card">
          <div class="card-header">
            <h2 class="font-semibold text-navy">Recent Borrow Requests</h2>
            <?php if ($canManageBorrowWorkflow): ?>
            <a href="requests.php" class="text-xs text-navy hover:text-gold-dark font-semibold">View all →</a>
            <?php endif; ?>
          </div>
          <div class="overflow-x-auto">
            <table class="table">
              <thead><tr><th>Request ID</th><th>Student</th><th>Item</th><th>Date</th><th>Status</th></tr></thead>
              <tbody>
                <?php if (!$recentRequests): ?>
                <tr><td colspan="5"><div class="empty"><div class="icon">-</div><p class="font-medium text-gray-700">No recent requests</p><p class="text-sm">Requests will appear here once submitted.</p></div></td></tr>
                <?php else: foreach ($recentRequests as $request): ?>
                <tr>
                  <td>#<?php echo h($request['request_id']); ?></td>
                  <td><?php echo h($request['borrower_name']); ?></td>
                  <td><?php echo h($request['item_name']); ?></td>
                  <td><?php echo h($request['request_date']); ?></td>
                  <td><?php echo badge($request['status']); ?></td>
                </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h2 class="font-semibold text-navy">Recent Transactions</h2>
            <?php if ($canManageBorrowWorkflow): ?>
            <a href="transactions.php" class="text-xs text-navy hover:text-gold-dark font-semibold">View all →</a>
            <?php endif; ?>
          </div>
          <div class="overflow-x-auto">
            <table class="table">
              <thead><tr><th>Txn ID</th><th>Borrower</th><th>Item</th><th>Status</th></tr></thead>
              <tbody>
                <?php if (!$recentTransactions): ?>
                <tr><td colspan="4"><div class="empty"><div class="icon">-</div><p class="font-medium text-gray-700">No recent transactions</p><p class="text-sm">Transactions will be logged here.</p></div></td></tr>
                <?php else: foreach ($recentTransactions as $transaction): ?>
                <tr>
                  <td>#<?php echo h($transaction['transaction_id']); ?></td>
                  <td><?php echo h($transaction['borrower_name']); ?></td>
                  <td><?php echo h($transaction['item_name']); ?></td>
                  <td><?php echo badge($transaction['status']); ?></td>
                </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <section class="card">
        <div class="card-header"><h2 class="font-semibold text-navy">Quick Activity</h2></div>
        <div class="card-body text-sm text-gray-600">
          <?php if (!$quickActivity): ?>
            <p class="text-gray-400">No activity yet.</p>
          <?php else: ?>
            <ul class="space-y-2">
              <?php foreach ($quickActivity as $activity): ?>
                <li class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 last:border-0 pb-2 last:pb-0">
                  <span><?php echo h($activity['text']); ?></span>
                  <span class="text-xs text-gray-400"><?php echo h(substr($activity['date'], 0, 16)); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </section>
    </main>

  </div>
  <script src="../js/shared.js"></script>
</body>
</html>
