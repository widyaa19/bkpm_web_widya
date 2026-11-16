async function muatDataMahasiswa() {
  const dashboard = document.querySelector('#dashboard-mahasiswa');
  if (!dashboard) {
    return;
  }

  const tabel = dashboard.querySelector('#tabel-mahasiswa');
  const tbody = tabel.querySelector('tbody');
  const status = dashboard.querySelector('#status-mahasiswa');
  const search = dashboard.querySelector('#cari-mahasiswa');
  const resultCount = dashboard.querySelector('#jumlah-hasil');
  const statTotal = dashboard.querySelector('#jumlah-mahasiswa');
  const statPrograms = dashboard.querySelector('#jumlah-prodi');
  const statActive = dashboard.querySelector('#jumlah-aktif');
  let mahasiswaList = [];

  function setStatus(message, state) {
    status.textContent = message;
    status.dataset.state = state;
    status.hidden = message === '';
  }

  function renderRows() {
    const query = search.value.trim().toLocaleLowerCase('id');
    const filtered = mahasiswaList.filter((mahasiswa) => {
      const searchableValues = [mahasiswa.nim, mahasiswa.nama, mahasiswa.prodi, mahasiswa.angkatan, mahasiswa.status];
      return searchableValues.some((value) => String(value ?? '').toLocaleLowerCase('id').includes(query));
    });

    tbody.replaceChildren();
    resultCount.textContent = `${filtered.length} dari ${mahasiswaList.length} mahasiswa`;

    if (filtered.length === 0) {
      const row = tbody.insertRow();
      const cell = row.insertCell();
      cell.colSpan = 5;
      cell.className = 'table-message';
      cell.textContent = mahasiswaList.length === 0
        ? 'Belum ada data'
        : 'Tidak ada mahasiswa yang cocok dengan pencarian.';
      return;
    }

    filtered.forEach((mahasiswa) => {
      const row = tbody.insertRow();
      [mahasiswa.nim, mahasiswa.nama, mahasiswa.prodi, mahasiswa.angkatan].forEach((value) => {
        const cell = row.insertCell();
        cell.textContent = value == null ? '-' : String(value);
      });

      const statusCell = row.insertCell();
      const badge = document.createElement('span');
      const normalizedStatus = String(mahasiswa.status ?? '').toLocaleLowerCase('id');
      badge.className = `status-badge status-${['aktif', 'cuti', 'lulus'].includes(normalizedStatus) ? normalizedStatus : 'other'}`;
      badge.textContent = mahasiswa.status == null || mahasiswa.status === '' ? 'Tidak diketahui' : String(mahasiswa.status);
      statusCell.append(badge);
    });
  }

  try {
    const response = await fetch(dashboard.dataset.apiUrl, {
      headers: { Accept: 'application/json' },
    });
    const result = await response.json();
    if (!response.ok || !result.success || !Array.isArray(result.data)) {
      throw new Error(result.message || 'Respons API tidak valid.');
    }

    mahasiswaList = result.data;
    statTotal.textContent = String(mahasiswaList.length);
    statPrograms.textContent = String(new Set(mahasiswaList.map((mahasiswa) => mahasiswa.prodi).filter(Boolean)).size);
    statActive.textContent = String(mahasiswaList.filter((mahasiswa) => String(mahasiswa.status ?? '').toLocaleLowerCase('id') === 'aktif').length);
    dashboard.setAttribute('aria-busy', 'false');
    setStatus('', 'success');
    renderRows();
    search.addEventListener('input', renderRows);
  } catch (error) {
    console.error('Gagal memuat data mahasiswa:', error);
    tbody.replaceChildren();
    const row = tbody.insertRow();
    const cell = row.insertCell();
    cell.colSpan = 5;
    cell.className = 'table-message';
    cell.textContent = 'Data mahasiswa gagal dimuat.';
    dashboard.setAttribute('aria-busy', 'false');
    setStatus(`Gagal memuat data mahasiswa: ${error.message}`, 'error');
    resultCount.textContent = 'Data tidak tersedia';
  }
}

document.addEventListener('DOMContentLoaded', muatDataMahasiswa);
