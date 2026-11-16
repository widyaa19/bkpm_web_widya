# Acara 25 - Authentication, Authorization, dan Dashboard Admin

Proyek menggunakan PHP Native dengan pola MVC. Kode aplikasi `app/` (config, core, model, controller, dan service) berada di luar `public/`; folder tampilan berada di `public/views/` sesuai struktur praktikum. Seluruh URL aplikasi masuk melalui `public/index.php` dan diteruskan oleh `app/Core/Router.php`. Class ber-namespace `App\\` dimuat otomatis melalui `app/Core/autoload.php`. Login menggunakan session untuk halaman dan JWT untuk operasi API; dashboard mahasiswa memakai Fetch dan RBAC untuk fitur CRUD admin. View katalog prodi dan mata kuliah tidak digunakan pada acara ini.

## Persiapan

1. Jalankan Apache dan MySQL dari Laragon, serta pastikan `mod_rewrite` aktif.
2. Gunakan database `si_akademik` yang sudah ada. Jangan impor ulang SQL jika database berisi data yang ingin dipertahankan. Untuk database yang telah dibuat sebelumnya, pastikan tabel `users` memiliki kolom role yang mengizinkan `admin` dan `user`; jalankan migrasi di bagian **Role admin** di bawah jika perlu.
3. Pastikan kredensial di [`config/database.php`](./config/database.php) sesuai dengan konfigurasi MySQL.
4. Akses aplikasi melalui `http://localhost/bkpm_web/acara25/public`; bila memakai virtual host, arahkan document root ke folder `public/`.
5. Impor [`postman_collection.json`](./postman_collection.json) dan environment [`postman_environment_local.json`](./postman_environment_local.json) ke Postman. Environment [`postman_environment_staging.json`](./postman_environment_staging.json) memakai URL contoh `https://staging.example.com`; ganti dengan URL staging Anda sebelum digunakan.

## Registrasi dan login

User dapat membuat akun melalui halaman `GET /register`, yang dapat dibuka dari tautan di halaman login. Form registrasi meminta username, email, password, dan konfirmasi password. Form mengirim ke `POST /register`; setelah berhasil, user diarahkan ke halaman login. Endpoint yang sama juga menerima JSON berisi `username`, `email`, dan `password` untuk Postman. Email divalidasi sebelum disimpan, password disimpan sebagai hash, dan setiap akun diberi role `user`. Respons API sukses menggunakan `201 Created`; email tidak valid menghasilkan `422`, sedangkan username atau email yang sudah terdaftar menghasilkan `409`.

```json
{
  "username": "mahasiswa",
  "email": "mahasiswa@example.com",
  "password": "password123"
}
```

Setelah registrasi, login melalui halaman `/login` dengan username dan password yang sama. Login form mengirim JSON ke `POST /login`; server membuat session halaman sekaligus mengembalikan JWT. Respons token berisi `data.token` dan `data.role`; username atau password yang salah menghasilkan HTTP `401`.

### Role admin

Akun baru selalu memiliki role `user`. Untuk database lama, jalankan migrasi berikut sekali:

```sql
ALTER TABLE users MODIFY role ENUM('admin', 'user') NOT NULL DEFAULT 'user';
```

Setelah mendaftarkan akun yang akan dijadikan admin, ubah role akun tersebut:

```sql
UPDATE users SET role = 'admin' WHERE username = 'username_admin';
```

User hanya dapat melihat dashboard. Admin dapat menambah, mengubah, dan menghapus mahasiswa; endpoint API tetap memeriksa role token sehingga menyembunyikan tombol di browser bukan satu-satunya proteksi.

## Integrasi API dengan frontend

Setelah login, `/dashboard` menampilkan data mahasiswa dari `/api/mahasiswa`, termasuk pencarian, jumlah mahasiswa, jumlah program studi, dan jumlah mahasiswa aktif. Dashboard admin menyediakan form CRUD; permintaan tulis mengirim JWT dan dashboard user tidak menampilkan kontrol tersebut. Tombol Logout menghapus token dan role dari `localStorage`, kemudian mengakhiri session. JavaScript membaca envelope JSON `success` dan `data`, lalu memasukkan nilai ke tabel melalui DOM API. CORS untuk request yang melewati front controller dikonfigurasi di `public/index.php`; request preflight `OPTIONS` dijawab dengan status `204` tanpa mengaktifkan credential bersama origin wildcard.

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
| POST | `/register` | Mendaftarkan akun user |
| GET | `/register` | Menampilkan form registrasi user |
| POST | `/login` | Login API dengan JSON dan mendapatkan JWT; form login tetap menggunakan session |

Endpoint GET mahasiswa tetap publik. Endpoint POST, PUT, dan DELETE mahasiswa memerlukan header `Authorization: Bearer <token>` dari respons login API; token tidak ada, tidak valid, atau kedaluwarsa menghasilkan HTTP `401`.
Token JWT berlaku selama 5 menit sejak diterbitkan. Setelah token kedaluwarsa, login kembali untuk mendapatkan token baru.

POST dan PUT menerima object JSON dengan field `nim`, `nama`, `email`, `prodi_id`, dan `angkatan`. NIM pada endpoint API harus terdiri dari tepat 9 digit. Field `status` opsional (default `aktif`; nilai yang diizinkan: `aktif`, `cuti`, atau `lulus`). `prodi_id` harus mengacu ke ID pada tabel `prodi` yang sudah ada. PUT mengganti representasi lengkap, bukan partial update.

Semua response API menggunakan `App\Core\Response` dengan envelope `success`, `message`, `data`, `errors`, dan `timestamp`. `errors` selalu berupa array (kosong jika tidak ada kesalahan validasi). Status validasi menggunakan `422`; kesalahan request menggunakan `400`, dan status lainnya mengikuti hasil operasi (`200`, `201`, `404`, `405`, `409`, `415`, atau `500`). Response JSON menggunakan karakter Unicode dan garis miring tanpa escape. Set `APP_ENV=development` pada environment server untuk mengaktifkan format JSON rapi (`JSON_PRETTY_PRINT`); mode default adalah `production`.

Collection Postman mencakup pengujian CRUD dan kasus error (ID tidak ditemukan, NIM kosong, email tidak valid, serta JSON tidak lengkap). Jalankan POST berhasil sebelum GET/PUT/DELETE berdasarkan ID; request POST menyimpan ID hasilnya ke variable collection. Ambil screenshot hasil pengujian dari Postman setelah API dan database berjalan.

API buku menerima field `judul`, `penulis`, `penerbit`, `tahun_terbit`, dan `stok`. Jalankan bagian tabel buku dan seed contoh pada [`database.sql`](./database.sql) jika tabel belum ada. Dokumentasi request Thunder Client dan diagram alur MVC ada di [`LAPORAN_PRAKTIKUM.md`](./LAPORAN_PRAKTIKUM.md).
