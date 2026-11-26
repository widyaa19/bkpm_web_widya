# Laporan Praktikum — API CRUD Buku

## Konfigurasi Thunder Client

Jalankan Apache dan MySQL Laragon. Pastikan database `si_akademik` sudah memiliki tabel `buku`; jika belum, jalankan bagian `CREATE TABLE buku` dan `INSERT IGNORE INTO buku` dari `database.sql`.

Gunakan URL dasar:

```text
http://localhost/bkpm_web/acara19/public/api/buku
```

Untuk request POST dan PUT, pilih **Body → JSON** pada Thunder Client. Thunder Client akan mengirim `Content-Type: application/json`. Untuk operasi yang memerlukan ID, ganti `{id}` dengan ID buku dari respons GET atau POST.

## Endpoint dan pengujian

### GET — Daftar buku

```text
GET http://localhost/bkpm_web/acara19/public/api/buku
```

Contoh respons `200 OK`:

```json
{
  "success": true,
  "message": "Data buku berhasil diambil.",
  "data": [
    {
      "id": 1,
      "judul": "Pemrograman Web dengan PHP",
      "penulis": "Andi Wijaya",
      "penerbit": "Informatika",
      "tahun_terbit": 2025,
      "stok": 10
    }
  ]
}
```

### GET — Satu buku

```text
GET http://localhost/bkpm_web/acara19/public/api/buku/1
```

Format query string juga didukung: `GET http://localhost/bkpm_web/acara19/public/api/buku?id=1`.

### POST — Menambahkan buku

```text
POST http://localhost/bkpm_web/acara19/public/api/buku
```

Contoh body JSON:

```json
{
  "judul": "Belajar REST API",
  "penulis": "Budi Santoso",
  "penerbit": "Andi Publisher",
  "tahun_terbit": 2026,
  "stok": 12
}
```

Respons berhasil menggunakan status `201 Created` dan mengembalikan data buku yang baru ditambahkan.

### PUT — Memperbarui buku

```text
PUT http://localhost/bkpm_web/acara19/public/api/buku/1
```

Gunakan body JSON yang sama seperti POST dengan seluruh field terisi. Respons berhasil menggunakan status `200 OK`.

### DELETE — Menghapus buku

```text
DELETE http://localhost/bkpm_web/acara19/public/api/buku/1
```

Respons berhasil menggunakan status `200 OK`. GET pada ID tersebut setelah penghapusan akan mengembalikan `404 Not Found`.

Field `judul`, `penulis`, dan `penerbit` wajib diisi. `tahun_terbit` harus angka antara 1900 dan 2155; `stok` harus bilangan bulat nol atau lebih. Kesalahan validasi mengembalikan `400 Bad Request`, sementara request JSON tanpa `Content-Type: application/json` mengembalikan `415 Unsupported Media Type`.

## Contoh tiga data awal

Skema dan tiga data contoh buku disediakan pada `database.sql`. Data contoh tersebut adalah:

1. Pemrograman Web dengan PHP — Andi Wijaya — Informatika — 2025 — stok 10.
2. Dasar-Dasar MySQL — Siti Rahma — Elex Media Komputindo — 2024 — stok 8.
3. Belajar REST API — Budi Santoso — Andi Publisher — 2026 — stok 12.

Query seed memakai `INSERT IGNORE` dengan indeks unik judul dan penerbit, sehingga dapat dijalankan ulang tanpa menggandakan ketiga baris tersebut.

## Validasi NIM mahasiswa

Pemeriksaan NIM duplikat dilakukan saat validasi request dan ditegakkan kembali oleh `MahasiswaRepository` sebelum INSERT/UPDATE menggunakan `existsByNim()`. Kolom NIM pada database juga memiliki indeks unik sebagai perlindungan tambahan. Ketika NIM sudah digunakan, endpoint mahasiswa menolak request dengan status `400 Bad Request`.

## Diagram alur

```text
Thunder Client
      |
      | HTTP request: method, URL, JSON
      v
public/index.php (Front Controller)
      |
      v
app/Core/Router.php
      |
      v
app/Controllers/BukuController.php
      |
      v
app/Models/Buku.php
      |
      | PDO prepared statement
      v
MySQL: si_akademik.buku
      |
      | hasil query
      v
BukuController — JSON response + status HTTP
      |
      v
Thunder Client
```

