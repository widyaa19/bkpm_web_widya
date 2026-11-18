# Laporan Pengujian Manual dan Blackbox

## Informasi Pengujian

- **Aplikasi:** Sistem Informasi Akademik — API autentikasi dan mahasiswa
- **Tanggal:** 28 Oktober 2026
- **Metode:** Manual blackbox testing dengan equivalence partitioning
- **Lingkungan:** Laragon, PHP 8.1, MySQL, API lokal
- **Base URL:** `http://localhost/bkpm_web/acara25/public`
- **Jumlah skenario:** 17

Dokumen ini memuat rancangan dan pencatatan hasil pengujian manual. Jalankan
request melalui Postman, periksa status HTTP serta responsnya, lalu lampirkan
screenshot sebagai bukti pengujian.

## Ruang Lingkup dan Pemetaan Route

| Method | Endpoint aktual | Controller | Autentikasi |
| --- | --- | --- | --- |
| POST | `/register` | `AuthController::register` | Tidak |
| POST | `/login` | `AuthController::login` | Tidak |
| GET | `/api/mahasiswa` | `ApiMahasiswaController::index` | Tidak |
| POST | `/api/mahasiswa` | `ApiMahasiswaController::store` | JWT admin |
| PUT | `/api/mahasiswa/{id}` | `ApiMahasiswaController::update` | JWT admin |
| DELETE | `/api/mahasiswa/{id}` | `ApiMahasiswaController::destroy` | JWT admin |

Rute API aktual menggunakan awalan `/api/mahasiswa`. Rute `/mahasiswa` pada
aplikasi ini adalah halaman web berbasis sesi dan bukan API REST; perubahan dan
penghapusan melalui web dikirim ke rute form terpisah. Karena itu, pengujian
blackbox API di bawah mengikuti rute yang terdaftar dan didokumentasikan
aplikasi.

Semua request yang mengirim body menggunakan `Content-Type: application/json`.
Operasi POST, PUT, dan DELETE mahasiswa memerlukan JWT admin pada header
`Authorization`. Token valid dengan role user harus ditolak menggunakan
`403 Forbidden`; token tidak ada, tidak valid, atau kedaluwarsa menghasilkan
`401 Unauthorized`.
Field wajib mahasiswa: `nim`, `nama`, `email`, `prodi_id`, dan `angkatan`;
`status` opsional dan default-nya `aktif`. NIM API harus terdiri dari 9 digit,
program studi harus tersedia, dan PUT mengganti seluruh data.

## Data Uji

Untuk mengulang pengujian, gunakan placeholder berikut:

- `{{token}}`: token dari respons login yang berhasil.
- `{{id_uji}}`: ID mahasiswa sementara yang dibuat pada TC05.
- `{{nim_uji}}`: NIM 9 digit unik untuk data uji.
- `{{nim_terpakai}}`: NIM 9 digit yang sudah digunakan oleh data lain.

Contoh body mahasiswa valid:

```json
{
  "nim": "{{nim_uji}}",
  "nama": "Mahasiswa Uji Acara 23",
  "email": "uji-acara25@example.com",
  "prodi_id": 1,
  "angkatan": 2026,
  "status": "aktif"
}
```

## Test Case dan Hasil

Untuk test case yang belum dijalankan, isi hasil aktual dan status setelah
eksekusi. `Pass` berarti status HTTP dan perilaku aktual sama dengan yang
diharapkan; selain itu tandai `Fail` dan simpan bukti respons.

