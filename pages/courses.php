<?php
require_once __DIR__ . '/../partials/page-data.php';
require_admin();
$courseRows = all_rows('SELECT * FROM course ORDER BY course_code');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Courses • MSU-MCEST CEMS</title>
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
    <a href="courses.php" class="nav-link active">Courses</a>
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
          <h1 class="text-lg font-semibold text-navy leading-tight whitespace-nowrap">Courses</h1>
          <p class="text-xs text-gray-500">Academic programs and course directory</p>
        </div>
        <div class="ml-auto flex items-center gap-2 flex-wrap justify-end"><input class="input max-w-xs" placeholder="Search courses..." data-search-target="#coursesTable" /><button class="btn btn-primary btn-sm" data-modal-open="addCourseModal">+ Add Course</button></div>
      </div>
    </header>

    <main class="p-6 space-y-6">
      <section class="card">
        <div class="overflow-x-auto" id="coursesTable">
          <table class="table">
            <thead><tr>
              <th>Course Code</th><th>Course Name</th><th class="text-right">Actions</th>
            </tr></thead>
            <tbody>
              <?php if (!$courseRows): ?>
                <tr><td colspan="3"><div class="empty"><div class="icon">-</div><p class="font-medium text-gray-700">No courses yet</p><p class="text-sm">Add courses or import from CSV.</p></div></td></tr>
              <?php else: foreach ($courseRows as $course): ?>
                <tr data-searchable>
                  <td><?php echo h($course['course_code']); ?></td>
                  <td><?php echo h($course['course_name']); ?></td>
                  <td class="text-right space-x-2">
                    <button type="button" class="text-blue-600 hover:text-blue-800 text-xs font-medium" data-modal-open="addCourseModal" data-edit-course data-code="<?php echo h($course['course_code']); ?>" data-name="<?php echo h($course['course_name']); ?>">Edit</button>
                    <form method="POST" action="../process/courses.php" class="inline" onsubmit="return confirm('Delete this course?');">
                      <input type="hidden" name="action" value="delete" />
                      <input type="hidden" name="course_code" value="<?php echo h($course['course_code']); ?>" />
                      <button class="text-red-600 hover:text-red-800 text-xs font-medium">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
        <div class="card-body flex items-center justify-between text-sm text-gray-500">
          <p>Showing <?php echo count($courseRows); ?> records</p>
        </div>
      </section>
    </main>

    <div class="modal" id="addCourseModal">
      <div class="modal-card">
        <div class="card-header"><h3 class="font-semibold text-navy">Add Course</h3>
          <button data-modal-close class="text-gray-400 hover:text-gray-700">✕</button></div>
        <form method="POST" action="../process/courses.php" class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
          <div><label class="label">Course Code</label><input class="input" name="course_code" placeholder="e.g., CS101" required /></div>
          <div><label class="label">Course Name</label><input class="input" name="course_name" placeholder="e.g., Introduction to Computer Science" required /></div>
          <div class="md:col-span-2 flex justify-end gap-2">
            <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
            <button type="submit" class="btn btn-primary">Save Course</button>
          </div>
        </form>
      </div>
    </div>

  </div>
  <script src="../js/shared.js"></script>
  <script>
    document.querySelectorAll('[data-edit-course]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var form = document.querySelector('#addCourseModal form');
        document.querySelector('#addCourseModal h3').textContent = 'Edit Course';
        form.elements.course_code.value = btn.dataset.code || '';
        form.elements.course_name.value = btn.dataset.name || '';
      });
    });
    document.querySelectorAll('[data-modal-open="addCourseModal"]:not([data-edit-course])').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var form = document.querySelector('#addCourseModal form');
        document.querySelector('#addCourseModal h3').textContent = 'Add Course';
        form.reset();
      });
    });
  </script>
</body>
</html>