Ambil screenshot setiap request dan response Thunder Client sebagai bukti praktikum. Jalankan POST sebelum GET/PUT/DELETE pada ID yang dibuat, atau gunakan tiga data seed sebagai data GET.

## Acara 21 — Dokumentasi Endpoint dengan Thunder Client

Dokumentasi kontrak endpoint, controller, autentikasi, request body, dan contoh response sukses/gagal tersedia pada [`docs/API_DOCUMENTATION.md`](./docs/API_DOCUMENTATION.md). Peta endpoint API mencakup autentikasi, CRUD mahasiswa, dan CRUD buku.

### Request dan controller

| Request Thunder Client | Controller dan method |
| --- | --- |
| `POST /register` | `AuthController::register()` |
| `POST /login` | `AuthController::login()` |
| `GET /api/mahasiswa` dan `GET /api/mahasiswa?id={id}` | `ApiMahasiswaController::index()` |
| `GET /api/mahasiswa/{id}` | `ApiMahasiswaController::show()` |
| `POST /api/mahasiswa` | `ApiMahasiswaController::store()` |
| `PUT /api/mahasiswa/{id}` atau `?id={id}` | `ApiMahasiswaController::update()` |
| `DELETE /api/mahasiswa/{id}` atau `?id={id}` | `ApiMahasiswaController::destroy()` |
| `GET /api/buku` | `BukuController::index()` |
| `GET /api/buku/{id}` | `BukuController::show()` |
| `POST /api/buku` | `BukuController::store()` |
| `PUT /api/buku/{id}` | `BukuController::update()` |
| `DELETE /api/buku/{id}` | `BukuController::destroy()` |

### Langkah dokumentasi dan pengujian

1. Buat collection **Acara 21 - Dokumentasi API** di Thunder Client.
2. Buat/simpan request sesuai tabel di atas. Gunakan base URL `http://localhost/bkpm_web/acara21/public`, lalu tambahkan path endpoint. Beri nama request dengan method dan endpoint agar mudah dicari.
3. Untuk masing-masing request, gunakan penjelasan `Controller::method` pada tabel dan dokumentasi API. Request dengan body menggunakan **Body → JSON**.
4. Jalankan `POST /register`, lalu `POST /login`. Salin `data.token` dari response login dan pilih **Auth → Bearer** pada request POST, PUT, dan DELETE mahasiswa. Token berlaku lima menit.
5. Jalankan request untuk memperoleh response aktual. Simpan bukti dengan screenshot request dan response sukses/gagal, lalu lampirkan pada laporan. Contoh response dan status HTTP yang diharapkan ada di [`docs/API_DOCUMENTATION.md`](./docs/API_DOCUMENTATION.md).

Thunder Client tidak mengikuti fitur Postman **Examples → View Documentation** pada prosedur praktikum. Karena itu, dokumentasi endpoint tertulis dan screenshot Thunder Client menjadi lampiran pengganti; jangan mencantumkan URL View Documentation yang tidak dibuat.

## Tugas Praktik Mandiri — Foto dan backup basis data

### Penghapusan foto lama

Pada alur dashboard di proyek ini, proses pembaruan foto dijalankan oleh `ApiMahasiswaController::update()`, bukan `MahasiswaController::update()`. Setelah data berhasil diperbarui dan foto baru diunggah, foto lama dihapus melalui `removePhoto()` dengan `unlink()`. Jika upload atau validasi gagal, foto lama tetap dipertahankan.

### Jadwal backup

Lakukan backup basis data `si_akademik` pada akhir **setiap sesi praktikum**, sebelum menghentikan MySQL. Di phpMyAdmin, pilih database `si_akademik` → **Export** → **Quick** → format **SQL** → **Export**. Simpan hasilnya dengan nama bertanggal, misalnya `si_akademik_2026-11-17.sql`, di folder backup lokal di luar `public/`. Setelah ekspor, pastikan berkas SQL tersimpan dan ukurannya tidak nol.

## Acara 30 — Deployment Produksi dan Serah Terima