| ID | Skenario pengujian | Data uji | Hasil yang diharapkan | Hasil aktual | Status |
| --- | --- | --- | --- | --- | --- |
| TC00 | Mengambil daftar mahasiswa | `GET /api/mahasiswa` | `200 OK`, daftar data dikembalikan | Isi setelah pengujian | Belum diuji |
| TC01 | Registrasi akun uji | `POST /register`, username/email unik dan field valid | `201 Created`, akun berhasil dibuat | Isi setelah pengujian | Belum diuji |
| TC02 | Login dengan kredensial benar | Username dan password akun uji | `200 OK`, token diterima | Isi setelah pengujian | Belum diuji |
| TC03 | Login dengan password berbeda | Username akun uji, password salah | `401 Unauthorized` | Isi setelah pengujian | Belum diuji |
| TC04 | Store tanpa token | `POST /api/mahasiswa`, tanpa header Authorization | `401 Unauthorized` | Isi setelah pengujian | Belum diuji |
| TC04a | Store menggunakan token role user | `POST /api/mahasiswa`, token login user | `403 Forbidden`, data tidak dibuat | Isi setelah pengujian | Belum diuji |
| TC05 | Membuat mahasiswa valid (prasyarat update/destroy) | Body valid dengan NIM unik 9 digit dan token admin | `201 Created`, data dan ID baru dikembalikan | Isi setelah pengujian | Belum diuji |
| TC06 | Store dengan NIM kosong | `POST /api/mahasiswa`, `nim=""` | `422 Unprocessable Entity` | Isi setelah pengujian | Belum diuji |
| TC07 | Store dengan NIM duplikat | `POST /api/mahasiswa`, `nim={{nim_terpakai}}` | `422 Unprocessable Entity` | Isi setelah pengujian | Belum diuji |
| TC08 | Store dengan email tidak valid | `POST /api/mahasiswa`, `email="bukan-email"` | `422 Unprocessable Entity` | Isi setelah pengujian | Belum diuji |
| TC09 | Update data valid | `PUT /api/mahasiswa?id={{id_uji}}`, seluruh field valid | `200 OK`, data diperbarui | Isi setelah pengujian | Belum diuji |
| TC09a | Update menggunakan token role user | `PUT /api/mahasiswa?id={{id_uji}}`, body valid dan token user | `403 Forbidden`, data target tidak berubah | Isi setelah pengujian | Belum diuji |
| TC10 | Update dengan NIM kosong | `PUT /api/mahasiswa?id={{id_uji}}`, `nim=""` | `422 Unprocessable Entity`, data target tidak berubah | Isi setelah pengujian | Belum diuji |
| TC11 | Update dengan NIM duplikat | `PUT /api/mahasiswa?id={{id_uji}}`, NIM milik data lain | `422 Unprocessable Entity`, data target tidak berubah | Isi setelah pengujian | Belum diuji |
| TC12 | Update dengan email tidak valid | `PUT /api/mahasiswa?id={{id_uji}}`, `email="bukan-email"` | `422 Unprocessable Entity`, data target tidak berubah | Isi setelah pengujian | Belum diuji |
| TC13 | Update ID yang tidak ditemukan | `PUT /api/mahasiswa/999999999`, body valid | `404 Not Found` | Isi setelah pengujian | Belum diuji |
| TC14 | Destroy tanpa token | `DELETE /api/mahasiswa?id={{id_uji}}`, tanpa Authorization | `401 Unauthorized`, data target tidak dihapus | Isi setelah pengujian | Belum diuji |
| TC14a | Destroy menggunakan token role user | `DELETE /api/mahasiswa?id={{id_uji}}`, token login user | `403 Forbidden`, data target tidak dihapus | Isi setelah pengujian | Belum diuji |
| TC15 | Destroy data yang ada | `DELETE /api/mahasiswa?id={{id_uji}}`, token admin | `200 OK`, data yang dihapus dikembalikan | Isi setelah pengujian | Belum diuji |
| TC16 | Memeriksa ID setelah destroy | `GET /api/mahasiswa/{{id_uji}}` setelah TC15 | `404 Not Found`, data sudah tidak tersedia | Isi setelah pengujian | Belum diuji |

TC09–TC13 mencakup lima kasus update dengan partisi data valid, NIM kosong,
NIM duplikat, email tidak valid, serta ID yang tidak ditemukan. TC14–TC16
menguji autentikasi destroy, penghapusan data valid, dan verifikasi bahwa data
yang dihapus tidak lagi tersedia.

