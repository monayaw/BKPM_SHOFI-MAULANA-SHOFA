# Dokumentasi API — Sistem Informasi Akademik (Acara 21)

Base URL lokal: `http://localhost/WebServer/acara22/public`

Semua response menggunakan JSON. Untuk request dengan body gunakan header `Content-Type: application/json`.

## Ringkasan endpoint ( Mendokumentasikan peta router)

| Method | Endpoint                 | Controller / Method          | Auth       | Keterangan                              |
|--------|--------------------------|-------------------------------|------------|-----------------------------------------|
| POST   | `/register`              | `AuthController::register()`  | Publik     | Mendaftarkan pengguna                   |
| POST   | `/login`                 | `AuthController::login()`     | Publik     | Login dan mendapatkan JWT               |
| GET    | `/mahasiswa`             | `MahasiswaController::handle()` | Publik   | Mengambil semua mahasiswa                |
| GET    | `/mahasiswa?id={id}`     | `MahasiswaController::handle()` | Publik   | Mengambil mahasiswa berdasarkan ID      |
| POST   | `/mahasiswa`             | `MahasiswaController::handle()` | Bearer JWT | Menambah mahasiswa                     |
| PUT    | `/mahasiswa?id={id}`     | `MahasiswaController::handle()` | Bearer JWT | Memperbarui data mahasiswa             |
| PATCH  | `/mahasiswa?id={id}`     | `MahasiswaController::handle()` | Bearer JWT | Memperbarui sebagian data mahasiswa    |
| DELETE | `/mahasiswa?id={id}`     | `MahasiswaController::handle()` | Bearer JWT | Menghapus mahasiswa                    |

> Router juga menerima `/api/mahasiswa` dan meneruskannya ke `MahasiswaController::handle()`. URL utama yang direkomendasikan pada praktikum adalah `/mahasiswa`.

## Format response umum

```json
{
  "success": true,
  "message": "Pesan hasil proses",
  "data": null,
  "errors": {},
  "timestamp": "2026-10-26T10:00:00+07:00"
}
```

`timestamp` akan mengikuti waktu server ketika request dijalankan.

## 1. Registrasi
### `POST /register`
Controller: `AuthController::register()`  
Autentikasi: publik  
Headers: `Content-Type: application/json`

Request body:
```json
{
  "username": "shofi_test",
  "email": "shofi@example.com",
  "password": "Rahasia123!"
}
```

Response sukses `201 Created`:
```json
{
  "success": true,
  "message": "Registrasi berhasil.",
  "data": {
    "id": 1,
    "username": "shofi_test",
    "email": "shofi@example.com"
  },
  "errors": {},
  "timestamp": "<waktu-server>"
}
```

Kemungkinan response gagal:
- `400 Bad Request`: body bukan JSON yang valid.
- `409 Conflict`: username atau email sudah terdaftar.
- `422 Unprocessable Entity`: username/email/password tidak memenuhi validasi, termasuk format email salah.
- `500 Internal Server Error`: terjadi kesalahan server atau database.

## 2. Login
### `POST /login`
Controller: `AuthController::login()`  
Autentikasi: publik  
Headers: `Content-Type: application/json`

Request body:
```json
{
  "username": "shofi_test",
  "password": "Rahasia123!"
}
```

Response sukses `200 OK`:
```json
{
  "success": true,
  "message": "Login berhasil.",
  "data": {
    "token": "<JWT-dari-server>",
    "token_type": "Bearer",
    "expires_in": 300,
    "role": "user"
  },
  "errors": {},
  "timestamp": "<waktu-server>"
}
```
Kemungkinan response gagal: `400` JSON tidak valid, `401` username/password salah, `422` field wajib kosong, atau `500` kesalahan server.

## 3. Mengambil seluruh mahasiswa
### `GET /mahasiswa`
Controller: `MahasiswaController::handle()`  
Autentikasi: publik  
Headers: tidak ada header autentikasi yang diperlukan.
Contoh response `200 OK` (data mengikuti database):
```json
{
  "success": true,
  "message": "Data mahasiswa berhasil diambil.",
  "data": [
    {
      "id": 1,
      "nim": "230100001",
      "nama": "Budi",
      "prodi": "Teknik Informatika",
      "status": "aktif",
      "email": "budi@example.com",
      "dosen_id": null
    }
  ],
  "errors": {},
  "timestamp": "<waktu-server>"
}
```
## 4. Mengambil mahasiswa berdasarkan ID
### `GET /mahasiswa?id=1`
Controller: `MahasiswaController::handle()`  
Autentikasi: publik
Response sukses `200 OK` berisi satu objek mahasiswa pada `data`. 
Jika ID tidak valid, response `400`; jika data tidak ditemukan, response `404`.

