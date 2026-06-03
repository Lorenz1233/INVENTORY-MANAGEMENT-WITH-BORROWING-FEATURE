<?php
$registerError = $_GET['error'] ?? '';
$registerMessages = [
    'missing' => 'Please complete all required fields.',
    'password_mismatch' => 'Passwords do not match.',
    'weak_password' => 'Password must be at least 6 characters.',
    'duplicate' => 'An account already exists for that ID number.',
    'failed' => 'The account request could not be submitted.',
];
$registerMessage = $registerMessages[$registerError] ?? '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Create Account - MSU-MCEST</title>
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
        <h1 class="text-3xl font-semibold leading-tight">Request access.</h1>
        <p class="mt-3 text-white/70 text-sm max-w-sm">
          Student and faculty accounts created here require administrator approval before sign-in.
        </p>
      </div>
      <p class="text-xs text-white/50">&copy; <span id="year"></span> MSU-MCEST.</p>
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
            <h2 class="text-xl font-semibold text-navy">Create account</h2>
            <p class="text-sm text-gray-500 mt-1">Submit your account request for administrator review.</p>

            <div class="<?php echo $registerError ? '' : 'hidden '; ?>mt-4 text-sm bg-red-50 text-red-700 border border-red-200 rounded-md px-3 py-2">
              <?php echo htmlspecialchars($registerMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>

            <form method="POST" action="../process/register.php" class="mt-5 space-y-4">
              <div>
                <label class="label" for="id_number">ID number</label>
                <input class="input" type="text" id="id_number" name="id_number" required />
              </div>
              <div>
                <label class="label" for="full_name">Full name</label>
                <input class="input" type="text" id="full_name" name="full_name" required />
              </div>
              <div>
                <label class="label" for="role">Account type</label>
                <select class="select" id="role" name="role" required>
                  <option value="student">Student</option>
                  <option value="faculty">Faculty</option>
                </select>
              </div>
              <div>
                <label class="label" for="password">Password</label>
                <input class="input" type="password" id="password" name="password" minlength="6" required />
              </div>
              <div>
                <label class="label" for="confirm_password">Confirm password</label>
                <input class="input" type="password" id="confirm_password" name="confirm_password" minlength="6" required />
              </div>
              <button type="submit" class="btn btn-primary w-full">Submit Account Request</button>
            </form>

            <p class="mt-5 text-sm text-gray-500 text-center">
              Already have an account?
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
