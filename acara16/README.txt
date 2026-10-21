ACARA 16 - RESTful API MAHASISWA

Struktur inti:
- app/Core/Database.php : koneksi database PDO
- config/database.php : konfigurasi koneksi MySQL
- public/api/mahasiswa.php : endpoint GET, POST, PUT, DELETE
- public/.htaccess : pemetaan URL RESTful ke file API
- storage/logs/ : log error dibuat otomatis saat diperlukan

Endpoint (sesuaikan prefix folder proyek bila berbeda):
GET    /api/mahasiswa       mengambil semua mahasiswa
GET    /api/mahasiswa/{id}  mengambil mahasiswa berdasarkan ID
POST   /api/mahasiswa       menambahkan mahasiswa
PUT    /api/mahasiswa/{id}  mengubah data mahasiswa
DELETE /api/mahasiswa/{id}  menghapus mahasiswa

Untuk POST dan PUT, gunakan Body -> raw -> JSON di Postman.
Field yang dipakai kode saat ini: nim, nama, prodi, status, email, dosen_id.
Status yang diterima: aktif, cuti, lulus.

Pastikan database si_akademik dan tabel mahasiswa sudah tersedia, serta mod_rewrite Apache aktif.
Konfigurasi database ada di config/database.php.
