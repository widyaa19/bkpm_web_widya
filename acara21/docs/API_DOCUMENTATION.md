# Dokumentasi API Sistem Informasi Akademik

Dokumentasi ini mengikuti route yang terdaftar di `routes/web.php` dan perilaku controller saat ini. Semua URL di bawah relatif terhadap root aplikasi.

**Base URL lokal:** `http://localhost/bkpm_web/acara21/public`

## Ringkasan Endpoint API

| Method | Endpoint | Controller::method | Autentikasi | Keterangan |
| --- | --- | --- | --- | --- |
| POST | `/register` | `AuthController::register` | Publik | Registrasi akun melalui JSON; form HTML juga dapat mengirim URL ini. |
| POST | `/login` | `AuthController::login` | Publik | Login JSON menghasilkan JWT; form HTML juga dapat mengirim URL ini. |
| GET | `/api/mahasiswa` | `ApiMahasiswaController::index` | Publik | Mengambil seluruh data mahasiswa. |
| GET | `/api/mahasiswa?id={id}` | `ApiMahasiswaController::index` | Publik | Mengambil mahasiswa berdasarkan query `id`. |
| GET | `/api/mahasiswa/{id}` | `ApiMahasiswaController::show` | Publik | Mengambil mahasiswa berdasarkan ID pada URL. |
| POST | `/api/mahasiswa` | `ApiMahasiswaController::store` | Bearer token | Menambahkan mahasiswa. |
| PUT | `/api/mahasiswa?id={id}` | `ApiMahasiswaController::update` | Bearer token | Memperbarui mahasiswa berdasarkan query `id`. |
| PUT | `/api/mahasiswa/{id}` | `ApiMahasiswaController::update` | Bearer token | Memperbarui mahasiswa berdasarkan ID pada URL. |
| DELETE | `/api/mahasiswa?id={id}` | `ApiMahasiswaController::destroy` | Bearer token | Menghapus mahasiswa berdasarkan query `id`. |
| DELETE | `/api/mahasiswa/{id}` | `ApiMahasiswaController::destroy` | Bearer token | Menghapus mahasiswa berdasarkan ID pada URL. |
| GET | `/api/buku` | `BukuController::index` | Publik | Mengambil seluruh data buku. |
| GET | `/api/buku?id={id}` | `BukuController::index` | Publik | Mengambil buku berdasarkan query `id`. |
| GET | `/api/buku/{id}` | `BukuController::show` | Publik | Mengambil buku berdasarkan ID pada URL. |
| POST | `/api/buku` | `BukuController::store` | Publik | Menambahkan buku. |
| PUT | `/api/buku?id={id}` | `BukuController::update` | Publik | Memperbarui buku berdasarkan query `id`. |
| PUT | `/api/buku/{id}` | `BukuController::update` | Publik | Memperbarui buku berdasarkan ID pada URL. |
| DELETE | `/api/buku?id={id}` | `BukuController::destroy` | Publik | Menghapus buku berdasarkan query `id`. |
| DELETE | `/api/buku/{id}` | `BukuController::destroy` | Publik | Menghapus buku berdasarkan ID pada URL. |

`{id}` harus berupa bilangan bulat positif. Pada operasi `PUT` dan `DELETE` mahasiswa, `id` harus diberikan melalui path atau query string.

## Format Umum API

Request dengan body harus menggunakan header:

```http
Content-Type: application/json
```

Request mahasiswa yang mengubah data juga harus mengirim:

```http
Authorization: Bearer <token>
```

Semua respons API menggunakan JSON dengan bentuk umum berikut:

```json
{
  "success": true,
  "message": "Pesan hasil operasi.",
  "data": {},
  "errors": [],
  "timestamp": "2026-10-27T11:00:00+00:00"
}
```

Nilai `data` berisi hasil operasi atau `null`. `errors` berisi array kosong jika tidak ada kesalahan; pada validasi mahasiswa, isinya dapat berupa object berisi pesan per field. Waktu `timestamp` menggunakan format ISO 8601. Respons sukses maupun gagal dikirim dengan `Content-Type: application/json; charset=utf-8`.

