  <?php
  /** @var string $search */
  /** @var array<int, array<string, mixed>> $mahasiswa */
  /** @var int $pages */
  /** @var int $page */
  ?>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
      <div>
        <p class="text-primary fw-semibold mb-1">DATA AKADEMIK</p>
        <h1 class="h2 mb-1">Daftar Mahasiswa</h1>
        <p class="text-secondary mb-0">Kelola data mahasiswa dalam satu tampilan.</p>
      </div>
      <a href="<?= BASE_URL ?>/mahasiswa/create" class="btn btn-primary">
        + Tambah Mahasiswa
      </a>
    </div>

    <form action="<?= BASE_URL ?>/mahasiswa" method="get" class="row g-2 mb-3">
      <div class="col-sm-8 col-md-5">
        <label for="search" class="visually-hidden">Cari nama atau NIM</label>
        <input type="search" class="form-control" id="search" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Cari nama atau NIM">
      </div>
      <div class="col-auto"><button type="submit" class="btn btn-outline-primary">Cari</button></div>
      <?php if ($search !== ''): ?><div class="col-auto"><a href="<?= BASE_URL ?>/mahasiswa" class="btn btn-light border">Reset</a></div><?php endif; ?>
    </form>

    <section class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
              <tr>
                <th class="px-4">NIM</th>
                <th>Nama</th>
                <th>Program Studi</th>
                <th>Angkatan</th>
                <th>Status</th>
                <th class="text-end px-4">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($mahasiswa === []): ?>
                <tr><td colspan="6" class="text-center text-secondary py-4">Data mahasiswa tidak ditemukan.</td></tr>
              <?php endif; ?>
              <?php foreach ($mahasiswa ?? [] as $item): ?>
                <tr>
                  <td class="px-4"><?= htmlspecialchars($item['nim'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($item['nama'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($item['prodi'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><span class="badge text-bg-light border"><?= htmlspecialchars((string) $item['angkatan'], ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td><?= htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="text-end px-4">
                    <a href="<?= BASE_URL ?>/mahasiswa/<?= (int) $item['id'] ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                    <a href="<?= BASE_URL ?>/mahasiswa/<?= (int) $item['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <form action="<?= BASE_URL ?>/mahasiswa/<?= (int) $item['id'] ?>/delete" method="post" class="d-inline" onsubmit="return confirm('Hapus data mahasiswa ini?')">
                      <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
    <?php if ($pages > 1): ?>
      <nav class="mt-3" aria-label="Navigasi halaman mahasiswa">
        <ul class="pagination justify-content-end">
          <?php for ($pageNumber = 1; $pageNumber <= $pages; $pageNumber++): ?>
            <li class="page-item <?= $pageNumber === $page ? 'active' : '' ?>">
              <a class="page-link" href="<?= BASE_URL ?>/mahasiswa?<?= htmlspecialchars(http_build_query(['search' => $search, 'page' => $pageNumber]), ENT_QUOTES, 'UTF-8') ?>"><?= $pageNumber ?></a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>
