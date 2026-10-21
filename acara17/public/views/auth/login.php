<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <h1 class="h3 mb-4">Login</h1>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>
    <form method="POST" action="<?= BASE_URL ?>/login" class="bg-white border rounded p-4">
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" required autocomplete="username" value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn-primary">Masuk</button>
    </form>
  </div>
</div>