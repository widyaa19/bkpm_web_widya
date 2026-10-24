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
