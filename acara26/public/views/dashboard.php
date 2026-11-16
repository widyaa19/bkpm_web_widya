<section class="dashboard-shell" id="dashboard-mahasiswa"
  data-api-url="<?= htmlspecialchars(BASE_URL . '/api/mahasiswa', ENT_QUOTES, 'UTF-8') ?>"
  data-upload-url="<?= htmlspecialchars(BASE_URL . '/uploads/', ENT_QUOTES, 'UTF-8') ?>"
  data-role="<?= htmlspecialchars($_SESSION['user_role'] ?? 'user', ENT_QUOTES, 'UTF-8') ?>"
  data-logout-url="<?= htmlspecialchars(BASE_URL . '/logout', ENT_QUOTES, 'UTF-8') ?>"
  aria-busy="true">
  <header class="dashboard-heading">
    <div>
      <p class="dashboard-kicker">SISTEM INFORMASI AKADEMIK</p>
      <h1>Mahasiswa</h1>
      <p class="dashboard-greeting">Selamat datang, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User', ENT_QUOTES, 'UTF-8') ?>.</p>
      <a class="dashboard-logout" id="logout-dashboard" href="<?= htmlspecialchars(BASE_URL . '/logout', ENT_QUOTES, 'UTF-8') ?>">Logout</a>
    </div>
    <label class="search-field" for="cari-mahasiswa">
      <span>Cari mahasiswa</span>
      <input id="cari-mahasiswa" type="search" placeholder="Nama, NIM, atau program studi" autocomplete="off">
    </label>
  </header>

  <p id="status-mahasiswa" class="dashboard-status" role="status" aria-live="polite">Memuat data...</p>

  <section class="dashboard-stats" aria-label="Ringkasan mahasiswa">
    <article class="stat-item stat-total">
      <span class="stat-label">Jumlah mahasiswa</span>
      <strong id="jumlah-mahasiswa">-</strong>
      <span class="stat-note">Seluruh data terdaftar</span>
    </article>
    <article class="stat-item stat-program">
      <span class="stat-label">Program studi</span>
      <strong id="jumlah-prodi">-</strong>
      <span class="stat-note">Program studi terwakili</span>
    </article>
    <article class="stat-item stat-active">
      <span class="stat-label">Mahasiswa aktif</span>
      <strong id="jumlah-aktif">-</strong>
      <span class="stat-note">Status akademik aktif</span>
    </article>
  </section>

  <section class="student-section" aria-labelledby="judul-daftar-mahasiswa">
    <div class="student-section-heading">
      <div>
        <p class="section-kicker">DATA AKADEMIK</p>
        <h2 id="judul-daftar-mahasiswa">Daftar mahasiswa</h2>
      </div>
      <div class="student-actions">
        <button class="dashboard-button" id="tambah-mahasiswa" type="button" data-admin-only hidden>Tambah mahasiswa</button>
        <span id="jumlah-hasil" class="result-count" aria-live="polite">Menunggu data</span>
      </div>
    </div>
    <form id="form-mahasiswa" class="student-form" hidden>
      <h3 id="judul-form-mahasiswa">Tambah mahasiswa</h3>
      <input id="mahasiswa-id" name="id" type="hidden">
      <div class="student-form-grid">
        <label>NIM<input name="nim" required maxlength="9" pattern="[0-9]{9}" inputmode="numeric"></label>
        <label>Nama<input name="nama" required maxlength="100"></label>
        <label>Foto<input name="foto" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png"></label>
        <label>Email<input name="email" type="email" required maxlength="100"></label>
        <label>ID program studi<input name="prodi_id" type="number" min="1" required></label>
        <label>Angkatan<input name="angkatan" type="number" min="1900" max="2155" required></label>
        <label>Status
          <select name="status" required>
            <option value="aktif">Aktif</option>
            <option value="cuti">Cuti</option>
            <option value="lulus">Lulus</option>
          </select>
        </label>
      </div>
      <div class="student-form-actions">
        <button class="dashboard-button" type="submit">Simpan</button>
        <button class="dashboard-button dashboard-button-secondary" id="batal-form-mahasiswa" type="button">Batal</button>
      </div>
    </form>
    <div class="table-wrap">
      <table id="tabel-mahasiswa">
        <thead>
          <tr>
            <th scope="col">NIM</th>
            <th scope="col">Nama</th>
            <th scope="col">Foto</th>
            <th scope="col">Program studi</th>
            <th scope="col">Angkatan</th>
            <th scope="col">Status</th>
            <th scope="col" data-admin-only hidden>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr><td colspan="<?= ($_SESSION['user_role'] ?? 'user') === 'admin' ? '7' : '6' ?>" class="table-message">Menghubungkan ke data akademik...</td></tr>
        </tbody>
      </table>
    </div>
  </section>
</section>