## Autentikasi

### POST `/register`

**Controller:** `AuthController::register`  
**Autentikasi:** Publik  
**Header:** `Content-Type: application/json`

**Request body:**

```json
{
  "username": "mahasiswa",
  "email": "mahasiswa@example.com",
  "password": "password123"
}
```

Ketiga field wajib berupa string tidak kosong. Email harus valid dan maksimal 100 karakter; username maksimal 50 karakter. Password disimpan sebagai hash dan akun baru menggunakan role `user`.

**Respons `201 Created`:**

```json
{
  "success": true,
  "message": "Registrasi berhasil.",
  "data": {
    "id": "1"
  },
  "errors": [],
  "timestamp": "2026-10-27T11:00:00+00:00"
}
```

Respons gagal yang umum: `400 Bad Request` untuk JSON atau field wajib yang tidak valid, `409 Conflict` bila username/email sudah digunakan, `415 Unsupported Media Type` bila Content-Type bukan JSON, dan `422 Unprocessable Entity` bila email atau data akun tidak memenuhi validasi.

### POST `/login`

**Controller:** `AuthController::login`  
**Autentikasi:** Publik  
**Header:** `Content-Type: application/json`

**Request body:**

```json
{
  "username": "mahasiswa",
  "password": "password123"
}
```

**Respons `200 OK`:**

```json
{
  "success": true,
  "message": "Login berhasil.",
  "data": {
    "token": "<jwt>",
    "role": "user"
  },
  "errors": [],
  "timestamp": "2026-10-27T11:00:00+00:00"
}
```

Token berlaku selama lima menit. Kirim token pada header `Authorization: Bearer <token>` untuk operasi tulis, ubah, dan hapus mahasiswa. Respons gagal: `400 Bad Request` untuk JSON/field wajib yang tidak valid, `401 Unauthorized` untuk kredensial salah, dan `500 Internal Server Error` bila login gagal diproses.

## API Mahasiswa

Controller untuk endpoint JSON mahasiswa adalah `ApiMahasiswaController`; `MahasiswaController` menangani halaman CRUD web dan tidak menjadi controller endpoint API.

### GET `/api/mahasiswa`

Mengambil daftar mahasiswa. Endpoint ini publik dan tidak membutuhkan body.

**Respons `200 OK` (contoh):**

```json
{
  "success": true,
  "message": "Data berhasil diambil.",
  "data": [
    {
      "id": 1,
      "nim": "230160001",
      "nama": "Dewi",
      "email": "dewi@example.com",
      "prodi_id": 1,
      "angkatan": 2023,
      "status": "aktif"
    }
  ],
  "errors": [],
  "timestamp": "2026-10-27T11:00:00+00:00"
}
```

### GET `/api/mahasiswa/{id}` atau `/api/mahasiswa?id={id}`

Mengambil satu mahasiswa berdasarkan ID. Contoh: `GET /api/mahasiswa/1`.

- `200 OK`: mahasiswa ditemukan; `data` berisi object mahasiswa.
- `400 Bad Request`: ID query bukan bilangan bulat positif.
- `404 Not Found`: mahasiswa tidak ditemukan.

### POST `/api/mahasiswa`

Menambahkan mahasiswa. Wajib mengirim `Content-Type: application/json` dan Bearer token.

**Request body:**

```json
{
  "nim": "230160001",
  "nama": "Dewi",
  "email": "dewi@example.com",
  "prodi_id": 1,
  "angkatan": 2023,
  "status": "aktif"
}
```

Semua field wajib kecuali `status`, yang default-nya `aktif`. NIM pada API harus tepat sembilan digit. Nama maksimal 100 karakter; email valid dan maksimal 100 karakter; `prodi_id` harus merujuk program studi yang tersedia; angkatan harus antara 2000 dan 2155; status harus `aktif`, `cuti`, atau `lulus`.

