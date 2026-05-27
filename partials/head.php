<?php /* Suggested PHP partial: <head> contents shared across pages.
   Include with: <?php include __DIR__ . '/../partials/head.php'; ?>  */ ?>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo htmlspecialchars($pageTitle ?? 'MSU-MCEST CEMS'); ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="<?php echo $assetsBase ?? ''; ?>/css/app.css" />
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          navy:       '#0B2545',
          'navy-dark':'#061A33',
          'navy-soft':'#13335E',
          gold:       '#D4A017',
          'gold-dark':'#B8860B',
          surface:    '#F7F8FB',
        },
        fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
      }
    }
  }
</script>
