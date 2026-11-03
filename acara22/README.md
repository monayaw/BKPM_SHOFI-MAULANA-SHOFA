# Project Acara 22 — Dokumentasi Endpoint

Project ini merupakan lanjutan dari Acara 21. Fokus Acara 22 adalah dokumentasi REST API menggunakan OpenAPI 3.0.

## Lokasi
Letakkan folder ini di:

`C:\xampp\htdocs\WebServer\acara22`

## Base URL
`http://localhost/WebServer/acara22/public`

Pastikan Apache dan MySQL aktif di XAMPP serta database yang dipakai project Acara 21 sudah tersedia dan konfigurasi `config/database.php` sesuai.

## Dokumentasi
- `docs/openapi.yaml` — spesifikasi OpenAPI 3.0 lengkap.
- `docs/API_DOCUMENTATION.md` — dokumentasi endpoint dalam Markdown.
- `docs/LAPORAN_ACARA22.md` — laporan praktikum, studi kasus, dan tugas mandiri.

## Membuka Swagger Editor
1. Jalankan Apache dan MySQL jika ingin mencoba endpoint lokal.
2. Buka https://editor.swagger.io.
3. Buka file `docs/openapi.yaml` menggunakan editor teks, lalu salin seluruh isinya.
4. Tempelkan ke panel kiri Swagger Editor.
5. Periksa panel kanan. Pastikan endpoint register, login, dan mahasiswa tampil tanpa error.
6. Simpan screenshot hasilnya ke `docs/swagger-editor.png`.

## Ringkasan endpoint

| Method | Endpoint | Auth |
|---|---|---|
| POST | `/register` | Publik |
| POST | `/login` | Publik |
| GET | `/mahasiswa` | Publik |
| GET | `/mahasiswa?id={id}` | Publik |
| POST | `/mahasiswa` | Bearer JWT |
| PUT | `/mahasiswa?id={id}` | Bearer JWT |
| PATCH | `/mahasiswa?id={id}` | Bearer JWT |
| DELETE | `/mahasiswa?id={id}` | Bearer JWT |

## Catatan
Endpoint dan skema mengikuti kode Acara 21 yang diberikan. Pada project ini operasi tulis mahasiswa dilindungi Bearer JWT. Masa berlaku token pada implementasi `Auth::generateToken()` adalah 300 detik.
