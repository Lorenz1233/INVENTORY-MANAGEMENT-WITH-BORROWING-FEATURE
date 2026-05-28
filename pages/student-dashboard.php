<?php
require_once __DIR__ . '/../partials/page-data.php';
require_borrower();

$studentId = (int) ($currentUser['student_id'] ?? $_SESSION['student_id'] ?? 0);
$activeRequests = (int) one_value('SELECT COUNT(*) FROM borrow_request WHERE student_id = ? AND status = "PENDING"', [$studentId]);
$approvedRequests = (int) one_value('SELECT COUNT(*) FROM borrow_request WHERE student_id = ? AND status = "APPROVED"', [$studentId]);
$borrowedItems = (int) one_value('SELECT COUNT(*) FROM transactions WHERE student_id = ? AND status IN ("PENDING", "ONGOING")', [$studentId]);
$returnedItems = (int) one_value('SELECT COUNT(*) FROM transactions WHERE student_id = ? AND status = "RETURNED"', [$studentId]);
$recentRequests = all_rows(
    'SELECT br.*, i.item_name, t.status AS transaction_status
     FROM borrow_request br
     JOIN items i ON i.item_id = br.item_id
     LEFT JOIN transactions t ON t.request_id = br.request_id
     WHERE br.student_id = ?
     ORDER BY br.created_at DESC
     LIMIT 5',
    [$studentId]
);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Student Dashboard • MSU-MCEST CEMS</title>
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
      <div class="text-xs text-white/60">Student Portal</div>
    </div>
  </div>
  <nav class="flex-1 py-4 text-sm">
    <p class="px-5 mt-3 first:mt-0 text-[11px] uppercase tracking-wider text-white/40 mb-2">Overview</p>
    <a href="student-dashboard.php" class="nav-link active">Dashboard</a>
    <p class="px-5 mt-3 first:mt-0 text-[11px] uppercase tracking-wider text-white/40 mb-2">Borrow</p>
    <a href="student-browse.php" class="nav-link">Browse Equipment</a>
    <a href="student-requests.php" class="nav-link">My Requests</a>
    <a href="student-change-password.php" class="nav-link">Change Password</a>
  </nav>
  <div class="p-4 border-t border-white/10 flex items-center gap-3">
    <div class="w-9 h-9 rounded-full bg-white/10 grid place-items-center text-sm font-semibold"><?php echo h($currentInitials); ?></div>
    <div class="text-sm leading-tight">
      <div class="font-medium"><?php echo h($currentName); ?></div>
      <div class="text-white/50 text-xs"><?php echo h(trim(($currentUser['course_code'] ?? 'Student') . (($currentUser['year_level'] ?? '') ? ' - ' . $currentUser['year_level'] : ''))); ?></div>
    </div>
    <a href="../process/logout.php" class="ml-auto text-white/60 hover:text-gold text-xs" data-confirm="Log out now?">Logout</a>
  </div>
</aside>
  <div class="md:ml-64 main-shift page-fade">
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
      <div class="px-6 py-4 flex flex-wrap items-center gap-4">
        <button data-sidebar-toggle class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-md border border-gray-200 text-navy">☰</button>
        <div class="min-w-0">
          <h1 class="text-lg font-semibold text-navy leading-tight whitespace-nowrap">Student Dashboard</h1>
          <p class="text-xs text-gray-500">Your borrowing overview</p>
        </div>
        <div class="ml-auto flex items-center gap-2 flex-wrap justify-end"></div>
      </div>
    </header>

    <main class="p-6 space-y-6">
      <section class="card bg-navy text-white border-navy">
        <div class="card-body flex flex-wrap items-center justify-between gap-4">
          <div>
            <p class="text-white/60 text-xs uppercase tracking-wider">Welcome</p>
            <h2 class="text-xl font-semibold mt-1">Hello, <?php echo h($currentUser['first_name'] ?? 'Student'); ?></h2>
            <p class="text-white/70 text-sm mt-1">Browse available equipment and track your borrow requests.</p>
          </div>
          <a href="student-browse.php" class="btn btn-gold">Browse Equipment</a>
        </div>
      </section>

      <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card">
        <div class="card-body">
          <p class="text-xs uppercase tracking-wider text-gray-500">Active Requests</p>
          <p class="mt-2 text-2xl font-semibold text-navy"><?php echo $activeRequests; ?></p>
          <p class="text-xs text-gray-400 mt-1">Currently pending</p>
        </div>
      </div>
        <div class="card">
        <div class="card-body">
          <p class="text-xs uppercase tracking-wider text-gray-500">Approved Requests</p>
          <p class="mt-2 text-2xl font-semibold text-navy"><?php echo $approvedRequests; ?></p>
          <p class="text-xs text-gray-400 mt-1">Ready to pick up</p>
        </div>
      </div>
        <div class="card">
        <div class="card-body">
          <p class="text-xs uppercase tracking-wider text-gray-500">Borrowed Items</p>
          <p class="mt-2 text-2xl font-semibold text-navy"><?php echo $borrowedItems; ?></p>
          <p class="text-xs text-gray-400 mt-1">Currently with you</p>
        </div>
      </div>
        <div class="card">
        <div class="card-body">
          <p class="text-xs uppercase tracking-wider text-gray-500">Returned Items</p>
          <p class="mt-2 text-2xl font-semibold text-navy"><?php echo $returnedItems; ?></p>
          <p class="text-xs text-gray-400 mt-1">Completed</p>
        </div>
      </div>
      </section>

      <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="card lg:col-span-2">
          <div class="card-header">
            <h2 class="font-semibold text-navy">Recent Requests</h2>
            <a href="student-requests.php" class="text-xs text-navy hover:text-gold-dark font-semibold">View all →</a>
          </div>
          <div class="overflow-x-auto">
            <table class="table">
              <thead><tr><th>Item</th><th>Qty</th><th>Date</th><th>Status</th></tr></thead>
              <tbody>
                <?php if (!$recentRequests): ?>
                  <tr><td colspan="4"><div class="empty"><div class="icon">-</div><p class="font-medium text-gray-700">No requests yet</p><p class="text-sm">Submit a request from Browse Equipment.</p></div></td></tr>
                <?php else: foreach ($recentRequests as $request):
                  $displayStatus = $request['transaction_status'] === 'RETURNED' ? 'RETURNED' : $request['status'];
                ?>
                  <tr>
                    <td><?php echo h($request['item_name']); ?></td>
                    <td><?php echo h($request['quantity_requested']); ?></td>
                    <td><?php echo h(substr($request['created_at'], 0, 10)); ?></td>
                    <td><?php echo badge($displayStatus); ?></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="card">
          <div class="card-header"><h2 class="font-semibold text-navy">Notices</h2></div>
          <div class="card-body text-sm text-gray-600 space-y-2">
            <!-- PHP: school announcements -->
            <p class="text-gray-400">No notices at this time.</p>
          </div>
        </div>
      </section>
    </main>

  </div>
  <script src="../js/shared.js"></script>
</body>
</html>
