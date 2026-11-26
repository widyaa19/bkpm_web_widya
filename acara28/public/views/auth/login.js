document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('#form-login');
  const errorMessage = document.querySelector('#login-error');
  if (!form || !errorMessage) {
    return;
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    errorMessage.hidden = true;

    const formData = new FormData(form);
    try {
      const response = await fetch(form.action, {
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
    }
  });
});
