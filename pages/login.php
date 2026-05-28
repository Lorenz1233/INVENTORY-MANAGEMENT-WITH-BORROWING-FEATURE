<?php
$loginError = $_GET['error'] ?? '';
$loginMessage = $loginError === 'missing'
    ? 'Please enter your username and password.'
    : ($loginError === 'login_required' ? 'Please sign in first.' : 'Invalid username or password.');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login • MSU-MCEST CEMS</title>
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
    <!-- Brand panel -->
    <section class="hidden md:flex bg-navy text-white p-10 flex-col justify-between">
      <div class="flex items-center gap-3">
        <img class="brand-logo brand-logo-lg" src="../assets/images/logo.png" width="48" height="48" alt="MSU-MCEST logo" />
        <div>
          <div class="font-semibold">MSU-MCEST</div>
          <div class="text-xs text-white/60">Campus Equipment Management</div>
        </div>
      </div>
      <div>
        <h1 class="text-3xl font-semibold leading-tight">Welcome back.</h1>
        <p class="mt-3 text-white/70 text-sm max-w-sm">
          Sign in to manage equipment borrowing, monitor transactions, and serve the MSU-MCEST campus community.
        </p>
      </div>
      <p class="text-xs text-white/50">© <span id="year"></span> MSU-MCEST. For administrative use.</p>
    </section>

    <!-- Form panel -->
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
            <h2 class="text-xl font-semibold text-navy">Sign in</h2>
            <p class="text-sm text-gray-500 mt-1">Use your MSU-MCEST account credentials.</p>

            <!-- Backend redirects back with ?error=... when login fails. -->
            <div id="loginError" class="<?php echo $loginError ? '' : 'hidden '; ?>mt-4 text-sm bg-red-50 text-red-700 border border-red-200 rounded-md px-3 py-2">
              <?php echo htmlspecialchars($loginMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>

            <form id="loginForm" method="POST" action="../process/login.php" class="mt-5 space-y-4">
              <div>
                <label class="label" for="username">Id number</label>
                <input class="input" type="text" id="username" name="username" required />
              </div>
              <div>
                <label class="label" for="password">Password</label>
                <input class="input" type="password" id="password" name="password" required />
              </div>
              <button type="submit" class="btn btn-primary w-full">Sign in</button>
            </form>

          </div>
        </div>

        <p class="mt-6 text-xs text-gray-400 text-center">
          Authentication is handled by the PHP backend.
        </p>
      </div>
    </section>
  </main>

  <script src="../js/shared.js"></script>
  <script>
    document.getElementById('year').textContent = new Date().getFullYear();
  </script>
</body>
</html>
