<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <h1 class="h3 mb-4">Daftar User</h1>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>
    <form method="POST" action="<?= htmlspecialchars(BASE_URL . '/register', ENT_QUOTES, 'UTF-8') ?>" class="bg-white border rounded p-4">
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" maxlength="50" required autocomplete="username" value="<?= htmlspecialchars($formData['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" maxlength="100" required autocomplete="email" value="<?= htmlspecialchars($formData['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password">
      </div>
      <div class="mb-3">
        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
      </div>
      <button type="submit" class="btn btn-primary">Daftar</button>
      <p class="mt-3 mb-0">Sudah punya akun? <a href="<?= htmlspecialchars(BASE_URL . '/login', ENT_QUOTES, 'UTF-8') ?>">Masuk</a></p>
    </form>
  </div>
</div>
