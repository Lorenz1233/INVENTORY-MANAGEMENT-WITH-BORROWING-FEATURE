<?php
require_once __DIR__ . '/../partials/page-data.php';
require_borrower();

$passwordErrorCode = $_GET['error'] ?? '';
$passwordSuccessCode = $_GET['success'] ?? '';
$passwordErrorMessages = [
    'password_match' => 'New passwords do not match.',
    'weak_password' => 'Password must be at least 6 characters.',
    'current_password' => 'Current password is incorrect.',
    'password_update_failed' => 'Password could not be updated. Please try again.',
];
$passwordError = $passwordErrorMessages[$passwordErrorCode] ?? '';
$passwordSuccess = $passwordSuccessCode === 'password_updated' ? 'Password updated successfully.' : '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Change Password • MSU-MCEST</title>
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
    <a href="student-dashboard.php" class="nav-link">Dashboard</a>
    <p class="px-5 mt-3 first:mt-0 text-[11px] uppercase tracking-wider text-white/40 mb-2">Borrow</p>
    <a href="student-browse.php" class="nav-link">Browse Equipment</a>
    <a href="student-requests.php" class="nav-link">My Requests</a>
    <a href="student-change-password.php" class="nav-link active">Change Password</a>
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
          <h1 class="text-lg font-semibold text-navy leading-tight whitespace-nowrap">Change Password</h1>
          <p class="text-xs text-gray-500">Update your account password</p>
        </div>
        <div class="ml-auto flex items-center gap-2 flex-wrap justify-end"></div>
      </div>
    </header>

    <main class="p-6">
      <section class="card max-w-xl">
        <div class="card-body">
          <?php if ($passwordError): ?>
            <div class="mb-4 text-sm bg-red-50 text-red-700 border border-red-200 rounded-md px-3 py-2"><?php echo h($passwordError); ?></div>
          <?php endif; ?>
          <?php if ($passwordSuccess): ?>
            <div class="mb-4 text-sm bg-green-50 text-green-700 border border-green-200 rounded-md px-3 py-2"><?php echo h($passwordSuccess); ?></div>
          <?php endif; ?>
          <form method="POST" action="../process/change_password.php" class="space-y-4">
            <!-- PHP: include CSRF token -->
            <div><label class="label">Current password</label><input class="input" type="password" name="current_password" required /></div>
            <div><label class="label">New password</label><input class="input" type="password" name="new_password" required minlength="6" /></div>
            <div><label class="label">Confirm new password</label><input class="input" type="password" name="confirm_password" required minlength="6" /></div>
            <div class="text-xs text-gray-500">Use at least 6 characters. Avoid reusing old passwords.</div>
            <div class="flex justify-end"><button class="btn btn-primary">Update Password</button></div>
          </form>
        </div>
      </section>
    </main>

  </div>
  <script src="../js/shared.js"></script>
</body>
</html>
