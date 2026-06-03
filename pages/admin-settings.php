<?php
require_once __DIR__ . '/../partials/page-data.php';
require_admin();

function settings_audit_action_label($action)
{
    return ucwords(str_replace('_', ' ', clean($action)));
}

function settings_audit_detail_text($details)
{
    $details = trim((string) ($details ?? ''));
    if ($details === '') {
        return '';
    }

    $decoded = json_decode($details, true);
    if (!is_array($decoded)) {
        return substr($details, 0, 160);
    }

    $parts = [];
    foreach ($decoded as $key => $value) {
        if (is_array($value)) {
            $value = count($value) . ' item(s)';
        } elseif (is_bool($value)) {
            $value = $value ? 'yes' : 'no';
        } elseif ($value === null || $value === '') {
            $value = 'none';
        } else {
            $value = (string) $value;
        }

        if (strlen($value) > 60) {
            $value = substr($value, 0, 57) . '...';
        }

        $parts[] = str_replace('_', ' ', (string) $key) . ': ' . $value;
        if (count($parts) >= 4) {
            break;
        }
    }

    return implode('; ', $parts);
}

$categoryRows = all_rows('SELECT * FROM category ORDER BY category_name');
$unitRows = all_rows('SELECT * FROM unit ORDER BY unit_name');
$positionRows = all_rows('SELECT * FROM positions ORDER BY position_name, position_code');
$systemNotesRow = all_rows(
    'SELECT s.*,
            COALESCE(NULLIF(TRIM(CONCAT(COALESCE(m.first_name, ""), " ", COALESCE(m.last_name, ""))), ""),
                     NULLIF(TRIM(CONCAT(COALESCE(o.first_name, ""), " ", COALESCE(o.last_name, ""))), ""),
                     u.username) AS updated_by_name
     FROM system_settings s
     LEFT JOIN users u ON u.user_id = s.updated_by
     LEFT JOIN master_list m ON m.student_id = u.student_id
     LEFT JOIN officials_masterlist o ON o.official_id = u.official_id
     WHERE s.setting_key = "system_notes"
     LIMIT 1'
)[0] ?? null;
$systemNotes = $systemNotesRow['setting_value'] ?? '';
$auditRows = all_rows(
    'SELECT a.*,
            COALESCE(NULLIF(TRIM(CONCAT(COALESCE(m.first_name, ""), " ", COALESCE(m.last_name, ""))), ""),
                     NULLIF(TRIM(CONCAT(COALESCE(o.first_name, ""), " ", COALESCE(o.last_name, ""))), ""),
                     u.username,
                     "System") AS actor_name,
            u.username AS actor_username
     FROM audit_log a
     LEFT JOIN users u ON u.user_id = a.actor_user_id
     LEFT JOIN master_list m ON m.student_id = u.student_id
     LEFT JOIN officials_masterlist o ON o.official_id = u.official_id
     ORDER BY a.created_at DESC, a.audit_id DESC
     LIMIT 15'
);
$csvImportFormats = [
    'students' => [
        'label' => 'Students',
        'summary' => 'Use one row per student borrower.',
        'required' => 'student_id plus first_name and last_name. You may use full_name or name instead of separate name columns.',
        'headers' => ['student_id', 'first_name', 'last_name', 'course', 'year_level'],
        'example' => ['20240001', 'Ana', 'Santos', 'BSIT', 'First'],
        'download' => '../assets/samples/students-import-example.csv',
        'notes' => ['student_id must be a positive number.', 'year_level accepts First, Second, Third, Fourth, or Junior High School 1-4.'],
    ],
    'faculty' => [
        'label' => 'Faculty',
        'summary' => 'Use one row per official staff or faculty account.',
        'required' => 'official_id plus first_name and last_name. You may use full_name or name instead of separate name columns.',
        'headers' => ['official_id', 'first_name', 'last_name', 'department'],
        'example' => ['FAC-1001', 'Liza', 'Cruz', 'Faculty'],
        'download' => '../assets/samples/faculty-import-example.csv',
        'notes' => ['department is used as the position/department reference.', 'Only administrators can import faculty.'],
    ],
    'masterlist' => [
        'label' => 'Mixed Masterlist',
        'summary' => 'Use this when one CSV contains both students and faculty.',
        'required' => 'user_type, id_number, and name columns. user_type must be student or faculty.',
        'headers' => ['user_type', 'id_number', 'first_name', 'last_name', 'department', 'year_level'],
        'example' => ['student', '20240003', 'Camille', 'Garcia', 'BSIT', 'Junior High School 1'],
        'download' => '../assets/samples/masterlist-import-example.csv',
        'notes' => ['Use department for student course or faculty position.', 'Only administrators can import mixed masterlists.'],
    ],
    'equipment' => [
        'label' => 'Equipment',
        'summary' => 'Use one row per item or equipment record.',
        'required' => 'item_name, equipment_name, or name, quantity or qty, and owner_official_id.',
        'headers' => ['item_name', 'item_code', 'category', 'unit', 'quantity', 'condition', 'description', 'date_added', 'owner_official_id'],
        'example' => ['Digital Multimeter', 'EQ-1001', 'Electronics', 'pcs', '12', 'Good', 'Handheld meter for lab use', '2026-05-27', 'OFF001'],
        'download' => '../assets/samples/equipment-import-example.csv',
        'extra_downloads' => [
            ['label' => 'Download items example', 'href' => '../assets/samples/items-import-example.csv'],
            ['label' => 'Download equipments example', 'href' => '../assets/samples/equipments-import-example.csv'],
        ],
        'notes' => ['owner_official_id must match an official in the masterlist.', 'Repeat the same item name with a different owner to split stock ownership.', 'quantity must be a whole number.', 'date_added must use YYYY-MM-DD when provided.'],
    ],
    'materials' => [
        'label' => 'Materials',
        'summary' => 'Use one row per campus material or supply.',
        'required' => 'material_name or item_name, quantity, and owner_official_id.',
        'headers' => ['material_name', 'category', 'unit', 'quantity', 'unit_price', 'description', 'date_added', 'owner_official_id'],
        'example' => ['Bond Paper A4', 'Office Supplies', 'ream', '20', '245.00', 'White copy paper', '2026-05-27', 'OFF003'],
        'download' => '../assets/samples/materials-import-example.csv',
        'notes' => ['owner_official_id must match an official in the masterlist.', 'Repeat the same material name with a different owner to split stock ownership.', 'unit_price accepts up to two decimal places.', 'date_added must use YYYY-MM-DD when provided.'],
    ],
];

