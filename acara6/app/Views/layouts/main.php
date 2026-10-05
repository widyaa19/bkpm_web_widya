<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title ?? 'SI Akademik', ENT_QUOTES, 'UTF-8') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <?php include __DIR__ . '/../partials/navbar.php'; ?>

  <main class="container py-5">
    <?php if (!empty($_SESSION['flash'])): ?>
      <?php $flashType = in_array($_SESSION['flash_type'] ?? 'success', ['success', 'warning'], true) ? $_SESSION['flash_type'] ?? 'success' : 'success'; ?>
      <div class="alert alert-<?= $flashType ?>" role="alert">
        <?= htmlspecialchars($_SESSION['flash'], ENT_QUOTES, 'UTF-8') ?>
      </div>
      <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
    <?php endif; ?>
    <?php require $content ?? ''; ?>
  </main>

  <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>