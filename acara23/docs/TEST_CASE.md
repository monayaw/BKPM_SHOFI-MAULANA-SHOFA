# 1. Acara 17 — Pengujian CRUD Mahasiswa
# TC01 — Mengambil Seluruh Data Mahasiswa
- **Skenario:** Menguji apakah API dapat menampilkan seluruh data mahasiswa.
- **Method:** GET
- **URL:** `http://localhost/WebServer/acara17/public/mahasiswa`
- **Data Uji:** Tidak memerlukan data input.
- **Hasil yang Diharapkan:** Status `200 OK` dan seluruh data mahasiswa berhasil ditampilkan.
- **Hasil Aktual:** Status `200 OK` dan seluruh data mahasiswa berhasil ditampilkan.
- **Status:** PASS

# TC02 — Menambah Mahasiswa
- **Skenario:** Menguji apakah API dapat menambahkan data mahasiswa baru.
- **Method:** POST
- **URL:** `http://localhost/WebServer/acara17/public/mahasiswa`
- **Data Uji:** Data mahasiswa baru dengan NIM, nama, dan jurusan.
- **Hasil yang Diharapkan:** Status `201 Created` dan data berhasil ditambahkan.
- **Hasil Aktual:** Status `201 Created` dan data berhasil ditambahkan.
- **Status:** PASS

# TC03 — Mengubah Data Mahasiswa
- **Skenario:** Menguji apakah API dapat mengubah data mahasiswa yang sudah tersimpan.
- **Method:** PUT
- **URL:** `http://localhost/WebServer/acara17/public/mahasiswa`
- **Data Uji:** Data mahasiswa yang akan diperbarui.
- **Hasil yang Diharapkan:** Status `200 OK` dan data berhasil diubah.
- **Hasil Aktual:** Status `200 OK` dan data berhasil diubah.
- **Status:** PASS

# TC04 — Menghapus Mahasiswa
- **Skenario:** Menguji apakah API dapat menghapus data mahasiswa berdasarkan ID.
- **Method:** DELETE
- **URL:** `http://localhost/WebServer/acara17/public/mahasiswa?id=4`
- **Data Uji:** ID mahasiswa `4`.
- **Hasil yang Diharapkan:** Status `200 OK` dan data berhasil dihapus.
- **Hasil Aktual:** Status `200 OK` dan data berhasil dihapus.
- **Status:** PASS

# 2. Acara 18 — JSON Encoding dan HTTP Status Code
Pengujian berfokus pada validasi data dan kesesuaian kode status HTTP yang diberikan API.
# TC05 — Mengirim NIM Valid
- **Skenario:** Menguji apakah API menerima data mahasiswa dengan NIM yang valid.
- **Method:** POST
- **URL:** `http://localhost/WebServer/acara18/public/mahasiswa`
- **Data Uji:** `nim=220100123`
- **Hasil yang Diharapkan:** Status `201 Created` dan data berhasil ditambahkan.
- **Hasil Aktual:** Status `201 Created`.
- **Status:** PASS

# TC06 — Mengirim NIM Kosong
- **Skenario:** Menguji apakah API menolak data mahasiswa yang tidak memiliki NIM.
- **Method:** POST
- **URL:** `http://localhost/WebServer/acara18/public/mahasiswa`
- **Data Uji:** `nim=""`
- **Hasil yang Diharapkan:** Status `422 Unprocessable Entity` karena NIM tidak boleh kosong.
- **Hasil Aktual:** Status `422 Unprocessable Entity`.
- **Status:** PASS

# TC07 — Mengirim NIM dengan Format Salah
- **Skenario:** Menguji apakah API menolak NIM yang jumlah digitnya kurang dari ketentuan.
- **Method:** POST
- **URL:** `http://localhost/WebServer/acara18/public/mahasiswa`
- **Data Uji:** NIM kurang dari 9 digit, contohnya `12345`.
- **Hasil yang Diharapkan:** Status `422 Unprocessable Entity`.
- **Hasil Aktual:** Status `422 Unprocessable Entity`.
- **Status:** PASS