## 5. Menambahkan mahasiswa
### `POST /mahasiswa`
Controller: `MahasiswaController::handle()`  
Autentikasi: wajib Bearer JWT.
Headers:
```text
Content-Type: application/json
Authorization: Bearer <token>
```
Request body:
```json
{
  "nim": "230100002",
  "nama": "Siti",
  "prodi": "Manajemen Informatika",
  "status": "aktif",
  "email": "siti@example.com",
  "dosen_id": null
}
```
Response sukses `201 Created`: `success` bernilai `true`, pesan `Mahasiswa berhasil ditambahkan.`, dan `data` berisi mahasiswa yang baru dibuat. Token hilang/tidak valid menghasilkan `401`; validasi data dapat menghasilkan `422`; kesalahan server menghasilkan `500`.

## 6. Memperbarui mahasiswa
### `PUT /mahasiswa?id=1`
Controller: `MahasiswaController::handle()`  
Autentikasi: wajib Bearer JWT.  
Headers: `Content-Type: application/json` dan `Authorization: Bearer <token>`.
Kirim field mahasiswa yang diperlukan, contohnya:
```json
{
  "nim": "230100001",
  "nama": "Budi Santoso",
  "prodi": "Teknik Informatika",
  "status": "aktif",
  "email": "budi@example.com",
  "dosen_id": null
}
```
Response sukses `200 OK` berisi data terbaru. Parameter `id` wajib; 
jika tidak ada menghasilkan `400`, dan jika mahasiswa tidak ditemukan menghasilkan `404`.

### `PATCH /mahasiswa?id=1`
Autentikasi dan headers sama dengan PUT. PATCH digunakan untuk memperbarui sebagian field saja, misalnya:
```json
{
  "nama": "Budi Santoso"
}
```
Response sukses `200 OK` berisi data terbaru.

## 7. Menghapus mahasiswa
### `DELETE /mahasiswa?id=1`
Controller: `MahasiswaController::handle()`  
Autentikasi: wajib Bearer JWT.  
Headers: `Authorization: Bearer <token>`.
Response sukses `200 OK`:
```json
{
  "success": true,
  "message": "Data mahasiswa berhasil dihapus.",
  "data": null,
  "errors": {},
  "timestamp": "<waktu-server>"
}
```
ID tidak diisi menghasilkan `400`; data yang tidak ditemukan atau sudah dihapus menghasilkan `404`.

## Alur request

```text
Client / Postman
      |
      v
public/index.php (Front Controller)
      |
      v
Router::dispatch()
      |
      +---- /register atau /login ---> AuthController ---> User model ---> Database
      |
      +---- /mahasiswa -------------> MahasiswaController
                                        |
                                        +-- GET: publik
                                        +-- POST/PUT/PATCH/DELETE: Auth::requireAuth()
                                        |
                                        v
                                  Mahasiswa model ---> Database
      |
      v
Response JSON + HTTP status
```

## Pengujian di Postman

1. Atur Environment `Local` dengan `base_url = http://localhost/WebServer/acara22/public`.
2. Jalankan `POST {{base_url}}/register` untuk membuat akun, bila belum ada.
3. Jalankan `POST {{base_url}}/login`, lalu salin nilai `data.token`.
4. Jalankan `GET {{base_url}}/mahasiswa` tanpa token; endpoint ini publik.
5. Jalankan `POST`, `PUT`, `PATCH`, dan `DELETE` mahasiswa dengan Authorization type **Bearer Token**.
6. Uji request tulis tanpa token dan pastikan respons `401 Unauthorized`.
7. Simpan response contoh sukses dan gagal di Postman melalui tab **Examples**, lalu pilih **View Documentation** pada Collection untuk melihat dokumentasi yang dihasilkan.

Catatan: contoh response pada dokumen ini merupakan contoh format. Nilai ID, data, role, dan timestamp aktual mengikuti database dan hasil pengujian.
