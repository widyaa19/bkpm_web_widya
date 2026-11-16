<?php
/** @var array<int, array<string, mixed>> $matakuliah */
/** @var int $pages */
/** @var int $page */
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <p class="text-primary fw-semibold mb-1">DATA AKADEMIK</p>
    <h1 class="h2 mb-1">Mata Kuliah</h1>
    <p class="text-secondary mb-0">Kelola mata kuliah dan program studinya.</p>
  </div>
  <a href="<?= BASE_URL ?>/matakuliah/create" class="btn btn-primary">+ Tambah Mata Kuliah</a>
</div>
<section class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-dark"><tr><th class="px-4">Kode</th><th>Nama Mata Kuliah</th><th>SKS</th><th>Program Studi</th><th class="text-end px-4">Aksi</th></tr></thead>
      <tbody>
        <?php if ($matakuliah === []): ?><tr><td colspan="5" class="text-center text-secondary py-4">Belum ada mata kuliah.</td></tr><?php endif; ?>
        <?php foreach ($matakuliah as $item): ?>
          <tr>
            <td class="px-4"><?= htmlspecialchars($item['kode'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($item['nama'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= (int) $item['sks'] ?></td>
            <td><?= htmlspecialchars($item['prodi'], ENT_QUOTES, 'UTF-8') ?></td>
            <td class="text-end px-4">
              <a href="<?= BASE_URL ?>/matakuliah/<?= (int) $item['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
              <form action="<?= BASE_URL ?>/matakuliah/<?= (int) $item['id'] ?>/delete" method="post" class="d-inline" onsubmit="return confirm('Hapus mata kuliah ini?')">
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
  <nav class="mt-3" aria-label="Navigasi halaman mata kuliah">
    <ul class="pagination justify-content-end">
      <?php for ($pageNumber = 1; $pageNumber <= $pages; $pageNumber++): ?>
        <li class="page-item <?= $pageNumber === $page ? 'active' : '' ?>"><a class="page-link" href="<?= BASE_URL ?>/matakuliah?page=<?= $pageNumber ?>"><?= $pageNumber ?></a></li>
      <?php endfor; ?>
    </ul>
  </nav>
<?php endif; ?>