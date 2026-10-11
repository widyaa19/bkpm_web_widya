<?php
/** @var array<int, array<string, mixed>> $prodi */
/** @var int $pages */
/** @var int $page */
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <p class="text-primary fw-semibold mb-1">DATA AKADEMIK</p>
    <h1 class="h2 mb-1">Program Studi</h1>
    <p class="text-secondary mb-0">Kelola daftar program studi.</p>
  </div>
  <a href="<?= BASE_URL ?>/prodi/create" class="btn btn-primary">+ Tambah Program Studi</a>
</div>
<section class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-dark"><tr><th class="px-4">Kode</th><th>Nama Program Studi</th><th class="text-end px-4">Aksi</th></tr></thead>
      <tbody>
        <?php if ($prodi === []): ?><tr><td colspan="3" class="text-center text-secondary py-4">Belum ada program studi.</td></tr><?php endif; ?>
        <?php foreach ($prodi as $item): ?>
          <tr>
            <td class="px-4"><?= htmlspecialchars($item['kode'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($item['nama'], ENT_QUOTES, 'UTF-8') ?></td>
            <td class="text-end px-4">
              <a href="<?= BASE_URL ?>/prodi/<?= (int) $item['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
              <form action="<?= BASE_URL ?>/prodi/<?= (int) $item['id'] ?>/delete" method="post" class="d-inline" onsubmit="return confirm('Hapus program studi ini? Data mahasiswa atau mata kuliah yang masih memakai prodi ini akan mencegah penghapusan.')">
                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php if ($pages > 1): ?>
  <nav class="mt-3" aria-label="Navigasi halaman program studi">
    <ul class="pagination justify-content-end">
      <?php for ($pageNumber = 1; $pageNumber <= $pages; $pageNumber++): ?>
        <li class="page-item <?= $pageNumber === $page ? 'active' : '' ?>"><a class="page-link" href="<?= BASE_URL ?>/prodi?page=<?= $pageNumber ?>"><?= $pageNumber ?></a></li>
      <?php endfor; ?>
    </ul>
  </nav>
<?php endif; ?>