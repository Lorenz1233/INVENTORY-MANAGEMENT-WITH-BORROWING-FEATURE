<?php
$error = $_GET['error'] ?? '';
$messages = [
    'missing' => 'Please complete all fields.',
    'password_match' => 'New passwords do not match.',
    'weak_password' => 'Password must be at least 6 characters.',
    'invalid_identity' => 'ID number and last name do not match an active account.',
    'reset_failed' => 'Password could not be reset. Please try again.',
];
$message = $messages[$error] ?? 'Password could not be reset. Please try again.';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Forgot Password • MSU-MCEST</title>
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
  <main class="page-fade min-h-screen grid md:grid-cols-2">
    <section class="hidden md:flex bg-navy text-white p-10 flex-col justify-between">
      <div class="flex items-center gap-3">
        <img class="brand-logo brand-logo-lg" src="../assets/images/logo.png" width="48" height="48" alt="MSU-MCEST logo" />
        <div>
          <div class="font-semibold">MSU-MCEST</div>
          <div class="text-xs text-white/60">Campus Equipment Management</div>
        </div>
      </div>
      <div>
        <h1 class="text-3xl font-semibold leading-tight">Reset access.</h1>
        <p class="mt-3 text-white/70 text-sm max-w-sm">
          Confirm your registered ID number and last name, then choose a new password.
        </p>
      </div>
      <p class="text-xs text-white/50">© <span id="year"></span> MSU-MCEST.</p>
    </section>

    <section class="flex items-center justify-center p-6 md:p-10">
      <div class="w-full max-w-md">
        <div class="md:hidden flex items-center gap-3 mb-6">
          <img class="brand-logo brand-logo-lg" src="../assets/images/logo.png" width="48" height="48" alt="MSU-MCEST logo" />
          <div>
            <div class="font-semibold text-navy">MSU-MCEST</div>
            <div class="text-xs text-gray-500">Equipment Management</div>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <h2 class="text-xl font-semibold text-navy">Forgot password</h2>
            <p class="text-sm text-gray-500 mt-1">Use the same ID number you use when signing in.</p>

            <div class="<?php echo $error ? '' : 'hidden '; ?>mt-4 text-sm bg-red-50 text-red-700 border border-red-200 rounded-md px-3 py-2">
              <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            </div>

            <form method="POST" action="../process/forgot_password.php" class="mt-5 space-y-4">
              <div>
                <label class="label" for="username">ID number</label>
                <input class="input" type="text" id="username" name="username" required />
              </div>
              <div>
                <label class="label" for="last_name">Registered last name</label>
                <input class="input" type="text" id="last_name" name="last_name" required />
              </div>
              <div>
                <label class="label" for="new_password">New password</label>
                <input class="input" type="password" id="new_password" name="new_password" minlength="6" required />
              </div>
              <div>
                <label class="label" for="confirm_password">Confirm new password</label>
                <input class="input" type="password" id="confirm_password" name="confirm_password" minlength="6" required />
              </div>
              <button type="submit" class="btn btn-primary w-full">Reset Password</button>
            </form>

            <p class="mt-5 text-sm text-gray-500 text-center">
              Remembered it?
              <a href="login.php" class="text-navy font-semibold hover:text-gold-dark">Sign in</a>
            </p>
          </div>
        </div>
      </div>
    </section>
  </main>

  <script src="../js/shared.js"></script>
  <script>
    document.getElementById('year').textContent = new Date().getFullYear();
  </script>
</body>
</html>