$availableCsvImportFormats = $csvImportFormats;
if (!$canManageUserRoles) {
    unset($availableCsvImportFormats['faculty'], $availableCsvImportFormats['masterlist']);
}
reset($availableCsvImportFormats);
$firstCsvFormatKey = key($availableCsvImportFormats);
$settingsErrorCode = $_GET['error'] ?? '';
$settingsErrorSection = $_GET['section'] ?? '';
$settingsErrorMessages = [
    'csv_import_failed' => $_GET['reason'] ?? 'The CSV file could not be imported. Check that the headers match the selected dataset.',
    'dataset' => 'Invalid import dataset.',
    'not_allowed' => 'You are not allowed to perform that settings action.',
    'upload_failed' => 'CSV upload failed. Please choose the file again.',
    'file_type' => 'Only CSV files are accepted.',
    'notes_too_long' => 'System notes must be 5000 characters or fewer.',
    'invalid_action' => 'The submitted settings action was not recognized.',
    'settings_failed' => 'The settings request could not be completed.',
];
$settingsError = '';
if ($settingsErrorCode !== '') {
    $settingsError = $settingsErrorMessages[$settingsErrorCode] ?? 'The settings request could not be completed.';
}
$csvErrorCodes = ['csv_import_failed', 'dataset', 'upload_failed', 'file_type'];
$notesErrorCodes = ['notes_too_long', 'not_allowed', 'settings_failed'];
$csvSettingsError = $settingsError !== '' && ($settingsErrorSection === 'csv' || ($settingsErrorSection === '' && in_array($settingsErrorCode, $csvErrorCodes, true)));
$noteSettingsError = $settingsError !== '' && ($settingsErrorSection === 'notes' || ($settingsErrorSection === '' && in_array($settingsErrorCode, $notesErrorCodes, true)));
$importSummary = isset($_GET['success']) && $_GET['success'] === 'imported'
    ? sprintf(
        'Import complete: %d total, %d successful, %d failed.',
        (int) ($_GET['total'] ?? 0),
        (int) ($_GET['count'] ?? 0),
        (int) ($_GET['failed'] ?? 0)
    )
    : '';
