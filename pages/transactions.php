<?php
require_once __DIR__ . '/../partials/page-data.php';
require_borrow_workflow_manager();

$transactionRows = all_rows(
    'SELECT t.*, i.item_name, CONCAT(m.first_name, " ", m.last_name) AS borrower_name
     FROM transactions t
     JOIN items i ON i.item_id = t.item_id
     JOIN master_list m ON m.student_id = t.student_id
     ORDER BY t.created_at DESC'
);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Transactions • MSU-MCEST CEMS</title>
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
    <a href="admin-dashboard.php" class="nav-link">Dashboard</a>
    <p class="px-5 mt-3 first:mt-0 text-[11px] uppercase tracking-wider text-white/40 mb-2">Catalog</p>
    <a href="equipment.php" class="nav-link">Equipment</a>
    <a href="materials.php" class="nav-link">Campus Materials</a>
    <p class="px-5 mt-3 first:mt-0 text-[11px] uppercase tracking-wider text-white/40 mb-2">Academics</p>
    <a href="courses.php" class="nav-link">Courses</a>
    <a href="students.php" class="nav-link">Students</a>
    <p class="px-5 mt-3 first:mt-0 text-[11px] uppercase tracking-wider text-white/40 mb-2">Transactions</p>
    <a href="requests.php" class="nav-link flex items-center">Borrow Requests<span class="ml-auto text-[10px] bg-gold text-navy-dark font-semibold px-2 py-0.5 rounded-full"><?php echo $pendingCount; ?></span></a>
    <a href="transactions.php" class="nav-link active">Transactions</a>
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
          <h1 class="text-lg font-semibold text-navy leading-tight whitespace-nowrap">Transactions</h1>
          <p class="text-xs text-gray-500">Borrow and return records</p>
        </div>
        <div class="ml-auto flex items-center gap-2 flex-wrap justify-end"><input class="input max-w-xs" placeholder="Search transactions..." data-search-target="#txnTable" /></div>
      </div>
    </header>

    <main class="p-6 space-y-6">
      <section class="card">
              <div data-tabs data-tabs-target="#txnTable" class="flex flex-wrap gap-1 border-b border-gray-200 px-4">
        <button class="tab is-active" data-tab="all">All</button>
        <button class="tab" data-tab="borrowed">Borrowed</button>
        <button class="tab" data-tab="returned">Returned</button>
        <button class="tab" data-tab="overdue">Overdue</button>
      </div>
        <div class="overflow-x-auto" id="txnTable">
          <table class="table">
            <thead><tr>
              <th>Txn #</th><th>Borrower</th><th>Item</th><th>Qty</th>
              <th>Date Borrowed</th><th>Due Date</th><th>Return Date</th><th>Status</th><th class="text-right">Actions</th>
            </tr></thead>
            <tbody>
              <?php if (!$transactionRows): ?>
                <tr><td colspan="9"><div class="empty"><div class="icon">-</div><p class="font-medium text-gray-700">No transactions yet</p><p class="text-sm">Approved borrow requests become transactions here.</p></div></td></tr>
              <?php else: foreach ($transactionRows as $transaction):
                $isReturned = $transaction['status'] === 'RETURNED';
                $isOverdue = !$isReturned && $transaction['expected_return_date'] < date('Y-m-d');
                $statusKey = $isReturned ? 'returned' : ($isOverdue ? 'overdue' : 'borrowed');
                $statusText = $isOverdue ? 'OVERDUE' : $transaction['status'];
              ?>
                <tr data-status="<?php echo h($statusKey); ?>" data-searchable>
                  <td>#<?php echo h($transaction['transaction_id']); ?></td>
                  <td><?php echo h($transaction['borrower_name']); ?></td>
                  <td><?php echo h($transaction['item_name']); ?></td>
                  <td><?php echo h($transaction['quantity_borrowed']); ?></td>
                  <td><?php echo h($transaction['borrow_date']); ?></td>
                  <td><?php echo h($transaction['expected_return_date']); ?></td>
                  <td><?php echo $isReturned ? h(substr($transaction['updated_at'], 0, 10)) : '-'; ?></td>
                  <td><?php echo badge($statusText); ?></td>
                  <td class="text-right">
                    <?php if (!$isReturned): ?>
                      <form method="POST" action="../process/transactions.php" class="inline" onsubmit="return confirm('Mark this item as returned?');">
                        <input type="hidden" name="transaction_id" value="<?php echo h($transaction['transaction_id']); ?>" />
                        <button name="action" value="return" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Mark Returned</button>
                      </form>
                    <?php else: ?>
                      <span class="text-xs text-gray-400">Completed</span>
                    <?php endif; ?>
                  </td>
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
