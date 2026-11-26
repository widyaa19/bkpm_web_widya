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
  const createForm = dashboard.querySelector('#form-mahasiswa');
  const editForm = dashboard.querySelector('#form-edit-mahasiswa');
  const notification = dashboard.querySelector('#notifikasi-crud');
  const forms = [createForm, editForm];
  let selectedPhotoUrl = null;
  let mahasiswaList = [];
  let notificationTimer = null;

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

  function showNotification(message, state) {
    window.clearTimeout(notificationTimer);
    notification.textContent = message;
    notification.dataset.state = state;
    notification.setAttribute('role', state === 'error' ? 'alert' : 'status');
    notification.setAttribute('aria-live', state === 'error' ? 'assertive' : 'polite');
    notification.hidden = false;
    notificationTimer = window.setTimeout(() => {
      notification.hidden = true;
    }, 5000);
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

  function showPhotoPreview(form, file = null, filename = '') {
    const previewContainer = form.querySelector('[data-photo-preview-container]');
    const preview = form.querySelector('[data-photo-preview]');
    const previewLabel = form.querySelector('[data-photo-preview-label]');

    if (selectedPhotoUrl) {
      URL.revokeObjectURL(selectedPhotoUrl);
      selectedPhotoUrl = null;
    }

    if (file) {
      selectedPhotoUrl = URL.createObjectURL(file);
      preview.src = selectedPhotoUrl;
      preview.alt = `Pratinjau ${file.name}`;
      previewLabel.textContent = file.name;
      previewContainer.hidden = false;
      return;
    }

    if (filename) {
      preview.src = `${dashboard.dataset.uploadUrl}${encodeURIComponent(filename)}`;
      preview.alt = 'Foto mahasiswa saat ini';
      previewLabel.textContent = 'Foto saat ini';
      previewContainer.hidden = false;
      return;
    }

    preview.removeAttribute('src');
    preview.alt = '';
    previewLabel.textContent = '';
    previewContainer.hidden = true;
  }

  function setFormValues(form, mahasiswa = {}) {
    form.elements.namedItem('id').value = mahasiswa.id ?? '';
    form.elements.namedItem('nim').value = mahasiswa.nim ?? '';
    form.elements.namedItem('nama').value = mahasiswa.nama ?? '';
    form.elements.namedItem('email').value = mahasiswa.email ?? '';
    form.elements.namedItem('prodi_id').value = mahasiswa.prodi_id ?? '';
    form.elements.namedItem('angkatan').value = mahasiswa.angkatan ?? '';
    form.elements.namedItem('status').value = mahasiswa.status ?? 'aktif';
    form.elements.namedItem('foto').value = '';
    showPhotoPreview(form, null, mahasiswa.foto ?? '');
  }

  function validateForm(form) {
    const emptyField = Array.from(form.querySelectorAll('[required]'))
      .find((field) => !String(field.value).trim());

    if (emptyField) {
      const fieldLabel = emptyField.closest('label')?.firstChild?.textContent.trim() || emptyField.name;
      emptyField.focus();
      setStatus(`${fieldLabel} wajib diisi.`, 'error');
      showNotification(`${fieldLabel} wajib diisi.`, 'error');
      return false;
    }

    if (!form.checkValidity()) {
      form.reportValidity();
      setStatus('Periksa kembali format data yang dimasukkan.', 'error');
      showNotification('Periksa kembali format data yang dimasukkan.', 'error');
      return false;
    }

    return true;
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
      const errors = Array.isArray(result.errors)
        ? result.errors.flat().join(' ')
        : result.errors && typeof result.errors === 'object'
          ? Object.values(result.errors).flat().join(' ')
          : '';
      throw new Error([result.message, errors].filter(Boolean).join(' '));
    }

    return result;
  }

  function openForm(form, mahasiswa = {}) {
    forms.forEach((otherForm) => {
      otherForm.hidden = true;
    });
    setFormValues(form, mahasiswa);
    form.hidden = false;
    form.elements.namedItem('nim').focus();
  }

  dashboard.querySelector('#tambah-mahasiswa').addEventListener('click', () => {
    openForm(createForm);
  });

  forms.forEach((form) => {
    form.querySelector('[data-cancel-form]').addEventListener('click', () => {
      form.reset();
      form.hidden = true;
      showPhotoPreview(form);
    });

    const photoInput = form.elements.namedItem('foto');
    photoInput.addEventListener('change', () => {
      showPhotoPreview(form, photoInput.files[0] ?? null);
    });

    form.addEventListener('invalid', () => {
      const message = 'Periksa field wajib dan format data yang dimasukkan.';
      setStatus(message, 'error');
      showNotification(message, 'error');
    }, true);

    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      if (!isAdmin) {
        return;
      }
      if (!validateForm(form)) {
        return;
      }

      const formData = new FormData(form);
      const id = form === editForm ? formData.get('id') : null;
      formData.delete('id');
      let result;
      try {
        result = await sendMutation(
          id
            ? `${dashboard.dataset.apiUrl}?id=${encodeURIComponent(id)}`
            : dashboard.dataset.apiUrl,
          id ? 'PUT' : 'POST',
          formData
        );
      } catch (error) {
        const message = error.message || 'Data mahasiswa gagal disimpan.';
        setStatus(message, 'error');
        showNotification(message, 'error');
        return;
      }

      form.reset();
      form.hidden = true;
      showPhotoPreview(form);
      showNotification(
        result.message || `Data mahasiswa berhasil ${id ? 'diperbarui' : 'ditambahkan'}.`,
        'success'
      );
      try {
        await refreshMahasiswa();
      } catch (error) {
        console.error('Perubahan tersimpan, tetapi data mahasiswa gagal dimuat ulang:', error);
        setStatus('Perubahan tersimpan, tetapi daftar mahasiswa gagal diperbarui.', 'error');
      }
    });
  });

  async function muatDataUntukEdit(id) {
    try {
      const response = await fetch(
        `${dashboard.dataset.apiUrl}?id=${encodeURIComponent(id)}`,
        { headers: { Accept: 'application/json' } }
      );
      const result = await response.json();
      if (!response.ok || !result.success || !result.data) {
        throw new Error(result.message || 'Data mahasiswa gagal dimuat.');
      }

      openForm(editForm, result.data);
    } catch (error) {
      setStatus(error.message || 'Data mahasiswa gagal dimuat untuk diedit.', 'error');
    }
  }

  tbody.addEventListener('click', async (event) => {
    const button = event.target.closest('button[data-action]');
    if (!button || !isAdmin) {
      return;
    }

    const mahasiswa = mahasiswaList.find((item) => String(item.id) === button.dataset.id);
    if (button.dataset.action === 'edit') {
      await muatDataUntukEdit(button.dataset.id);
      return;
    }

    if (button.dataset.action === 'delete' && mahasiswa && window.confirm(`Hapus data ${mahasiswa.nama}?`)) {
      let result;
      try {
        result = await sendMutation(
          `${dashboard.dataset.apiUrl}?id=${encodeURIComponent(mahasiswa.id)}`,
          'DELETE'
        );
      } catch (error) {
        const message = error.message || 'Data mahasiswa gagal dihapus.';
        setStatus(message, 'error');
        showNotification(message, 'error');
        return;
      }

      showNotification(result.message || 'Data mahasiswa berhasil dihapus.', 'success');
      try {
        await refreshMahasiswa();
      } catch (error) {
        console.error('Data terhapus, tetapi daftar mahasiswa gagal dimuat ulang:', error);
        setStatus('Data terhapus, tetapi daftar mahasiswa gagal diperbarui.', 'error');
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