$notesUpdateSummary = isset($_GET['success']) && $_GET['success'] === 'notes_saved'
    ? 'System notes saved successfully.'
    : '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Settings • MSU-MCEST</title>
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
      <div class="text-xs text-white/60">Inventory System</div>
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
          <?php if ($csvSettingsError): ?>
            <p class="text-sm rounded-md border border-red-200 bg-red-50 text-red-700 px-3 py-2"><?php echo h($settingsError); ?></p>
          <?php endif; ?>
          <?php if ($importSummary): ?>
            <p class="text-sm rounded-md border border-green-200 bg-green-50 text-green-700 px-3 py-2"><?php echo h($importSummary); ?></p>
          <?php endif; ?>
          <form method="POST" action="../process/admin_settings.php" enctype="multipart/form-data" class="space-y-3">
            <input type="hidden" name="settings_action" value="import_csv" />
            <div>
              <label class="label">Target dataset</label>
              <select class="select" name="dataset" id="csvDatasetSelect">
                <?php foreach ($availableCsvImportFormats as $datasetKey => $format): ?>
                  <option value="<?php echo h($datasetKey); ?>"><?php echo h($format['label']); ?></option>
                <?php endforeach; ?>
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
            <div class="rounded-md border border-gray-200 bg-gray-50 p-3 space-y-3">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <p class="text-sm font-semibold text-navy">CSV format</p>
                  <p class="text-xs text-gray-500">Match the selected dataset before uploading.</p>
                </div>
              </div>
              <?php foreach ($availableCsvImportFormats as $datasetKey => $format): ?>
                <div class="csv-format-panel space-y-3 <?php echo $datasetKey === $firstCsvFormatKey ? '' : 'hidden'; ?>" data-csv-format="<?php echo h($datasetKey); ?>">
                  <p class="text-sm text-gray-600"><?php echo h($format['summary']); ?></p>
                  <p class="text-xs text-gray-500"><span class="font-semibold text-gray-700">Required:</span> <?php echo h($format['required']); ?></p>
                  <div class="overflow-x-auto">
                    <table class="table text-xs bg-white">
                      <thead>
                        <tr>
                          <?php foreach ($format['headers'] as $header): ?>
                            <th><?php echo h($header); ?></th>
                          <?php endforeach; ?>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <?php foreach ($format['example'] as $value): ?>
                            <td><?php echo h($value); ?></td>
                          <?php endforeach; ?>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div class="flex flex-wrap items-center gap-2">
                    <a class="btn btn-outline btn-sm" href="<?php echo h($format['download']); ?>" download>Download example CSV</a>
                    <?php foreach (($format['extra_downloads'] ?? []) as $download): ?>
                      <a class="btn btn-outline btn-sm" href="<?php echo h($download['href']); ?>" download><?php echo h($download['label']); ?></a>
                    <?php endforeach; ?>
                    <?php foreach ($format['notes'] as $note): ?>
                      <span class="text-xs text-gray-500"><?php echo h($note); ?></span>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            <button class="btn btn-primary">Upload CSV</button>
          </form>
        </div>
      </section>

      <section class="card">
        <div class="card-header"><h2 class="font-semibold text-navy">System Notes</h2></div>
        <div class="card-body text-sm text-gray-600 space-y-3">
          <?php if ($noteSettingsError): ?>
            <p class="text-sm rounded-md border border-red-200 bg-red-50 text-red-700 px-3 py-2"><?php echo h($settingsError); ?></p>
          <?php endif; ?>
          <?php if ($notesUpdateSummary): ?>
            <p class="text-sm rounded-md border border-green-200 bg-green-50 text-green-700 px-3 py-2"><?php echo h($notesUpdateSummary); ?></p>
          <?php endif; ?>
          <?php if ($systemNotesRow): ?>
            <p class="text-xs text-gray-500">
              Last updated by <?php echo h($systemNotesRow['updated_by_name'] ?: 'System'); ?>
              on <?php echo h(substr($systemNotesRow['updated_at'], 0, 16)); ?>
            </p>
          <?php endif; ?>
          <?php if ($canManageUserRoles): ?>
            <form method="POST" action="../process/admin_settings.php" class="space-y-3">
              <input type="hidden" name="settings_action" value="save_notes" />
              <textarea class="input min-h-[9rem]" name="system_notes" maxlength="5000" placeholder="School-specific notes, IT contact details, or staff instructions"><?php echo h($systemNotes); ?></textarea>
              <div class="flex justify-end">
                <button class="btn btn-primary btn-sm">Save Notes</button>
              </div>
            </form>
          <?php else: ?>
            <div class="rounded-md border border-gray-200 bg-gray-50 p-3 whitespace-pre-wrap"><?php echo h($systemNotes !== '' ? $systemNotes : 'No system notes saved yet.'); ?></div>
          <?php endif; ?>
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

      <section class="card lg:col-span-2">
        <div class="card-header"><h2 class="font-semibold text-navy">Audit / Help</h2></div>
        <div class="card-body grid grid-cols-1 lg:grid-cols-[1fr_18rem] gap-4 text-sm text-gray-600">
          <div class="overflow-x-auto">
            <table class="table text-xs">
              <thead><tr><th>When</th><th>Actor</th><th>Action</th><th>Record</th><th>Details</th></tr></thead>
              <tbody>
                <?php if (!$auditRows): ?>
                  <tr><td colspan="5"><div class="empty text-sm"><p class="font-medium text-gray-700">No audit entries recorded yet.</p></div></td></tr>
                <?php else: foreach ($auditRows as $audit):
                  $auditRecord = trim(($audit['table_name'] ?? '') . (($audit['record_id'] ?? '') !== '' ? ' #' . $audit['record_id'] : ''));
                  $auditDetails = settings_audit_detail_text($audit['details'] ?? '');
                ?>
                  <tr>
                    <td class="whitespace-nowrap"><?php echo h(substr($audit['created_at'], 0, 16)); ?></td>
                    <td><?php echo h($audit['actor_name']); ?></td>
                    <td><?php echo h(settings_audit_action_label($audit['action_type'])); ?></td>
                    <td><?php echo h($auditRecord !== '' ? $auditRecord : 'System'); ?></td>
                    <td class="max-w-md break-words"><?php echo h($auditDetails !== '' ? $auditDetails : 'No details'); ?></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
          <div class="rounded-md border border-gray-200 bg-gray-50 p-3 space-y-3">
            <h3 class="text-sm font-semibold text-navy">Settings Help</h3>
            <ul class="list-disc list-inside space-y-2 text-xs text-gray-600">
              <li>CSV imports use the selected dataset format and the downloadable examples above.</li>
              <li>System Notes are shared on this settings page and editable by administrators.</li>
              <li>Audit rows show recent saved changes, imports, and account actions.</li>
              <li>Reference data cannot be deleted while records still depend on it.</li>
            </ul>
          </div>
        </div>
      </section>
    </main>

  </div>
  <script src="../js/shared.js"></script>
  <script>
    const csvDatasetSelect = document.getElementById('csvDatasetSelect');
    if (csvDatasetSelect) {
      const syncCsvFormat = () => {
        document.querySelectorAll('[data-csv-format]').forEach((panel) => {
          panel.classList.toggle('hidden', panel.dataset.csvFormat !== csvDatasetSelect.value);
        });
      };

      csvDatasetSelect.addEventListener('change', syncCsvFormat);
      syncCsvFormat();
    }
  </script>
</body>
</html>