- `201 Created`: data berhasil dibuat, `data` berisi object mahasiswa.
- `400 Bad Request`: body tidak dapat dibaca.
- `401 Unauthorized`: Bearer token tidak ada, tidak valid, atau kedaluwarsa.
- `415 Unsupported Media Type`: Content-Type bukan JSON.
- `422 Unprocessable Entity`: format NIM atau validasi data tidak sesuai.
- `500 Internal Server Error`: operasi gagal diproses.

### PUT `/api/mahasiswa/{id}` atau `/api/mahasiswa?id={id}`

Memperbarui seluruh data mahasiswa (bukan partial update). Header, body, serta aturan validasi sama dengan `POST /api/mahasiswa`.

- `200 OK`: data berhasil diperbarui dan `data` berisi object mahasiswa terbaru.
- `400 Bad Request`: ID tidak valid atau body tidak dapat dibaca.
- `401 Unauthorized`: Bearer token tidak ada, tidak valid, atau kedaluwarsa.
- `404 Not Found`: mahasiswa tidak ditemukan.
- `415 Unsupported Media Type`: Content-Type bukan JSON.
- `422 Unprocessable Entity`: NIM atau field lain tidak memenuhi validasi.
- `500 Internal Server Error`: operasi gagal diproses.

### DELETE `/api/mahasiswa/{id}` atau `/api/mahasiswa?id={id}`

Menghapus mahasiswa. Wajib mengirim Bearer token; body tidak diperlukan.

- `200 OK`: data berhasil dihapus; `data` berisi object mahasiswa yang dihapus.
- `400 Bad Request`: ID tidak valid.
- `401 Unauthorized`: Bearer token tidak ada, tidak valid, atau kedaluwarsa.
- `404 Not Found`: mahasiswa tidak ditemukan.
- `500 Internal Server Error`: operasi gagal diproses.

## API Buku

Endpoint buku saat ini tidak memakai autentikasi. Operasi yang mengirim body memerlukan `Content-Type: application/json`.

### GET `/api/buku` dan GET `/api/buku/{id}` atau `?id={id}`

Tanpa ID, mengambil seluruh buku. Dengan ID, mengambil satu buku.

**Contoh respons `200 OK` untuk satu buku:**

```json
{
  "success": true,
  "message": "Data buku berhasil diambil.",
  "data": {
    "id": 1,
    "judul": "Pemrograman Web",
    "penulis": "Dewi",
    "penerbit": "Penerbit Kampus",
    "tahun_terbit": 2024,
    "stok": 10
  },
  "errors": [],
  "timestamp": "2026-10-27T11:00:00+00:00"
}
```

Untuk `GET /api/buku` tanpa ID, `data` berupa array dengan object buku seperti di atas. ID tidak valid menghasilkan `400 Bad Request`; buku yang tidak ditemukan menghasilkan `404 Not Found`.

### POST `/api/buku`

Menambahkan buku.

**Request body:**

```json
{
  "judul": "Pemrograman Web",
  "penulis": "Dewi",
  "penerbit": "Penerbit Kampus",
  "tahun_terbit": 2024,
  "stok": 10
}
```

Semua field wajib. Judul maksimal 150 karakter; penulis dan penerbit maksimal 100 karakter; tahun terbit berupa bilangan bulat 1900–2155; stok berupa bilangan bulat nol atau lebih.

- `201 Created`: buku berhasil ditambahkan dan `data` berisi object buku.
- `400 Bad Request`: body bukan object JSON atau validasi field gagal.
- `415 Unsupported Media Type`: Content-Type bukan JSON.
- `500 Internal Server Error`: operasi gagal diproses.

### PUT `/api/buku/{id}` atau `/api/buku?id={id}`

Memperbarui seluruh field buku; gunakan body yang sama dengan endpoint POST.

- `200 OK`: data buku berhasil diperbarui.
- `400 Bad Request`: ID atau validasi body tidak valid.
- `404 Not Found`: buku tidak ditemukan.
- `415 Unsupported Media Type`: Content-Type bukan JSON.
- `500 Internal Server Error`: operasi gagal diproses.

### DELETE `/api/buku/{id}` atau `/api/buku?id={id}`

Menghapus buku; body tidak diperlukan.

