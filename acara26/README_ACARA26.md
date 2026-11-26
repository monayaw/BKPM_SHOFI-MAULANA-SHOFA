# Acara 26 — Smart Village

**Pokok Bahasan:** Manajemen konten, Upload file dan Database management  
**Tempat:** Politeknik Negeri Jember  
**Alokasi Waktu:** 2 x 170 menit

Project ini melanjutkan aplikasi PHP Native MVC sebelumnya. Fitur login, JWT, role admin/user, dan CRUD mahasiswa tetap dipertahankan.

## 1. Persiapan

1. Ekstrak folder `acara26` ke `C:\xampp\htdocs\WebServer\acara26`.
2. Jalankan Apache dan MySQL melalui XAMPP.
3. Pastikan database `si_akademik` tersedia.
4. Pastikan tabel `users` memiliki kolom `id`, `username`, `email`, `password`, dan `role`. Nilai `role` adalah `admin` atau `user`. Project mendukung password lama berbentuk teks biasa pada login pertama, lalu otomatis mengubahnya menjadi hash.
5. Pastikan tabel `mahasiswa` memiliki kolom `id`, `nim`, `nama`, `prodi`, `foto`, `status`, `dosen_id`, `email`, dan `angkatan` sesuai dump database. NIM mengikuti tipe `VARCHAR(20)` dan email boleh kosong.
6. Buka phpMyAdmin, pilih database `si_akademik`, lalu jalankan `database/migration_acara26.sql`. Perintah menggunakan `IF NOT EXISTS`, sehingga kolom `foto` atau `email` yang sudah tersedia tidak ditambahkan ulang.
7. Pastikan folder `public/uploads` dapat ditulisi oleh PHP.
8. Pastikan ekstensi PHP EXIF aktif. Jika belum, aktifkan `extension=exif` pada `php.ini`, lalu restart Apache.
9. Buka `http://localhost/WebServer/acara26/public/views/login.html`.

Konfigurasi database ada di `config/database.php`. Konfigurasi bawaan memakai MySQL lokal XAMPP: host `localhost`, database `si_akademik`, username `root`, password kosong.

## 2. Login dan hak akses

Login menggunakan akun yang sudah terdaftar pada tabel `users`. Password hash diverifikasi menggunakan `password_verify()`. Untuk kompatibilitas database lama, password teks biasa juga diterima satu kali; setelah login berhasil, sistem langsung menyimpan ulang password dalam bentuk hash.

Untuk mengatur role akun melalui phpMyAdmin:

```sql
UPDATE users SET role = 'admin' WHERE username = 'username_akun';
UPDATE users SET role = 'user' WHERE username = 'username_akun';
```

Pada database yang disertakan, akun awal adalah `admin` dengan password lama `admin123` (role admin), serta `mahasiswa` dengan password lama `user123` (role user). Login pertama akan mengubah password menjadi hash. Admin dapat menambah, mengubah, dan menghapus data mahasiswa. User hanya dapat melihat data. Pembatasan juga diperiksa di backend melalui `Auth::requireAdmin()`, bukan hanya menyembunyikan tombol di browser.

## 3. Fitur upload foto

- Form tambah/ubah menerima foto JPG, JPEG, atau PNG.
- Ukuran maksimum foto adalah 2 MB.
- Server memeriksa ekstensi dan signature gambar dengan `exif_imagetype()`.
- NIM maksimal 20 karakter sesuai kolom `VARCHAR(20)` pada database. Email bersifat opsional karena kolom database mengizinkan `NULL`.
- Nama file dibuat unik dengan awalan `foto_`.
- File disimpan di `public/uploads/`; nama file disimpan di kolom `mahasiswa.foto`.
- Saat data diedit dan foto baru dipilih, foto lama dihapus setelah data baru berhasil disimpan.
- Jika foto tidak diganti, foto lama tetap dipertahankan.
- Saat data mahasiswa dihapus, foto terkait juga dihapus.
- Folder uploads memiliki aturan untuk menonaktifkan directory listing dan menolak file skrip PHP.

## 4. API

Base URL: `http://localhost/WebServer/acara26/public`

| Method | Endpoint | Keterangan |
|---|---|---|
| POST | `/login` | Login dan memperoleh Bearer Token |
| GET | `/mahasiswa` | Melihat semua mahasiswa |
| GET | `/mahasiswa?id=1` | Melihat satu mahasiswa |
| POST | `/mahasiswa` | Menambah mahasiswa menggunakan FormData |
| POST | `/mahasiswa?id=1` | Mengubah data menggunakan FormData |
| PUT/PATCH | `/mahasiswa?id=1` | Memperbarui data menggunakan JSON |
| DELETE | `/mahasiswa?id=1` | Menghapus data dan foto |

Untuk endpoint tambah, ubah, dan hapus, sertakan header:

```text
Authorization: Bearer TOKEN
```

Untuk POST FormData, jangan mengatur header `Content-Type` secara manual. Browser akan membuat multipart boundary secara otomatis.

Contoh field FormData: `nim`, `nama`, `prodi`, `status`, `email`, dan `foto`. Field `foto` boleh tidak dikirim jika tidak ada foto baru.

## 5. Backup database

1. Buka phpMyAdmin.
2. Pilih database `si_akademik`.
3. Klik **Export**.
4. Pilih metode **Quick** dan format **SQL**.
5. Klik **Export** untuk mengunduh file cadangan.
6. Simpan file dengan nama yang mencantumkan tanggal backup.

Foto disimpan sebagai file di `public/uploads`, bukan di database. Salin folder tersebut juga saat membuat cadangan lengkap.

## 6. Restore database

1. Buat database tujuan, misalnya `si_akademik_restore`.
2. Pilih database tujuan tersebut di phpMyAdmin.
3. Buka menu **Import**.
4. Pilih file SQL hasil backup.
5. Klik **Import/Go** dan tunggu proses selesai.
6. Periksa tabel, jumlah data, dan kolom `foto`.
7. Salin cadangan folder `uploads` ke folder project jika foto juga perlu dipulihkan.

## 7. Pengujian

- Tambah mahasiswa tanpa foto.
- Tambah mahasiswa dengan foto JPG/PNG yang valid dan berukuran di bawah 2 MB.
- Pastikan foto tersimpan di `public/uploads` dan nama file tercatat di kolom `foto`.
- Uji file PDF, gambar palsu yang hanya diganti ekstensinya, dan foto di atas 2 MB. Server harus menolak file tersebut.
- Ubah data dengan foto baru; pastikan foto baru tersimpan dan foto lama dihapus.
- Ubah data tanpa memilih foto baru; foto lama harus tetap ada.
- Hapus data mahasiswa; pastikan foto terkait ikut terhapus.
- Login sebagai user, lalu coba POST/PUT/DELETE melalui Postman. Server harus menolak akses dengan HTTP 403.
- Lakukan backup dan restore ke database percobaan, lalu periksa tabel dan datanya.

## 8. Jadwal backup praktikum

Backup dilakukan pada akhir setiap sesi praktikum. Catat tanggal, nama file SQL, lokasi penyimpanan, serta hasil pemeriksaan restore pada laporan praktikum.
