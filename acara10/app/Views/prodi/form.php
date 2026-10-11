<?php
/** @var bool $isEditing */
/** @var int|null $editingId */
/** @var array<string, mixed> $formData */
/** @var array<int, string> $errors */
?>
<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="mb-4">
      <a href="<?= BASE_URL ?>/prodi" class="text-decoration-none">&larr; Kembali ke daftar</a>
      <h1 class="h2 mt-3 mb-1"><?= $isEditing ? 'Edit Program Studi' : 'Tambah Program Studi' ?></h1>
    </div>
    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form action="<?= $isEditing ? BASE_URL . '/prodi/' . (int) $editingId . '/update' : BASE_URL . '/prodi' ?>" method="post" class="card border-0 shadow-sm">
      <div class="card-body p-4 p-md-5">
        <div class="mb-3">
          <label for="kode" class="form-label">Kode</label>
          <input type="text" class="form-control" id="kode" name="kode" value="<?= htmlspecialchars($formData['kode'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="10" required>
        </div>
        <div class="mb-4">
          <label for="nama" class="form-label">Nama Program Studi</label>
          <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($formData['nama'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="100" required>
        </div>
        <div class="d-flex justify-content-end gap-2">
          <a href="<?= BASE_URL ?>/prodi" class="btn btn-light border">Batal</a>
          <button type="submit" class="btn btn-primary"><?= $isEditing ? 'Simpan Perubahan' : 'Simpan Program Studi' ?></button>
        </div>
      </div>
    </form>
  </div>
</div>