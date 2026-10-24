<?php
/** @var bool $isEditing */
/** @var int|null $editingId */
/** @var array<string, mixed> $formData */
/** @var array<int, string> $errors */
/** @var array<int, array<string, mixed>> $prodi */
?>
<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="mb-4">
      <a href="<?= BASE_URL ?>/matakuliah" class="text-decoration-none">&larr; Kembali ke daftar</a>
      <h1 class="h2 mt-3 mb-1"><?= $isEditing ? 'Edit Mata Kuliah' : 'Tambah Mata Kuliah' ?></h1>
    </div>
    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form action="<?= $isEditing ? BASE_URL . '/matakuliah/' . (int) $editingId . '/update' : BASE_URL . '/matakuliah' ?>" method="post" class="card border-0 shadow-sm">
      <div class="card-body p-4 p-md-5">
        <div class="mb-3">
          <label for="kode" class="form-label">Kode</label>
          <input type="text" class="form-control" id="kode" name="kode" value="<?= htmlspecialchars($formData['kode'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="10" required>
        </div>
        <div class="mb-3">
          <label for="nama" class="form-label">Nama Mata Kuliah</label>
          <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($formData['nama'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="150" required>
        </div>
        <div class="mb-3">
          <label for="sks" class="form-label">SKS</label>
          <input type="number" class="form-control" id="sks" name="sks" value="<?= htmlspecialchars((string) ($formData['sks'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" min="1" max="24" required>
        </div>
        <div class="mb-4">
          <label for="prodi_id" class="form-label">Program Studi</label>
          <select class="form-select" id="prodi_id" name="prodi_id" required>
            <option value="" disabled <?= empty($formData['prodi_id'] ?? $formData['prodiId'] ?? null) ? 'selected' : '' ?>>Pilih program studi</option>
            <?php foreach ($prodi as $item): ?>
              <option value="<?= (int) $item['id'] ?>" <?= (int) ($formData['prodi_id'] ?? $formData['prodiId'] ?? 0) === (int) $item['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($item['nama'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($item['kode'], ENT_QUOTES, 'UTF-8') ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="d-flex justify-content-end gap-2">
          <a href="<?= BASE_URL ?>/matakuliah" class="btn btn-light border">Batal</a>
          <button type="submit" class="btn btn-primary"><?= $isEditing ? 'Simpan Perubahan' : 'Simpan Mata Kuliah' ?></button>
        </div>
      </div>
    </form>
  </div>
</div>