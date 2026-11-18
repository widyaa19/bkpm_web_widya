async function muatDataMahasiswa() {
  const dashboard = document.querySelector('#dashboard-mahasiswa');
  if (!dashboard) {
    return;
  }

  const token = localStorage.getItem('token');
  if (!token) {
    window.location.replace(dashboard.dataset.logoutUrl);
    return;
  }

  const isAdmin = dashboard.dataset.role === 'admin' && localStorage.getItem('role') === 'admin';
  document.querySelectorAll('[data-admin-only]').forEach((element) => {
    element.hidden = !isAdmin;
  });

  const tabel = dashboard.querySelector('#tabel-mahasiswa');
  const tbody = tabel.querySelector('tbody');
  const status = dashboard.querySelector('#status-mahasiswa');
  const search = dashboard.querySelector('#cari-mahasiswa');
  const resultCount = dashboard.querySelector('#jumlah-hasil');
  const statTotal = dashboard.querySelector('#jumlah-mahasiswa');
  const statPrograms = dashboard.querySelector('#jumlah-prodi');
  const statActive = dashboard.querySelector('#jumlah-aktif');
  const form = dashboard.querySelector('#form-mahasiswa');
  const formTitle = dashboard.querySelector('#judul-form-mahasiswa');
  const photoInput = form.elements.namedItem('foto');
  const photoPreviewContainer = dashboard.querySelector('#foto-preview-container');
  const photoPreview = dashboard.querySelector('#foto-preview');
  const photoPreviewLabel = dashboard.querySelector('#foto-preview-label');
  let selectedPhotoUrl = null;
  let mahasiswaList = [];

  document.querySelectorAll('a[href$="/logout"]').forEach((logoutLink) => {
    logoutLink.addEventListener('click', () => {
      localStorage.removeItem('token');
      localStorage.removeItem('role');
    });
  });

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
      cell.colSpan = isAdmin ? 7 : 6;
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

      const photoCell = row.insertCell(2);
      if (mahasiswa.foto) {
        const photo = document.createElement('img');
        photo.src = `${dashboard.dataset.uploadUrl}${encodeURIComponent(mahasiswa.foto)}`;
        photo.alt = `Foto ${mahasiswa.nama}`;
        photo.width = 48;
        photo.height = 48;
        photoCell.append(photo);
      } else {
        photoCell.textContent = '-';
      }

      const statusCell = row.insertCell();
      const badge = document.createElement('span');
      const normalizedStatus = String(mahasiswa.status ?? '').toLocaleLowerCase('id');
      badge.className = `status-badge status-${['aktif', 'cuti', 'lulus'].includes(normalizedStatus) ? normalizedStatus : 'other'}`;
      badge.textContent = mahasiswa.status == null || mahasiswa.status === '' ? 'Tidak diketahui' : String(mahasiswa.status);
      statusCell.append(badge);

      if (isAdmin) {
        const actionCell = row.insertCell();
        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.className = 'dashboard-button dashboard-button-small';
        editButton.dataset.action = 'edit';
        editButton.dataset.id = String(mahasiswa.id);
        editButton.textContent = 'Ubah';
        actionCell.append(editButton);

        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.className = 'dashboard-button dashboard-button-small dashboard-button-danger';
        deleteButton.dataset.action = 'delete';
        deleteButton.dataset.id = String(mahasiswa.id);
        deleteButton.textContent = 'Hapus';
        actionCell.append(deleteButton);
      }
    });
  }

  function showPhotoPreview(file = null, filename = '') {
    if (selectedPhotoUrl) {
      URL.revokeObjectURL(selectedPhotoUrl);
      selectedPhotoUrl = null;
    }

    if (file) {
      selectedPhotoUrl = URL.createObjectURL(file);
      photoPreview.src = selectedPhotoUrl;
      photoPreview.alt = `Pratinjau ${file.name}`;
      photoPreviewLabel.textContent = file.name;
      photoPreviewContainer.hidden = false;
      return;
    }

    if (filename) {
      photoPreview.src = `${dashboard.dataset.uploadUrl}${encodeURIComponent(filename)}`;
      photoPreview.alt = 'Foto mahasiswa saat ini';
      photoPreviewLabel.textContent = 'Foto saat ini';
      photoPreviewContainer.hidden = false;
      return;
    }

    photoPreview.removeAttribute('src');
    photoPreview.alt = '';
    photoPreviewLabel.textContent = '';
    photoPreviewContainer.hidden = true;
  }

  function setFormValues(mahasiswa = {}) {
    form.elements.namedItem('id').value = mahasiswa.id ?? '';
    form.elements.namedItem('nim').value = mahasiswa.nim ?? '';
    form.elements.namedItem('nama').value = mahasiswa.nama ?? '';
    form.elements.namedItem('email').value = mahasiswa.email ?? '';
    form.elements.namedItem('prodi_id').value = mahasiswa.prodi_id ?? '';
    form.elements.namedItem('angkatan').value = mahasiswa.angkatan ?? '';
    form.elements.namedItem('status').value = mahasiswa.status ?? 'aktif';
    photoInput.value = '';
    showPhotoPreview(null, mahasiswa.foto ?? '');
  }

  async function sendMutation(url, method, data) {
    const isFormData = data instanceof FormData;
    const response = await fetch(url, {
      method,
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
        ...(data && !isFormData ? { 'Content-Type': 'application/json' } : {}),
      },
      ...(data ? { body: isFormData ? data : JSON.stringify(data) } : {}),
    });
    const result = await response.json();
    if (!response.ok || !result.success) {
      const errors = Array.isArray(result.errors) ? result.errors.flat().join(' ') : '';
      throw new Error([result.message, errors].filter(Boolean).join(' '));
    }

    return result;
  }

  dashboard.querySelector('#tambah-mahasiswa').addEventListener('click', () => {
    setFormValues();
    formTitle.textContent = 'Tambah mahasiswa';
    form.hidden = false;
    form.elements.namedItem('nim').focus();
  });

  dashboard.querySelector('#batal-form-mahasiswa').addEventListener('click', () => {
    form.reset();
    form.hidden = true;
    showPhotoPreview();
  });

  photoInput.addEventListener('change', () => {
    showPhotoPreview(photoInput.files[0] ?? null);
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!isAdmin) {
      return;
    }

    const formData = new FormData(form);
    const id = formData.get('id');
    formData.delete('id');
    try {
      const result = await sendMutation(
        id ? `${dashboard.dataset.apiUrl}/${encodeURIComponent(id)}` : dashboard.dataset.apiUrl,
        'POST',
        formData
      );
      form.reset();
      form.hidden = true;
      showPhotoPreview();
      await refreshMahasiswa();
      setStatus(result.message, 'success');
    } catch (error) {
      setStatus(error.message || 'Data mahasiswa gagal disimpan.', 'error');
    }
  });

  tbody.addEventListener('click', async (event) => {
    const button = event.target.closest('button[data-action]');
    if (!button || !isAdmin) {
      return;
    }

    const mahasiswa = mahasiswaList.find((item) => String(item.id) === button.dataset.id);
    if (button.dataset.action === 'edit' && mahasiswa) {
      setFormValues(mahasiswa);
      formTitle.textContent = 'Ubah mahasiswa';
      form.hidden = false;
      form.elements.namedItem('nim').focus();
      return;
    }

    if (button.dataset.action === 'delete' && mahasiswa && window.confirm(`Hapus data ${mahasiswa.nama}?`)) {
      try {
        const result = await sendMutation(
          `${dashboard.dataset.apiUrl}/${encodeURIComponent(mahasiswa.id)}`,
          'DELETE'
        );
        await refreshMahasiswa();
        setStatus(result.message, 'success');
      } catch (error) {
        setStatus(error.message || 'Data mahasiswa gagal dihapus.', 'error');
      }
    }
  });

  async function refreshMahasiswa() {
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
  }

  try {
    await refreshMahasiswa();
    search.addEventListener('input', renderRows);
  } catch (error) {
    console.error('Gagal memuat data mahasiswa:', error);
    tbody.replaceChildren();
    const row = tbody.insertRow();
    const cell = row.insertCell();
    cell.colSpan = isAdmin ? 7 : 6;
    cell.className = 'table-message';
    cell.textContent = 'Data mahasiswa gagal dimuat.';
    dashboard.setAttribute('aria-busy', 'false');
    setStatus(`Gagal memuat data mahasiswa: ${error.message}`, 'error');
    resultCount.textContent = 'Data tidak tersedia';
  }
}

document.addEventListener('DOMContentLoaded', muatDataMahasiswa);
