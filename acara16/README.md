# Acara 16 - RESTful API Mahasiswa

Implementasi API menggunakan PHP, MySQL, dan JSON. Endpoint utama dilayani melalui router aplikasi di `public/index.php`.

## Persiapan

1. Jalankan MySQL dari Laragon.
2. Impor database yang Anda lampirkan (`si_akademik (6).sql`) melalui phpMyAdmin atau MySQL client. API memakai database `si_akademik` yang sama dan membaca tabel `mahasiswa` serta `prodi` beserta data yang sudah ada. [`database.sql`](./database.sql) adalah skema referensi yang disesuaikan dengan dump tersebut (termasuk `matakuliah`), untuk instalasi baru; jangan impor bila ingin mempertahankan database yang sudah ada.
3. Pastikan kredensial di [`config/database.php`](./config/database.php) sesuai dengan konfigurasi MySQL.
4. Akses aplikasi melalui URL Laragon Anda. URL bawaan di Postman adalah `http://localhost/bkpm_web/acara16/public`; sesuaikan variable `baseUrl` bila menggunakan virtual host.
5. Impor [`postman_collection.json`](./postman_collection.json) ke Postman.

## Endpoint

| Method | Endpoint | Fungsi |
| --- | --- | --- |
| GET | `/api/mahasiswa` | Mengambil seluruh mahasiswa |
| GET | `/api/mahasiswa/{id}` | Mengambil satu mahasiswa |
| POST | `/api/mahasiswa` | Menambahkan mahasiswa |
| PUT | `/api/mahasiswa/{id}` | Mengubah seluruh data mahasiswa |
| DELETE | `/api/mahasiswa/{id}` | Menghapus mahasiswa |

POST dan PUT menerima object JSON dengan field `nim`, `nama`, `email`, `prodi_id`, dan `angkatan`. Field `status` opsional (default `aktif`; nilai yang diizinkan: `aktif`, `cuti`, atau `lulus`). `prodi_id` harus mengacu ke ID pada tabel `prodi`; database terlampir sudah menyediakan ID 1, 2, dan 3. PUT mengganti representasi lengkap, bukan partial update.

Response menggunakan bentuk `{ "success": true|false, "message": "...", "data": ... }`. Error validasi menyertakan field `errors`. Status yang digunakan antara lain `200`, `201`, `400`, `404`, `405`, `415`, dan `500`.

Collection Postman mencakup pengujian CRUD dan kasus error (ID tidak ditemukan, NIM kosong, email tidak valid, serta JSON tidak lengkap). Jalankan POST berhasil sebelum GET/PUT/DELETE berdasarkan ID; request POST menyimpan ID hasilnya ke variable collection. Ambil screenshot hasil pengujian dari Postman setelah API dan database berjalan.
