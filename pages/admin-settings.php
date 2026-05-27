<?php
require_once __DIR__ . '/../partials/page-data.php';
require_admin();
$categoryRows = all_rows('SELECT * FROM category ORDER BY category_name');
$unitRows = all_rows('SELECT * FROM unit ORDER BY unit_name');
$positionRows = all_rows('SELECT * FROM positions ORDER BY position_name, position_code');
$settingsUsers = all_rows(
    'SELECT u.*,
            COALESCE(NULLIF(TRIM(CONCAT(COALESCE(m.first_name, ""), " ", COALESCE(m.last_name, ""))), ""),
                     NULLIF(TRIM(CONCAT(COALESCE(o.first_name, ""), " ", COALESCE(o.last_name, ""))), ""),
                     u.username) AS full_name
     FROM users u
     LEFT JOIN master_list m ON m.student_id = u.student_id
     LEFT JOIN officials_masterlist o ON o.official_id = u.official_id
     ORDER BY u.username'
);
$settingsOfficials = array_values(array_filter($settingsUsers, function ($user) {
    return in_array($user['role'], ['admin', 'faculty'], true);
}));
$settingsBorrowers = array_values(array_filter($settingsUsers, function ($user) {
    return $user['role'] === 'student' && (int) $user['is_active'] === 1;
}));
$importSummary = isset($_GET['success']) && $_GET['success'] === 'imported'
    ? sprintf(
        'Import complete: %d total, %d successful, %d failed.',
        (int) ($_GET['total'] ?? 0),
        (int) ($_GET['count'] ?? 0),
        (int) ($_GET['failed'] ?? 0)
    )
    : '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Settings • MSU-MCEST CEMS</title>
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
    <div class="w-9 h-9 rounded-md bg-gold text-navy-dark grid place-items-center font-bold">M</div>
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
    <?php if ($canManageBorrowWorkflow): ?>
    <p class="px-5 mt-3 first:mt-0 text-[11px] uppercase tracking-wider text-white/40 mb-2">Transactions</p>
    <a href="requests.php" class="nav-link flex items-center">Borrow Requests<span class="ml-auto text-[10px] bg-gold text-navy-dark font-semibold px-2 py-0.5 rounded-full"><?php echo $pendingCount; ?></span></a>
    <a href="transactions.php" class="nav-link">Transactions</a>
    <?php endif; ?>
    <p class="px-5 mt-3 first:mt-0 text-[11px] uppercase tracking-wider text-white/40 mb-2">Admin</p>
    <?php if ($canManageUserRoles): ?>
    <a href="users.php" class="nav-link">System Users</a>
    <?php endif; ?>
    <a href="admin-settings.php" class="nav-link active">Settings</a>
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
          <h1 class="text-lg font-semibold text-navy leading-tight whitespace-nowrap">Settings</h1>
          <p class="text-xs text-gray-500">Administrative utilities</p>
        </div>
        <div class="ml-auto flex items-center gap-2 flex-wrap justify-end"></div>
      </div>
    </header>

    <main class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
      <section class="card">
        <div class="card-header"><h2 class="font-semibold text-navy">CSV Import</h2></div>
        <div class="card-body space-y-3">
          <p class="text-sm text-gray-600">Import students, equipment, or materials from CSV files.</p>
          <?php if ($importSummary): ?>
            <p class="text-sm rounded-md border border-green-200 bg-green-50 text-green-700 px-3 py-2"><?php echo h($importSummary); ?></p>
          <?php endif; ?>
          <form method="POST" action="../process/admin_settings.php" enctype="multipart/form-data" class="space-y-3">
            <div>
              <label class="label">Target dataset</label>
              <select class="select" name="dataset">
                <option value="students">Students</option>
                <?php if ($canManageUserRoles): ?>
                <option value="faculty">Faculty</option>
                <option value="masterlist">Mixed Masterlist</option>
                <?php endif; ?>
                <option value="equipment">Equipment</option>
                <option value="materials">Materials</option>
              </select>
            </div>
            <div>
              <label class="label">Import mode</label>
              <select class="select" name="import_mode">
                <option value="safe">Safe mode: skip failed rows</option>
                <option value="strict">Strict mode: rollback on first error</option>
              </select>
            </div>
            <div>
              <label class="label">CSV file</label>
              <input class="input" type="file" name="csv_file" accept=".csv" />
            </div>
            <button class="btn btn-primary">Upload CSV</button>
          </form>
        </div>
      </section>

      <section class="card">
        <div class="card-header"><h2 class="font-semibold text-navy">System Notes</h2></div>
        <div class="card-body text-sm text-gray-600 space-y-2">
          <p>Use this area for school-specific notes, contact information for the IT office, or instructions for staff.</p>
          <p class="text-gray-400"><!-- PHP: render notes from DB -->No notes yet.</p>
        </div>
      </section>

      <section class="card lg:col-span-2">
        <div class="card-header"><h2 class="font-semibold text-navy">Reference Data Management</h2></div>
        <div class="card-body grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="space-y-3">
            <h3 class="text-sm font-semibold text-navy">Categories</h3>
            <form method="POST" action="../process/categories.php" class="flex gap-2">
              <input class="input" name="category_name" placeholder="e.g., Electronics" required />
              <button class="btn btn-primary btn-sm">Add</button>
            </form>
            <?php foreach ($categoryRows as $category): ?>
              <form method="POST" action="../process/categories.php" class="flex gap-2">
                <input type="hidden" name="category_id" value="<?php echo h($category['category_id']); ?>" />
                <input class="input" name="category_name" value="<?php echo h($category['category_name']); ?>" required />
                <button class="btn btn-outline btn-sm">Save</button>
                <button name="action" value="delete" class="btn btn-outline btn-sm" data-confirm="Delete this category?">Delete</button>
              </form>
            <?php endforeach; ?>
          </div>
          <div class="space-y-3">
            <h3 class="text-sm font-semibold text-navy">Units</h3>
            <form method="POST" action="../process/units.php" class="flex gap-2">
              <input class="input" name="unit_name" placeholder="e.g., pcs" required />
              <button class="btn btn-primary btn-sm">Add</button>
            </form>
            <?php foreach ($unitRows as $unit): ?>
              <form method="POST" action="../process/units.php" class="flex gap-2">
                <input type="hidden" name="unit_id" value="<?php echo h($unit['unit_id']); ?>" />
                <input class="input" name="unit_name" value="<?php echo h($unit['unit_name']); ?>" required />
                <button class="btn btn-outline btn-sm">Save</button>
                <button name="action" value="delete" class="btn btn-outline btn-sm" data-confirm="Delete this unit?">Delete</button>
              </form>
            <?php endforeach; ?>
          </div>
          <div class="space-y-3">
            <h3 class="text-sm font-semibold text-navy">Faculty Positions</h3>
            <form method="POST" action="../process/positions.php" class="grid grid-cols-[7rem_1fr_auto] gap-2">
              <input class="input" name="position_code" placeholder="Code" required />
              <input class="input" name="position_name" placeholder="e.g., Professor" required />
              <button class="btn btn-primary btn-sm">Add</button>
            </form>
            <?php foreach ($positionRows as $position): ?>
              <form method="POST" action="../process/positions.php" class="grid grid-cols-[7rem_1fr_auto_auto] gap-2">
                <input class="input bg-gray-100" name="position_code" value="<?php echo h($position['position_code']); ?>" readonly />
                <input class="input" name="position_name" value="<?php echo h($position['position_name']); ?>" required />
                <button class="btn btn-outline btn-sm">Save</button>
                <button name="action" value="delete" class="btn btn-outline btn-sm" data-confirm="Delete this position?">Delete</button>
              </form>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section class="card lg:col-span-2">
        <div class="card-header"><h2 class="font-semibold text-navy">Roles & Permissions</h2></div>
        <div class="card-body text-sm text-gray-600">
          <p>Permissions are enforced server-side via PHP session checks. Front-end visibility hints only.</p>
          <ul class="list-disc list-inside mt-2 space-y-1">
            <li><span class="font-medium text-navy">Administrator</span> — full access to all admin pages.</li>
            <li><span class="font-medium text-navy">Staff</span> — equipment, materials, courses, and students.</li>
            <li><span class="font-medium text-navy">Student</span> — browse and request equipment only.</li>
          </ul>
        </div>
      </section>

      <?php if ($canManageUserRoles): ?>
      <section class="card lg:col-span-2">
        <div class="card-header"><h2 class="font-semibold text-navy">Official Role Authorization</h2></div>
        <div class="card-body space-y-4">
          <p class="text-sm text-gray-600">Manage authorization and revocation of official (staff/admin) roles. Use this section to grant or revoke elevated permissions to system users.</p>
          <div class="overflow-x-auto">
            <table class="table text-sm">
              <thead><tr><th>Username</th><th>Full Name</th><th>Current Role</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
              <tbody>
                <?php if (!$settingsOfficials): ?>
                  <tr><td colspan="5"><div class="empty text-sm"><p class="font-medium text-gray-700">No official users found</p></div></td></tr>
                <?php else: foreach ($settingsOfficials as $official):
                  $fullName = trim($official['full_name']) ?: $official['username'];
                  $roleLabel = $official['role'] === 'admin' ? 'Administrator' : 'Staff';
                ?>
                <tr>
                  <td><?php echo h($official['username']); ?></td>
                  <td><?php echo h($fullName); ?></td>
                  <td><span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded"><?php echo h($roleLabel); ?></span></td>
                  <td><?php echo $official['is_active'] ? '<span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Active</span>' : '<span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded">Inactive</span>'; ?></td>
                  <td class="text-right space-x-2">
                    <button class="text-blue-600 hover:text-blue-800 text-xs font-medium" data-modal-open="editRoleModal" onclick="setUserForRoleEdit(<?php echo js($official['username']); ?>, <?php echo js($fullName); ?>, <?php echo js($roleLabel); ?>)">Edit Role</button>
                    <form method="POST" action="../process/admin_settings.php" class="inline" onsubmit="return confirm('Revoke official authorization?');">
                      <input type="hidden" name="username" value="<?php echo h($official['username']); ?>" />
                      <input type="hidden" name="new_role" value="Revoke" />
                      <button class="text-red-600 hover:text-red-800 text-xs font-medium">Revoke</button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
          <div class="pt-4 border-t flex gap-2">
            <button class="btn btn-primary btn-sm" data-modal-open="authorizeRoleModal">+ Authorize New Official</button>
          </div>
        </div>
      </section>
      <?php endif; ?>

      <section class="card lg:col-span-2">
        <div class="card-header"><h2 class="font-semibold text-navy">Audit / Help</h2></div>
        <div class="card-body text-sm text-gray-600">
          <!-- PHP: link to documentation, or render audit trail -->
          <p class="text-gray-400">No audit entries yet.</p>
        </div>
      </section>
    </main>

    <?php if ($canManageUserRoles): ?>
    <!-- Modal: Authorize New Official -->
    <div class="modal" id="authorizeRoleModal">
      <div class="modal-card">
        <div class="card-header"><h3 class="font-semibold text-navy">Authorize Official Role</h3>
          <button data-modal-close class="text-gray-400 hover:text-gray-700">✕</button></div>
        <form method="POST" action="../process/admin_settings.php" class="card-body grid grid-cols-1 gap-4">
          <div><label class="label">Select System User</label>
            <select class="select" name="user_id" required>
              <option value="">Choose a system user...</option>
              <?php foreach ($settingsBorrowers as $borrower): ?><option value="<?php echo h($borrower['user_id']); ?>"><?php echo h(trim($borrower['full_name']) ?: $borrower['username']); ?> - <?php echo h($borrower['username']); ?></option><?php endforeach; ?>
            </select>
          </div>
          <div><label class="label">Authorize As Role</label>
            <select class="select" name="new_role" required>
              <option value="">Select role...</option>
              <option value="Staff">Staff (Equipment Management)</option>
              <option value="Administrator">Administrator (Full Access)</option>
            </select>
          </div>
          <div class="flex justify-end gap-2">
            <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
            <button type="submit" class="btn btn-primary">Authorize</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Edit User Role -->
    <div class="modal" id="editRoleModal">
      <div class="modal-card">
        <div class="card-header"><h3 class="font-semibold text-navy">Edit Official Authorization</h3>
          <button data-modal-close class="text-gray-400 hover:text-gray-700">✕</button></div>
        <form method="POST" action="../process/admin_settings.php" class="card-body grid grid-cols-1 gap-4">
          <input type="hidden" name="username" id="editUsername" />
          <div><label class="label">User</label><input class="input bg-gray-100" id="editUserDisplay" disabled /></div>
          <div><label class="label">Current Role</label><input class="input bg-gray-100" id="editCurrentRole" disabled /></div>
          <div><label class="label">Change Role To</label>
            <select class="select" name="new_role" required>
              <option value="">Select role...</option>
              <option value="Staff">Staff (Equipment Management)</option>
              <option value="Administrator">Administrator (Full Access)</option>
            </select>
          </div>
          <div class="flex justify-end gap-2">
            <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
            <button type="submit" class="btn btn-primary">Update Role</button>
          </div>
        </form>
      </div>
    </div>
    <?php endif; ?>

  </div>
  <script src="../js/shared.js"></script>
  <script>
    function setUserForRoleEdit(username, fullName, currentRole) {
      document.getElementById('editUsername').value = username;
      document.getElementById('editUserDisplay').value = fullName + ' (' + username + ')';
      document.getElementById('editCurrentRole').value = currentRole;
    }
  </script>
</body>
</html>
