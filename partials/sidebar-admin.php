<?php /* Suggested PHP partial: admin sidebar.
   Replace static <a> hrefs with PHP routes. Use $current to highlight active link.
   Example session check at top of every admin page:
     if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        header('Location: /login.php'); exit;
     }
*/ ?>
<aside class="hidden md:flex md:flex-col w-64 bg-navy text-white fixed inset-y-0 left-0 z-30">
  <div class="px-3 py-3 border-b border-white/10 flex items-center gap-3">
    <img class="brand-logo" src="../assets/images/logo.png" width="44" height="44" alt="MSU-MCEST logo" />
    <div>
      <div class="text-sm font-semibold leading-tight">MSU-MCEST</div>
      <div class="text-xs text-white/60">Inventory Management</div>
    </div>
  </div>
  <nav class="flex-1 py-4 text-sm">
    <p class="px-5 text-[11px] uppercase tracking-wider text-white/40 mb-2">Overview</p>
    <a href="admin-dashboard.php" class="nav-link"><span>Dashboard</span></a>

    <p class="px-5 mt-5 text-[11px] uppercase tracking-wider text-white/40 mb-2">Catalog</p>
    <a href="equipment.php" class="nav-link"><span>Equipment</span></a>
    <a href="materials.php" class="nav-link"><span>Campus Materials</span></a>

    <?php if ($canManageBorrowWorkflow ?? false): ?>
    <p class="px-5 mt-5 text-[11px] uppercase tracking-wider text-white/40 mb-2">Transactions</p>
    <a href="requests.php" class="nav-link flex items-center justify-between">
      <span>Borrow Requests</span>
      <!-- PHP: pending count badge -->
      <span class="text-[10px] bg-gold text-navy-dark font-semibold px-2 py-0.5 rounded-full"><?php echo $pendingCount ?? 0; ?></span>
    </a>
    <a href="transactions.php" class="nav-link"><span>Transactions</span></a>
    <?php endif; ?>

    <p class="px-5 mt-5 text-[11px] uppercase tracking-wider text-white/40 mb-2">Admin</p>
    <a href="students.php" class="nav-link"><span>Students</span></a>
    <?php if ($canManageUserRoles ?? false): ?>
    <a href="users.php" class="nav-link"><span>System Users</span></a>
    <?php endif; ?>
    <a href="admin-settings.php" class="nav-link"><span>Settings</span></a>
    <a href="change-password.php" class="nav-link"><span>Change Password</span></a>
  </nav>
  <div class="p-4 border-t border-white/10 flex items-center gap-3">
    <div class="w-9 h-9 rounded-full bg-white/10 grid place-items-center text-sm font-semibold">
      <!-- PHP: initials of logged-in user --><?php echo strtoupper(substr($_SESSION['user']['name'] ?? 'A',0,1)); ?>
    </div>
    <div class="text-sm leading-tight">
      <div class="font-medium"><?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'Admin User'); ?></div>
      <div class="text-white/50 text-xs"><?php echo htmlspecialchars($_SESSION['user']['role'] ?? 'admin'); ?></div>
    </div>
    <a href="logout.php" class="ml-auto text-white/60 hover:text-gold text-xs">Logout</a>
  </div>
</aside>