- `200 OK`: buku berhasil dihapus; `data` berisi object buku yang dihapus.
- `400 Bad Request`: ID bukan bilangan bulat positif.
- `404 Not Found`: buku tidak ditemukan.
- `500 Internal Server Error`: operasi gagal diproses.

## Ringkasan Route Web

Route berikut mengembalikan halaman atau menjalankan aksi form berbasis session, bukan API JSON. Route halaman yang dilindungi memerlukan session login.

| Method | Endpoint | Controller::method | Akses |
| --- | --- | --- | --- |
| GET | `/` | `HomeController::index` | Session login |
| GET | `/dashboard` | `HomeController::index` | Session login |
| GET | `/login` | `AuthController::loginForm` | Publik |
| GET | `/register` | `AuthController::registerForm` | Publik |
| GET | `/logout` | `AuthController::logout` | Publik |
| GET | `/mahasiswa` | `MahasiswaController::index` | Session login |
| GET | `/mahasiswa/create` | `MahasiswaController::create` | Session login |
| GET | `/mahasiswa/edit?id={id}` | `MahasiswaController::edit` | Session login |
| GET | `/mahasiswa/{id}/edit` | `MahasiswaController::edit` | Session login |
| GET | `/mahasiswa/{id}` | `MahasiswaController::show` | Session login |
| POST | `/mahasiswa` | `MahasiswaController::store` | Session login |
| POST | `/mahasiswa/delete` | `MahasiswaController::delete` | Session login |
| POST | `/mahasiswa/{id}/update` | `MahasiswaController::update` | Session login |
| POST | `/mahasiswa/{id}/delete` | `MahasiswaController::destroy` | Session login |
| GET | `/prodi` | `ProdiController::index` | Session login |
| GET | `/prodi/create` | `ProdiController::create` | Session login |
| GET | `/prodi/{id}/edit` | `ProdiController::edit` | Session login |
| POST | `/prodi` | `ProdiController::store` | Session login |
| POST | `/prodi/{id}/update` | `ProdiController::update` | Session login |
| POST | `/prodi/{id}/delete` | `ProdiController::destroy` | Session login |
| GET | `/matakuliah` | `MatakuliahController::index` | Session login |
| GET | `/matakuliah/create` | `MatakuliahController::create` | Session login |
| GET | `/matakuliah/{id}/edit` | `MatakuliahController::edit` | Session login |
| POST | `/matakuliah` | `MatakuliahController::store` | Session login |
| POST | `/matakuliah/{id}/update` | `MatakuliahController::update` | Session login |
| POST | `/matakuliah/{id}/delete` | `MatakuliahController::destroy` | Session login |

`POST /login` dan `POST /register` juga melayani form HTML apabila dikirim sebagai form-urlencoded atau multipart; untuk kontrak API JSON, gunakan `Content-Type: application/json`.

## Pengujian dan Dokumentasi dengan Thunder Client

1. Di VS Code, buka Thunder Client dan buat collection, misalnya **Acara 21 - Dokumentasi API**.
2. Buat request sesuai tabel endpoint di atas. Gunakan base URL `http://localhost/bkpm_web/acara21/public`; untuk deployment lain, sesuaikan dengan URL aplikasi.
3. Uji `POST /register`, kemudian `POST /login`. Salin nilai `data.token` dari respons login dan atur sebagai Bearer Token untuk request POST, PUT, dan DELETE mahasiswa.
4. Simpan request di collection dan beri nama sesuai method serta endpoint. Gunakan tabel ringkasan di dokumen ini sebagai keterangan controller dan method penangan.
5. Jalankan setiap request dan lampirkan screenshot request beserta response sukses dan gagal pada laporan. Contoh response dan status HTTP yang diharapkan sudah dicantumkan pada bagian endpoint dokumen ini.

Thunder Client digunakan untuk mengelola dan menjalankan request di VS Code. Fitur **View Documentation** dan tautan dokumentasi publik yang disebut pada prosedur Postman tidak dihasilkan oleh alur Thunder Client ini. Gunakan file dokumentasi API ini beserta screenshot hasil pengujian sebagai lampiran laporan.