Tahap akhir ini memperkuat deployment hasil Acara 28 dan menutup proyek MVC
Sistem Informasi Akademik. Fondasi Acara 17 berupa pemisahan MVC dan Front
Controller menjadi dasar pengembangan endpoint API CRUD. Tahap-tahap berikutnya
menambahkan model dan controller untuk data akademik, validasi serta
dokumentasi/pengujian API; Acara 28 melengkapi autentikasi, JWT, RBAC, dan
dashboard admin. Pada Acara 30, aplikasi disiapkan untuk produksi melalui
HTTPS, konfigurasi error PHP yang aman, penghalangan akses langsung ke kode
aplikasi, dan pengujian ulang sebelum serah terima.

### Checklist deployment cPanel

- [ ] Di **SSL/TLS Status**, pilih domain proyek dan jalankan **Run AutoSSL**.
- [ ] Pastikan sertifikat aktif, kemudian buka aplikasi dengan
  `https://alwiyaa.my.id/Widya/public` dan verifikasi indikator koneksi aman.
- [ ] Aktifkan **Force HTTPS Redirect** di menu **Domains**, atau gunakan
  aturan pada [`public/.htaccess`](./public/.htaccess). Aturan berkas ini
  mengalihkan host produksi `alwiyaa.my.id` (termasuk `www`) ke HTTPS tanpa
  mengubah akses localhost selama pengembangan.
- [ ] Di **MultiPHP INI Editor**, pilih domain dan atur `display_errors = Off`
  serta `log_errors = On`. Atur `error_log` ke lokasi privat yang dapat ditulis
  PHP, misalnya `/home/CPANEL_USER/logs/php_errors.log`; ganti `CPANEL_USER`
  dengan nama akun cPanel sebenarnya dan pastikan direktori tersedia di luar
  document root.
- [ ] Pertahankan document root pada folder `public/`; berkas
  [`app/.htaccess`](./app/.htaccess) juga menolak akses HTTP langsung ke
  direktori aplikasi sebagai lapisan tambahan.
- [ ] Jalankan ulang semua 20 skenario pada [`docs/TEST_CASE.md`](./docs/TEST_CASE.md)
  terhadap environment produksi. Catat status dan hasil aktual, cek bahwa URL
  akhir tiap request memakai HTTPS, dan simpan bukti tanpa token/password.
- [ ] Periksa bahwa base URL produksi di
  [`docs/API_DOCUMENTATION.md`](./docs/API_DOCUMENTATION.md) dan
  [`docs/openapi.yaml`](./docs/openapi.yaml) adalah
  `https://alwiyaa.my.id/Widya/public`.
- [ ] Serahkan URL produksi, dokumentasi endpoint, hasil test case, serta
  informasi konfigurasi yang diperlukan secara aman. Jangan sertakan kredensial,
  JWT, atau rahasia konfigurasi di laporan.

### Ringkasan struktur MVC akhir

```text
Sistem Informasi Akademik
├── app/                         (kode aplikasi, bukan document root)
│   ├── Controllers/             (Auth, Home, CRUD, API)
│   ├── Models/                  (entitas dan akses data)
│   ├── Services/                (logika layanan)
│   ├── Core/                    (Router, Response, Auth, Database, autoload)
│   └── .htaccess                (Require all denied)
├── config/                      (konfigurasi aplikasi dan database)
├── routes/                      (route web dan API)
├── storage/logs/                (log aplikasi privat)
└── public/                      (document root)
    ├── .htaccess                (HTTPS produksi dan rewrite Front Controller)
    ├── index.php                (Front Controller)
    ├── api/                     (entry point API kompatibilitas)
    ├── uploads/                 (unggahan foto)
    └── views/                   (View dan aset frontend)
```

Alur request akhirnya: **Browser/Postman → HTTPS (AutoSSL/cPanel) →
`public/.htaccess` → `public/index.php` → Router → Controller → Service/Model →
database**. Untuk halaman HTML, Controller merender View dari `public/views/`;
untuk endpoint API, Controller mengembalikan response JSON. Folder `app/`,
`config/`, `routes/`, dan `storage/` tetap berada di luar document root.