## Langkah Reproduksi di Postman

1. Pastikan Apache dan MySQL Laragon aktif dan database `si_akademik` tersedia.
   Di Postman, impor [`postman_collection.json`](../postman_collection.json)
   dan [`postman_environment_local.json`](../postman_environment_local.json).
   Pilih environment **Local**, lalu ubah `base_url` menjadi
   `http://localhost/bkpm_web/acara25/public` agar sesuai dengan lokasi project
   ini.
2. Atur `testNim` menjadi NIM 9 digit yang belum digunakan. Request registrasi
   pada collection membuat `registerUsername` dan `registerEmail` unik secara
   otomatis; gunakan akun yang sama untuk request login. Password pada request
   collection adalah `password123`, jadi gunakan nilai itu pada login.
   Collection menyimpan JWT pada variable `jwt` (setara dengan `{{token}}`
   pada tabel) dan ID mahasiswa pada `mahasiswaId` (setara dengan `{{id_uji}}`).
   Jangan membagikan environment atau collection yang berisi kredensial maupun
   token.
3. Jalankan TC01 untuk membuat akun melalui `POST {{base_url}}/register`, lalu
   TC02 untuk login melalui `POST {{base_url}}/login`. Simpan `data.token`
   sebagai variable `jwt`/`token`. Jalankan TC03 dengan password yang berbeda
   untuk memastikan kredensial salah ditolak.
4. Jalankan TC00 untuk melihat data mahasiswa dan menentukan NIM 9 digit valid
   yang sudah digunakan sebagai `nim_terpakai`. Jalankan TC04 dengan menghapus
   header Bearer Authorization untuk menguji store tanpa token. Jalankan TC05
   dengan body mahasiswa valid dan NIM unik; simpan `data.id` sebagai
   `mahasiswaId`/`id_uji`.
   Untuk TC04, set tab Authorization request ke **No Auth** agar token tidak
   ikut dikirim.
5. Jalankan TC06–TC08 untuk variasi store. Pastikan `nim_terpakai` merupakan
   NIM 9 digit valid yang sudah tersimpan. Untuk setiap kasus, ubah body request
   POST mahasiswa sesuai data uji pada tabel.
6. Jalankan TC09–TC13 untuk menguji variasi update. Gunakan
   `PUT {{base_url}}/api/mahasiswa?id={{id_uji}}` dan kirim seluruh field wajib
   pada body JSON. Setelah kasus gagal TC10–TC12, gunakan
   `GET {{base_url}}/api/mahasiswa/{{id_uji}}` untuk memastikan data target
   tidak berubah. TC13 menggunakan ID yang tidak ditemukan.
7. Jalankan TC14 sebelum TC15 dengan mengatur Authorization request DELETE ke
   **No Auth**. Jalankan TC15 setelah semua test case yang membutuhkan data
   mahasiswa karena TC15 menghapus data uji. Jalankan TC16 setelahnya untuk
   memastikan ID yang telah dihapus tidak ditemukan.
8. Catat status HTTP aktual dan ringkasan body respons pada kolom hasil aktual.
   Tandai Pass hanya jika status dan perilaku sesuai hasil yang diharapkan;
   jika tidak, tandai Fail dan simpan bukti respons.
9. Jika ditemukan Fail, telusuri handler API, Controller, Service, atau Model
   yang terkait. Lakukan perbaikan pada lapisan yang relevan, jalankan ulang
   kasus terkait, lalu catat hasil retest. Akun uji dapat dibiarkan atau
   dibersihkan melalui database sesuai kebijakan lingkungan; endpoint hapus
   akun tidak disediakan dalam route project ini.

## Kesimpulan

Lengkapi kesimpulan setelah seluruh 17 skenario dieksekusi. Jika terdapat Fail,
cantumkan kasus yang gagal, perbaikan pada lapisan terkait, dan hasil retest.
