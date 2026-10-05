<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1">Dashboard</h1>
    <p class="text-secondary mb-0">Selamat datang, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?>.</p>
  </div>
  <a class="btn btn-primary" href="<?= BASE_URL ?>/mahasiswa">Kelola Mahasiswa</a>
</div>
<section class="bg-white border rounded p-4">
  <h2 class="h5">SI Akademik</h2>
  <p class="mb-0">Anda berhasil masuk ke area yang dilindungi middleware autentikasi.</p>
</section>