# TC08 — Mengakses ID yang Tidak Ditemukan
- **Skenario:** Menguji respons API ketika pengguna mengakses data mahasiswa dengan ID yang tidak tersedia.
- **Method:** GET
- **URL:** `http://localhost/WebServer/acara18/public/mahasiswa`
- **Data Uji:** ID mahasiswa yang tidak tersedia.
- **Hasil yang Diharapkan:** Status `404 Not Found` karena data tidak ditemukan.
- **Hasil Aktual:** Status `404 Not Found`.
- **Status:** PASS

# 3. Acara 19 — Postman dan API Authentication
Pengujian berfokus pada proses pendaftaran akun menggunakan endpoint register.
# TC09 — Register dengan Data Benar
- **Skenario:** Menguji apakah pengguna dapat membuat akun dengan mengisi data yang benar.
- **Method:** POST
- **URL:** `http://localhost/WebServer/acara19/public/register`
- **Data Uji:** Username dan password yang valid.
- **Hasil yang Diharapkan:** Status `201 Created` dan akun berhasil didaftarkan.
- **Hasil Aktual:** Status `201 Created`.
- **Status:** PASS

# TC10 — Register dengan Data Kosong
- **Skenario:** Menguji apakah API menolak pendaftaran ketika data yang diperlukan tidak diisi.
- **Method:** POST
- **URL:** `http://localhost/WebServer/acara19/public/register`
- **Data Uji:** Username dan password kosong.
- **Hasil yang Diharapkan:** Status `400 Bad Request` karena data tidak lengkap.
- **Hasil Aktual:** Status `400 Bad Request`.
- **Status:** PASS

# 4. Acara 20 — API Authentication dan Error Handling
Pengujian berfokus pada proses login dan keamanan akses API menggunakan token.
# TC11 — Login dengan Akun Benar
- **Skenario:** Menguji apakah pengguna dapat login menggunakan username dan password yang benar.
- **Method:** POST
- **URL:** `http://localhost/WebServer/acara20/public/login`
- **Data Uji:** Username dan password yang benar.
- **Hasil yang Diharapkan:** Status `200 OK` dan token berhasil diterima.
- **Hasil Aktual:** Status `200 OK` dan token berhasil diterima.
- **Status:** PASS

# TC12 — Login dengan Password Salah
- **Skenario:** Menguji apakah API menolak proses login ketika password yang dimasukkan salah.
- **Method:** POST
- **URL:** `http://localhost/WebServer/acara20/public/login`
- **Data Uji:** Username benar dan password salah.
- **Hasil yang Diharapkan:** Status `401 Unauthorized` karena data login tidak sesuai.
- **Hasil Aktual:** Status `401 Unauthorized`.
- **Status:** PASS

# TC13 — Menambahkan Mahasiswa Tanpa Token
- **Skenario:** Menguji apakah API menolak penambahan data mahasiswa ketika pengguna belum menyertakan token autentikasi.
- **Method:** POST
- **URL:** `http://localhost/WebServer/acara20/public/mahasiswa`
- **Data Uji:** Data mahasiswa tanpa Authorization Token.
- **Hasil yang Diharapkan:** Status `401 Unauthorized` karena pengguna belum melakukan autentikasi.
- **Hasil Aktual:** Status `401 Unauthorized`.
- **Status:** PASS

# TC14 — Menambahkan Mahasiswa dengan Token Tidak Valid
- **Skenario:** Menguji apakah API menolak akses ketika pengguna menggunakan token yang tidak valid.
- **Method:** POST
- **URL:** `http://localhost/WebServer/acara20/public/mahasiswa`
- **Data Uji:** Data mahasiswa dengan token yang tidak valid.
- **Hasil yang Diharapkan:** Status `401 Unauthorized` karena token tidak dapat digunakan untuk autentikasi.
- **Hasil Aktual:** Status `401 Unauthorized`.
- **Status:** PASS

