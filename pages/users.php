<?php
require_once __DIR__ . '/../partials/page-data.php';
require_user_role_manager();

$userRows = all_rows(
    'SELECT u.*,
            COALESCE(NULLIF(TRIM(CONCAT(COALESCE(m.first_name, ""), " ", COALESCE(m.last_name, ""))), ""),
                     NULLIF(TRIM(CONCAT(COALESCE(o.first_name, ""), " ", COALESCE(o.last_name, ""))), ""),
                     u.username) AS full_name
     FROM users u
     LEFT JOIN master_list m ON m.student_id = u.student_id
     LEFT JOIN officials_masterlist o ON o.official_id = u.official_id
     ORDER BY u.created_at DESC'
);
$borrowerRows = all_rows(
    'SELECT u.*,
            COALESCE(NULLIF(TRIM(CONCAT(COALESCE(m.first_name, ""), " ", COALESCE(m.last_name, ""))), ""),
                     u.username) AS full_name
     FROM users u
     LEFT JOIN master_list m ON m.student_id = u.student_id
     WHERE u.role = "student" AND u.is_active = 1
     ORDER BY u.username'
);
$officialRows = array_values(array_filter($userRows, function ($user) {
    return in_array($user['role'], ['admin', 'faculty'], true);
}));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>System Users - MSU-MCEST CEMS</title>
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
<aside class="sidebar hidden md:flex md:flex-col w-64 bg-navy text-white fixed inset-y-0 left-0 z-30">
  <div class="px-5 py-5 border-b border-white/10 flex items-center gap-3">
    <div class="w-9 h-9 rounded-md bg-gold text-navy-dark grid place-items-center font-bold">M</div>
    <div><div class="text-sm font-semibold leading-tight">MSU-MCEST</div><div class="text-xs text-white/60">Equipment Mgmt</div></div>
  </div>
  <nav class="flex-1 py-4 text-sm">
    <p class="px-5 mt-3 text-[11px] uppercase tracking-wider text-white/40 mb-2">Overview</p>
    <a href="admin-dashboard.php" class="nav-link">Dashboard</a>
    <p class="px-5 mt-3 text-[11px] uppercase tracking-wider text-white/40 mb-2">Catalog</p>
    <a href="equipment.php" class="nav-link">Equipment</a>
    <a href="materials.php" class="nav-link">Campus Materials</a>
    <p class="px-5 mt-3 text-[11px] uppercase tracking-wider text-white/40 mb-2">Academics</p>
    <a href="courses.php" class="nav-link">Courses</a>
    <a href="students.php" class="nav-link">Students</a>
    <?php if ($canManageBorrowWorkflow): ?>
    <p class="px-5 mt-3 text-[11px] uppercase tracking-wider text-white/40 mb-2">Transactions</p>
    <a href="requests.php" class="nav-link flex items-center">Borrow Requests<span class="ml-auto text-[10px] bg-gold text-navy-dark font-semibold px-2 py-0.5 rounded-full"><?php echo $pendingCount; ?></span></a>
    <a href="transactions.php" class="nav-link">Transactions</a>
    <?php endif; ?>
    <p class="px-5 mt-3 text-[11px] uppercase tracking-wider text-white/40 mb-2">Admin</p>
    <?php if ($canManageUserRoles): ?>
    <a href="users.php" class="nav-link active">System Users</a>
    <?php endif; ?>
    <a href="admin-settings.php" class="nav-link">Settings</a>
    <a href="change-password.php" class="nav-link">Change Password</a>
  </nav>
  <div class="p-4 border-t border-white/10 flex items-center gap-3">
    <div class="w-9 h-9 rounded-full bg-white/10 grid place-items-center text-sm font-semibold"><?php echo h($currentInitials); ?></div>
    <div class="text-sm leading-tight"><div class="font-medium"><?php echo h($currentName); ?></div><div class="text-white/50 text-xs"><?php echo h(ucfirst($currentUser['role'] ?? 'user')); ?></div></div>
    <a href="../process/logout.php" class="ml-auto text-white/60 hover:text-gold text-xs" data-confirm="Log out now?">Logout</a>
  </div>
</aside>

