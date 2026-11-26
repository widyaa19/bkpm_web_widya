document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('#form-login');
  const errorMessage = document.querySelector('#login-error');
  const statusMessage = document.querySelector('#login-status');
  if (!form || !errorMessage || !statusMessage) {
    return;
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    errorMessage.hidden = true;
    statusMessage.textContent = 'Sedang masuk...';
    statusMessage.hidden = false;
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.textContent;
    submitButton.disabled = true;
    submitButton.textContent = 'Memproses...';

    const formData = new FormData(form);
    try {
      const response = await window.AppRequests.fetchWithRetry(form.action, {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          username: formData.get('username'),
          password: formData.get('password'),
        }),
      });
      const result = await response.json();
      if (!response.ok || !result.success || !result.data?.token || !result.data?.role) {
        throw new Error(result.message || 'Login gagal diproses.');
      }

      localStorage.setItem('token', result.data.token);
      localStorage.setItem('role', result.data.role);
      window.location.assign(`${form.action.replace(/\/login\/?$/, '')}/dashboard`);
    } catch (error) {
      errorMessage.textContent = error.message || 'Login gagal diproses. Periksa koneksi lalu coba kembali.';
      errorMessage.hidden = false;
      statusMessage.hidden = true;
    } finally {
      submitButton.disabled = false;
      submitButton.textContent = originalButtonText;
    }
  });
});
