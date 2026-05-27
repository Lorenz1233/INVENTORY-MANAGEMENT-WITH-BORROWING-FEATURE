<?php
require_once __DIR__ . '/../partials/page-data.php';
require_admin();

$studentRows = all_rows(
    'SELECT m.student_id AS id_number, m.first_name, m.last_name, m.course_code AS group_code,
            CASE
                WHEN m.course_code IS NULL THEN NULL
                WHEN c.course_name IS NULL OR c.course_name = m.course_code THEN m.course_code
                ELSE CONCAT(m.course_code, " - ", c.course_name)
            END AS group_name,
            "student" AS account_role,
            m.year_level, "student" AS user_type
     FROM master_list m
     LEFT JOIN course c ON c.course_code = m.course_code
     ORDER BY m.last_name, m.first_name'
);
$facultyRows = all_rows(
    'SELECT o.official_id AS id_number, o.first_name, o.last_name, COALESCE(p.position_code, o.position_code) AS group_code,
            COALESCE(p.position_name, o.position_code) AS group_name,
            COALESCE(u.role, "faculty") AS account_role,
            NULL AS year_level, "faculty" AS user_type
     FROM officials_masterlist o
     LEFT JOIN positions p ON p.position_code = o.position_code
     LEFT JOIN users u ON u.official_id = o.official_id
     ORDER BY o.last_name, o.first_name'
);
$masterRows = array_merge($studentRows, $facultyRows);
$courseRows = all_rows('SELECT * FROM course ORDER BY course_code');
$positionRows = all_rows('SELECT * FROM positions ORDER BY position_name, position_code');
$studentYearLevelOptions = ['First', 'Second', 'Third', 'Fourth', 'Junior High School 1', 'Junior High School 2', 'Junior High School 3', 'Junior High School 4'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Students • MSU-MCEST CEMS</title>
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
    <a href="students.php" class="nav-link active">Masterlist</a>
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
          <h1 class="text-lg font-semibold text-navy leading-tight whitespace-nowrap">Masterlist</h1>
          <p class="text-xs text-gray-500">Registered students and officials directory</p>
        </div>
        <div class="ml-auto flex items-center gap-2 flex-wrap justify-end">
          <input class="input max-w-xs" placeholder="Search masterlist..." data-search-target="#studentsTable" />
          <button type="button" class="btn btn-primary btn-sm" data-modal-open="addStudentModal" data-master-create="student">+ Add Student</button>
          <?php if ($canManageUserRoles): ?>
          <button type="button" class="btn btn-outline btn-sm" data-modal-open="addStudentModal" data-master-create="faculty">+ Add Official</button>
          <?php endif; ?>
        </div>
      </div>
    </header>

    <main class="p-6 space-y-6">
      <section class="card">
        <div class="overflow-x-auto" id="studentsTable">
          <table class="table">
            <thead><tr>
              <th>ID Number</th><th>Full Name</th><th>Department / Course</th><th>User Type / Year</th>
              <th class="text-right">Actions</th>
            </tr></thead>
            <tbody>
              <?php if (!$masterRows): ?>
                <tr><td colspan="5"><div class="empty"><div class="icon">-</div><p class="font-medium text-gray-700">No records yet</p><p class="text-sm">Add students/faculty or import from CSV.</p></div></td></tr>
              <?php else: foreach ($masterRows as $person):
                $fullName = trim($person['first_name'] . ' ' . $person['last_name']);
                $accountRole = $person['account_role'] ?? ($person['user_type'] === 'faculty' ? 'faculty' : 'student');
                $typeText = $person['user_type'] === 'faculty'
                    ? ($accountRole === 'admin' ? 'Official / Administrator' : 'Official / Staff')
                    : ($person['year_level'] ?: '-');
              ?>
                <tr data-searchable>
                  <td><?php echo h($person['id_number']); ?></td>
                  <td><?php echo h($fullName); ?></td>
                  <td><?php echo h($person['group_name'] ?: '-'); ?></td>
                  <td><?php echo h($typeText); ?></td>
                  <td class="text-right space-x-2">
                    <button type="button" class="text-blue-600 hover:text-blue-800 text-xs font-medium"
                      data-modal-open="addStudentModal"
                      data-edit-master
                      data-id="<?php echo h($person['id_number']); ?>"
                      data-type="<?php echo h($person['user_type']); ?>"
                      data-name="<?php echo h($fullName); ?>"
                      data-group-code="<?php echo h($person['group_code']); ?>"
                      data-account-role="<?php echo h($accountRole); ?>"
                      data-year="<?php echo h($person['year_level']); ?>">Edit</button>
                    <form method="POST" action="../process/masterlist.php" class="inline" onsubmit="return confirm('Delete this masterlist record?');">
                      <input type="hidden" name="action" value="delete" />
                      <input type="hidden" name="user_type" value="<?php echo h($person['user_type']); ?>" />
                      <input type="hidden" name="id_number" value="<?php echo h($person['id_number']); ?>" />
                      <button class="text-red-600 hover:text-red-800 text-xs font-medium">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
        <div class="card-body flex items-center justify-between text-sm text-gray-500">
          <p>Showing <?php echo count($masterRows); ?> records</p>
        </div>
      </section>
    </main>

    <div class="modal" id="addStudentModal">
      <div class="modal-card">
        <div class="card-header"><h3 class="font-semibold text-navy">Add Student</h3>
          <button data-modal-close class="text-gray-400 hover:text-gray-700">✕</button></div>
        <form method="POST" action="../process/masterlist.php" class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
          <input type="hidden" name="action" value="save" />
          <div><label class="label" data-id-label>Student ID number</label><input class="input" name="student_id" required /></div>
          <div><label class="label">User type</label>
            <select class="select" name="user_type">
              <option value="student">Student / Borrower</option>
              <?php if ($canManageUserRoles): ?>
              <option value="faculty">Official</option>
              <?php endif; ?>
            </select>
          </div>
          <div><label class="label">Full name</label><input class="input" name="full_name" required /></div>
          <div data-student-field><label class="label">Course / Program</label>
            <select class="select" name="course_code">
              <option value="">Select course</option>
              <?php foreach ($courseRows as $course): ?>
                <option value="<?php echo h($course['course_code']); ?>"><?php echo h($course['course_code'] . ' - ' . $course['course_name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div data-faculty-field class="hidden"><label class="label">Official Position</label>
            <select class="select" name="position_code">
              <option value="">Select position</option>
              <?php foreach ($positionRows as $position): ?>
                <option value="<?php echo h($position['position_code']); ?>"><?php echo h($position['position_name'] ?: $position['position_code']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php if ($canManageUserRoles): ?>
          <div data-faculty-field class="hidden"><label class="label">System Role</label>
            <select class="select" name="official_role">
              <option value="Staff">Staff</option>
              <option value="Administrator">Administrator</option>
            </select>
          </div>
          <?php endif; ?>
          <div data-student-field><label class="label">Year level</label>
            <select class="select" name="year_level">
              <option value="">Select year level</option>
              <?php foreach ($studentYearLevelOptions as $yearOption): ?>
                <option value="<?php echo h($yearOption); ?>"><?php echo h($yearOption); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="md:col-span-2 flex justify-end gap-2">
            <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
            <button type="submit" class="btn btn-primary" data-master-submit>Save Student</button>
          </div>
        </form>
      </div>
    </div>

  </div>
  <script src="../js/shared.js"></script>
  <script>
    function setMasterlistLabels(form, isEdit) {
      var isFaculty = form.elements.user_type.value === 'faculty';
      var title = document.querySelector('#addStudentModal h3');
      var idLabel = form.querySelector('[data-id-label]');
      var submit = form.querySelector('[data-master-submit]');

      title.textContent = isEdit
        ? (isFaculty ? 'Edit Official' : 'Edit Student')
        : (isFaculty ? 'Add Official' : 'Add Student');
      idLabel.textContent = isFaculty ? 'Official ID number' : 'Student ID number';
      submit.textContent = isEdit ? 'Save Changes' : (isFaculty ? 'Save Official' : 'Save Student');
    }

    function ensureSelectOption(select, value) {
      if (!value || Array.prototype.some.call(select.options, function (option) { return option.value === value; })) {
        return;
      }

      var option = document.createElement('option');
      option.value = value;
      option.textContent = value;
      select.appendChild(option);
    }

    function syncMasterlistFields(form) {
      var isFaculty = form.elements.user_type.value === 'faculty';
      form.querySelectorAll('[data-student-field]').forEach(function (field) {
        field.classList.toggle('hidden', isFaculty);
      });
      form.querySelectorAll('[data-faculty-field]').forEach(function (field) {
        field.classList.toggle('hidden', !isFaculty);
      });
      form.elements.course_code.disabled = isFaculty;
      form.elements.course_code.required = !isFaculty;
      form.elements.year_level.disabled = isFaculty;
      form.elements.position_code.disabled = !isFaculty;
      form.elements.position_code.required = isFaculty;
      if (form.elements.official_role) {
        form.elements.official_role.disabled = !isFaculty;
        form.elements.official_role.required = isFaculty;
      }
    }

    document.querySelector('#addStudentModal form').elements.user_type.addEventListener('change', function () {
      syncMasterlistFields(this.form);
      setMasterlistLabels(this.form, this.form.dataset.editMode === '1');
    });

    document.querySelectorAll('[data-edit-master]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var form = document.querySelector('#addStudentModal form');
        form.dataset.editMode = '1';
        form.elements.student_id.value = btn.dataset.id || '';
        form.elements.user_type.value = btn.dataset.type || 'student';
        form.elements.full_name.value = btn.dataset.name || '';
        form.elements.course_code.value = btn.dataset.type === 'student' ? (btn.dataset.groupCode || '') : '';
        form.elements.position_code.value = btn.dataset.type === 'faculty' ? (btn.dataset.groupCode || '') : '';
        if (form.elements.official_role) {
          form.elements.official_role.value = btn.dataset.accountRole === 'admin' ? 'Administrator' : 'Staff';
        }
        ensureSelectOption(form.elements.year_level, btn.dataset.year || '');
        form.elements.year_level.value = btn.dataset.year || '';
        syncMasterlistFields(form);
        setMasterlistLabels(form, true);
      });
    });
    document.querySelectorAll('[data-master-create]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var form = document.querySelector('#addStudentModal form');
        form.dataset.editMode = '0';
        form.reset();
        form.elements.user_type.value = btn.dataset.masterCreate || 'student';
        syncMasterlistFields(form);
        setMasterlistLabels(form, false);
      });
    });
    var masterlistForm = document.querySelector('#addStudentModal form');
    masterlistForm.dataset.editMode = '0';
    syncMasterlistFields(masterlistForm);
    setMasterlistLabels(masterlistForm, false);
  </script>
</body>
</html>