<div class="md:ml-64 main-shift page-fade">
  <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
    <div class="px-6 py-4 flex flex-wrap items-center gap-4">
      <button data-sidebar-toggle class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-md border border-gray-200 text-navy">☰</button>
      <div><h1 class="text-lg font-semibold text-navy leading-tight whitespace-nowrap">System Users</h1><p class="text-xs text-gray-500">Administrators, staff, and borrower accounts</p></div>
      <div class="ml-auto flex items-center gap-2 flex-wrap justify-end"><input class="input max-w-xs" placeholder="Search users..." data-search-target="#usersTable" /><?php if ($canManageUserRoles): ?><button class="btn btn-primary btn-sm" data-modal-open="addUserModal">+ Add User</button><?php endif; ?></div>
    </div>
  </header>

  <main class="p-6 space-y-6">
    <section class="card">
      <div class="card-header">
        <select class="select max-w-[10rem]" id="roleFilter">
          <option value="">All roles</option>
          <option value="admin">Administrator</option>
          <option value="faculty">Faculty</option>
          <option value="student">Student</option>
        </select>
      </div>
      <div class="overflow-x-auto" id="usersTable">
        <table class="table">
          <thead><tr><th>Username</th><th>Full Name</th><th>Role</th><th>Status</th><th>Created</th><th class="text-center">Actions</th></tr></thead>
          <tbody>
            <?php if (!$userRows): ?>
              <tr><td colspan="6"><div class="empty"><div class="icon">-</div><p class="font-medium text-gray-700">No users yet</p><p class="text-sm">System users will appear here.</p></div></td></tr>
            <?php else: foreach ($userRows as $user):
              $roleLabel = $user['role'] === 'admin' ? 'Administrator' : ($user['role'] === 'faculty' ? 'Staff' : 'Student');
              $fullName = trim($user['full_name']) ?: $user['username'];
            ?>
              <tr data-searchable data-role="<?php echo h($user['role']); ?>">
                <td><?php echo h($user['username']); ?></td>
                <td><?php echo h($fullName); ?></td>
                <td><?php echo h($roleLabel); ?></td>
                <td><?php echo $user['is_active'] ? '<span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Active</span>' : '<span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded">Inactive</span>'; ?></td>
                <td><?php echo h(substr($user['created_at'], 0, 10)); ?></td>
                <td>
                  <?php if ((int) $user['user_id'] !== (int) ($_SESSION['user_id'] ?? 0)): ?>
                    <form method="POST" action="../process/users.php" class="flex justify-center" onsubmit="return confirm('Deactivate this user?');">
                      <input type="hidden" name="action" value="deactivate" />
                      <input type="hidden" name="user_id" value="<?php echo h($user['user_id']); ?>" />
                      <button class="text-red-600 hover:text-red-800 text-xs font-medium">Deactivate</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <?php if ($canManageUserRoles): ?>
    <section class="card">
      <div class="card-header"><h2 class="font-semibold text-navy">Manage Official Authorizations</h2><button class="btn btn-primary btn-sm" data-modal-open="authorizeOfficial">+ Authorize Official</button></div>
      <div class="overflow-x-auto">
        <table class="table text-sm">
          <thead><tr><th>Username</th><th>Full Name</th><th>Current Role</th><th>Authorized As</th><th>Status</th><th class="text-center">Actions</th></tr></thead>
          <tbody>
            <?php if (!$officialRows): ?>
              <tr><td colspan="6"><div class="empty text-sm"><p class="font-medium text-gray-700">No authorized officials</p><p class="text-xs">System users can be authorized as officials here.</p></div></td></tr>
            <?php else: foreach ($officialRows as $official):
              $fullName = trim($official['full_name']) ?: $official['username'];
              $roleLabel = $official['role'] === 'admin' ? 'Administrator' : 'Staff';
            ?>
              <tr data-searchable>
                <td><?php echo h($official['username']); ?></td>
                <td><?php echo h($fullName); ?></td>
                <td><span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded">User</span></td>
                <td><span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded"><?php echo h($roleLabel); ?></span></td>
                <td><?php echo $official['is_active'] ? '<span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Active</span>' : '<span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded">Inactive</span>'; ?></td>
                <td>
                  <form method="POST" action="../process/users.php" class="flex justify-center" onsubmit="return confirm('Revoke official authorization?');">
                    <input type="hidden" name="action" value="role_update" />
                    <input type="hidden" name="user_id" value="<?php echo h($official['user_id']); ?>" />
                    <input type="hidden" name="new_role" value="Revoke" />
                    <button class="text-red-600 hover:text-red-800 text-xs font-medium">Revoke</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </section>
    <?php endif; ?>
  </main>

  <?php if ($canManageUserRoles): ?>
  <div class="modal" id="addUserModal">
    <div class="modal-card">
      <div class="card-header"><h3 class="font-semibold text-navy">Add System User</h3><button data-modal-close class="text-gray-400 hover:text-gray-700">x</button></div>
      <form method="POST" action="../process/users.php" class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
        <input type="hidden" name="action" value="create" />
        <div><label class="label">Username</label><input class="input" name="username" required /></div>
        <div><label class="label">Full name</label><input class="input" name="full_name" required /></div>
        <div><label class="label">Initial Role</label><select class="select" name="role"><option>Administrator</option><option>Staff</option><option>Student</option></select></div>
        <div><label class="label">Status</label><select class="select" name="status"><option>Active</option><option>Inactive</option></select></div>
        <div><label class="label">Temporary Password</label><input class="input" type="password" name="password" /></div>
        <div class="md:col-span-2 flex justify-end gap-2"><button type="button" class="btn btn-outline" data-modal-close>Cancel</button><button type="submit" class="btn btn-primary">Create User</button></div>
      </form>
    </div>
  </div>

  <div class="modal" id="authorizeOfficial">
    <div class="modal-card">
      <div class="card-header"><h3 class="font-semibold text-navy">Authorize Official Role</h3><button data-modal-close class="text-gray-400 hover:text-gray-700">x</button></div>
      <form method="POST" action="../process/users.php" class="card-body grid grid-cols-1 gap-4">
        <input type="hidden" name="action" value="role_update" />
        <div><label class="label">Select System User</label><select class="select" name="user_id" required><option value="">Choose a user...</option><?php foreach ($borrowerRows as $borrower): ?><option value="<?php echo h($borrower['user_id']); ?>"><?php echo h(trim($borrower['full_name']) ?: $borrower['username']); ?> (<?php echo h($borrower['username']); ?>)</option><?php endforeach; ?></select></div>
        <div><label class="label">Authorize As Role</label><select class="select" name="new_role" required><option value="">Select a role...</option><option value="Faculty">Faculty</option><option value="Admin">Admin</option></select></div>
        <div class="flex justify-end gap-2"><button type="button" class="btn btn-outline" data-modal-close>Cancel</button><button type="submit" class="btn btn-primary">Authorize</button></div>
      </form>
    </div>
  </div>

  <div class="modal" id="editAuthorization">
    <div class="modal-card">
      <div class="card-header"><h3 class="font-semibold text-navy">Edit Authorization</h3><button data-modal-close class="text-gray-400 hover:text-gray-700">x</button></div>
      <form method="POST" action="../process/users.php" class="card-body grid grid-cols-1 gap-4">
        <input type="hidden" name="action" value="role_update" />
        <input type="hidden" name="user_id" id="authUserId" />
        <div><label class="label">Official User</label><input class="input bg-gray-100" id="authUserDisplay" disabled /></div>
        <div><label class="label">Current Authorization</label><input class="input bg-gray-100" id="authCurrentRole" disabled /></div>
        <div><label class="label">Change Authorization To</label><select class="select" name="new_role" required><option value="">Select a role...</option><option value="faculty">Faculty</option><option value="Admin">Admin</option><option value="Revoke">Revoke</option></select></div>
        <div class="flex justify-end gap-2"><button type="button" class="btn btn-outline" data-modal-close>Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
      </form>
    </div>
  </div>
  <?php endif; ?>
</div>
<script src="../js/shared.js"></script>
<script>
  document.getElementById('roleFilter').addEventListener('change', function () {
    var role = this.value;
    document.querySelectorAll('#usersTable tbody tr[data-role]').forEach(function (row) {
      row.style.display = !role || row.dataset.role === role ? '' : 'none';
    });
  });
  function setAuthorizationData(userId, username, fullName, currentAuth) {
    document.getElementById('authUserId').value = userId;
    document.getElementById('authUserDisplay').value = fullName + ' (' + username + ')';
    document.getElementById('authCurrentRole').value = currentAuth;
  }
</script>
</body>
</html>
