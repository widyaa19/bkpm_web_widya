async function fetchWithRetry(input, init) {
  for (let attempt = 0; ; attempt += 1) {
    try {
      return await fetch(input, init);
    } catch (error) {
      if (!(error instanceof TypeError)) {
        throw error;
      }

      if (attempt >= 1) {
        throw new Error('Koneksi jaringan gagal setelah dicoba ulang. Periksa koneksi lalu coba kembali.');
      }

      await new Promise((resolve) => window.setTimeout(resolve, 300));
    }
  }
}

window.AppRequests = Object.freeze({ fetchWithRetry });
