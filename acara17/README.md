# Acara 17 - API CRUD

Proyek menggunakan PHP Native dengan pola MVC. Kode aplikasi `app/` (config, core, model, controller, dan service) berada di luar `public/`; folder tampilan berada di `public/views/` sesuai struktur praktikum. Seluruh URL aplikasi masuk melalui `public/index.php` dan diteruskan oleh `app/Core/Router.php`. Class ber-namespace `App\\` dimuat otomatis melalui `app/Core/autoload.php`. Struktur dan fitur web yang sudah ada, termasuk autentikasi serta katalog program studi dan mata kuliah, tetap dipertahankan.

## Persiapan

1. Jalankan Apache dan MySQL dari Laragon, serta pastikan `mod_rewrite` aktif.
2. Gunakan database `si_akademik` yang sudah ada. Jangan impor ulang SQL jika database berisi data yang ingin dipertahankan.
3. Pastikan kredensial di [`config/database.php`](./config/database.php) sesuai dengan konfigurasi MySQL.
4. Akses aplikasi melalui `http://localhost/bkpm_web/acara17/public`; bila memakai virtual host, arahkan document root ke folder `public/`.
5. Impor [`postman_collection.json`](./postman_collection.json) ke Postman.

## Endpoint

| Method | Endpoint | Fungsi |
| --- | --- | --- |
| GET | `/api/mahasiswa` | Mengambil seluruh mahasiswa |
| GET | `/api/mahasiswa?id={id}` atau `/api/mahasiswa/{id}` | Mengambil satu mahasiswa |
| POST | `/api/mahasiswa` | Menambahkan mahasiswa |
| PUT | `/api/mahasiswa?id={id}` atau `/api/mahasiswa/{id}` | Mengubah seluruh data mahasiswa |
| DELETE | `/api/mahasiswa?id={id}` atau `/api/mahasiswa/{id}` | Menghapus mahasiswa |
| GET | `/api/buku` | Mengambil seluruh buku |
| GET | `/api/buku/{id}` atau `/api/buku?id={id}` | Mengambil satu buku |
| POST | `/api/buku` | Menambahkan buku |
| PUT | `/api/buku/{id}` atau `/api/buku?id={id}` | Mengubah data buku |
| DELETE | `/api/buku/{id}` atau `/api/buku?id={id}` | Menghapus buku |

POST dan PUT menerima object JSON dengan field `nim`, `nama`, `email`, `prodi_id`, dan `angkatan`. Field `status` opsional (default `aktif`; nilai yang diizinkan: `aktif`, `cuti`, atau `lulus`). `prodi_id` harus mengacu ke ID pada tabel `prodi` yang sudah ada. PUT mengganti representasi lengkap, bukan partial update.

Response menggunakan bentuk `{ "success": true|false, "message": "...", "data": ... }`. Error validasi menyertakan field `errors`. Status yang digunakan antara lain `200`, `201`, `400`, `404`, `405`, `415`, dan `500`.

Collection Postman mencakup pengujian CRUD dan kasus error (ID tidak ditemukan, NIM kosong, email tidak valid, serta JSON tidak lengkap). Jalankan POST berhasil sebelum GET/PUT/DELETE berdasarkan ID; request POST menyimpan ID hasilnya ke variable collection. Ambil screenshot hasil pengujian dari Postman setelah API dan database berjalan.

API buku menerima field `judul`, `penulis`, `penerbit`, `tahun_terbit`, dan `stok`. Jalankan bagian tabel buku dan seed contoh pada [`database.sql`](./database.sql) jika tabel belum ada. Dokumentasi request Thunder Client dan diagram alur MVC ada di [`LAPORAN_PRAKTIKUM.md`](./LAPORAN_PRAKTIKUM.md).
