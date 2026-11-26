# Acara 27 — Integrasi Backend-Frontend

**Pokok Bahasan:** Integrasi backend-frontend  
**Acara Praktikum/Praktik:** Minggu 14 / Pertemuan 1  
**Tempat:** Politeknik Negeri Jember  
**Alokasi Waktu:** 2 x 170 menit

Project ini melanjutkan project sebelumnya. Fitur autentikasi JWT, role admin/user, data mahasiswa, dan upload foto tetap dipakai.

## Fitur Acara 27

1. Halaman `form_tambah.html` mengirim data mahasiswa dengan `FormData` dan method `POST`.
2. Halaman `form_edit.html` mengambil data berdasarkan ID melalui `GET /mahasiswa?id=...`, lalu mengirim perubahan dengan method `PUT` dan `FormData`.
3. Tombol hapus mengirim method `DELETE` ke `DELETE /mahasiswa?id=...`.
4. Request tambah, edit, dan hapus mengirim token melalui header `Authorization: Bearer <token>`.
5. Frontend tidak mengatur `Content-Type` secara manual ketika body berupa `FormData`. Browser mengatur multipart boundary secara otomatis.
6. Frontend memeriksa kolom wajib, format foto JPG/PNG, dan ukuran foto maksimal 2 MB sebelum mengirim.
7. Pesan berhasil atau gagal ditampilkan pada halaman.

## Persiapan

1. Ekstrak folder `acara27` ke `C:\xampp\htdocs\WebServer\acara27`.
2. Jalankan Apache dan MySQL melalui XAMPP.
3. Gunakan database `si_akademik` yang sudah digunakan sebelumnya.
4. Pastikan konfigurasi database di `config/database.php` sesuai dengan komputer.
5. Pastikan folder `public/uploads` dapat ditulisi PHP dan ekstensi EXIF aktif.
6. Buka `http://localhost/WebServer/acara27/public/views/login.html`.

## Alur Penggunaan

1. Login menggunakan akun admin.
2. Pada dashboard, tekan **Tambah Mahasiswa** untuk membuka `form_tambah.html`.
3. Isi NIM, nama, program studi, status, email (opsional), ID dosen (opsional), dan foto (opsional).
4. Tekan **Tambah Mahasiswa**. Frontend mengirim FormData melalui POST.
5. Pada tabel dashboard, tekan **Ubah** untuk membuka `form_edit.html?id=ID`.
6. Form edit mengambil data lama, lalu mengirim FormData dengan method PUT.
7. Tekan **Hapus** untuk menghapus data. Sistem meminta konfirmasi sebelum mengirim DELETE.
8. Periksa hasil perubahan pada dashboard dan database.

## Endpoint API

Base URL: `http://localhost/WebServer/acara27/public`

| Method | Endpoint | Fungsi |
|---|---|---|
| POST | `/login` | Login dan mendapatkan token |
| GET | `/mahasiswa` | Mengambil seluruh data mahasiswa |
| GET | `/mahasiswa?id=1` | Mengambil satu data mahasiswa |
| POST | `/mahasiswa` | Menambah mahasiswa |
| PUT | `/mahasiswa?id=1` | Mengubah mahasiswa menggunakan FormData |
| DELETE | `/mahasiswa?id=1` | Menghapus mahasiswa dan foto terkait |

Semua operasi CRUD yang mengubah data memerlukan header:

```http
Authorization: Bearer TOKEN
```

Untuk FormData, jangan menambahkan header `Content-Type` secara manual. Pada request PUT multipart, backend membaca field dan file dari body multipart.

## Pengujian

- Login sebagai admin dan pastikan dashboard tampil.
- Tambahkan mahasiswa dengan dan tanpa foto.
- Pastikan data baru muncul pada tabel dan database.
- Edit NIM, nama, program studi, status, email, dan foto.
- Pastikan perubahan muncul pada dashboard tanpa perlu reload manual dari pengguna.
- Hapus data dan pastikan baris database serta file foto terkait terhapus.
- Coba kirim form kosong; frontend harus menolak sebelum request dikirim.
- Coba upload file bukan JPG/PNG atau foto lebih dari 2 MB; frontend dan backend harus menolak.
- Login sebagai user dan pastikan tombol tambah, edit, dan hapus tidak tersedia; backend juga menolak operasi perubahan.
- Uji kegagalan server atau jaringan dan pastikan pesan gagal tampil.

## Tugas Praktik Mandiri

1. Tambahkan notifikasi visual setiap operasi Create, Update, dan Delete berhasil maupun gagal.
2. Tambahkan validasi sisi View untuk field wajib sebelum data dikirim ke Controller.
3. Catat hasil pengujian pada laporan praktikum.

## Catatan

Backup database dilakukan pada akhir sesi praktikum. Karena foto disimpan sebagai file di `public/uploads`, salin folder tersebut bersama file SQL saat membuat backup lengkap.
