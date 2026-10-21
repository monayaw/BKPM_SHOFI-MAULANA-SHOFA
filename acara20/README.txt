ACARA 20 - API AUTHENTICATION DAN ERROR HANDLING
Melanjutkan proyek Acara 19 (Native PHP, MySQL, XAMPP).

LOKASI FOLDER
Ekstrak folder acara20 ke:
C:\xampp\htdocs\WebServer\acara20

PERSIAPAN
1. Jalankan Apache dan MySQL dari XAMPP.
2. Pastikan config/database.php memakai database si_akademik.
3. Pastikan tabel users sudah dibuat dengan database/users.sql dari Acara 19.
4. Pastikan tabel mahasiswa masih tersedia dan kolomnya sesuai proyek Acara 18/19.
5. Buka Postman, import postman/Acara20.postman_collection.json dan
   postman/Local.postman_environment.json, lalu pilih environment Acara 20 - Local.

ENDPOINT
- GET    {{base_url}}/mahasiswa       : melihat data mahasiswa, publik.
- GET    {{base_url}}/mahasiswa?id=1  : melihat satu mahasiswa, publik.
- POST   {{base_url}}/register        : mendaftarkan user.
- POST   {{base_url}}/login           : login dan memperoleh JWT.
- POST   {{base_url}}/mahasiswa       : menambah data, perlu Bearer Token.
- PUT    {{base_url}}/mahasiswa?id=1  : memperbarui data, perlu Bearer Token.
- PATCH  {{base_url}}/mahasiswa?id=1  : memperbarui sebagian data, perlu Bearer Token.
- DELETE {{base_url}}/mahasiswa?id=1  : menghapus data, perlu Bearer Token.

ALUR PENGUJIAN
1. Register user baru melalui POST /register dengan Body raw JSON:
   {
     "username": "shofi_test",
     "email": "shofi_test@example.com",
     "password": "Rahasia123!"
   }
   Jika user sudah ada, gunakan username dan email lain. Sukses: HTTP 201.
2. Login melalui POST /login dengan JSON:
   {
     "username": "shofi_test",
     "password": "Rahasia123!"
   }
   Sukses: HTTP 200 dan response berisi token.
3. Salin token dari response login.
4. Untuk POST/PUT/PATCH/DELETE /mahasiswa, tambahkan header:
   Authorization: Bearer TOKEN_HASIL_LOGIN
   Content-Type: application/json
5. Uji request tulis tanpa token: harus mendapat HTTP 401.
6. Uji token yang diubah atau kedaluwarsa: harus mendapat HTTP 401.
7. Uji GET /mahasiswa tanpa token: tetap bisa diakses.
8. Uji endpoint salah: HTTP 404. Uji method yang tidak didukung: HTTP 405.
9. Periksa storage/logs/php-error.log jika terjadi error server.

ERROR HANDLING
- Response API tetap menggunakan JSON dengan success, message, data, errors, timestamp.
- Error teknis dicatat di storage/logs/php-error.log; pesan umum dikirim ke client.
- Masa berlaku token default 1 jam (3600 detik). Untuk tugas mandiri, ubah 3600
  menjadi 300 pada app/Core/Auth.php untuk masa berlaku 5 menit.

CATATAN
- Secret JWT di Auth.php hanya untuk praktikum lokal; jangan gunakan secret contoh
  ini untuk aplikasi produksi.
- JWT stateless tidak otomatis menyediakan logout server-side. Token yang sudah
  diterbitkan tetap berlaku sampai exp, kecuali dibuat mekanisme tambahan seperti
  blacklist/revocation.
- Pastikan jangan mengunggah password atau secret asli ke repositori publik.
