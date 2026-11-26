<nav class="navbar navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-semibold" href="<?= htmlspecialchars(BASE_URL . '/dashboard', ENT_QUOTES, 'UTF-8') ?>">SI Akademik</a>
    <div class="d-flex align-items-center gap-3">
      <?php if (!empty($_SESSION['logged_in'])): ?>
        <a class="nav-link text-white" href="<?= htmlspecialchars(BASE_URL . '/dashboard', ENT_QUOTES, 'UTF-8') ?>">Mahasiswa</a>
        <a class="nav-link text-white" href="<?= htmlspecialchars(BASE_URL . '/logout', ENT_QUOTES, 'UTF-8') ?>">Logout</a>
      <?php else: ?>
        <a class="nav-link text-white" href="<?= htmlspecialchars(BASE_URL . '/login', ENT_QUOTES, 'UTF-8') ?>">Login</a>
      <?php endif; ?>
    </div>
  </div>
</nav>