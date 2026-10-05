<nav class="navbar navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-semibold" href="<?= BASE_URL ?>/dashboard">SI Akademik</a>
    <div class="d-flex align-items-center gap-3">
      <a class="nav-link text-white" href="<?= BASE_URL ?>/dashboard">Dashboard</a>
      <?php if (!empty($_SESSION['logged_in'])): ?>
        <a class="nav-link text-white" href="<?= BASE_URL ?>/mahasiswa">Mahasiswa</a>
        <a class="nav-link text-white" href="<?= BASE_URL ?>/logout">Logout</a>
      <?php else: ?>
        <a class="nav-link text-white" href="<?= BASE_URL ?>/login">Login</a>
      <?php endif; ?>
    </div>
  </div>
</nav>