-- Migration untuk database si_akademik (MariaDB).
-- Aman dijalankan jika kolom foto/email sudah ada.
ALTER TABLE mahasiswa
ADD COLUMN IF NOT EXISTS foto VARCHAR(255) DEFAULT NULL AFTER prodi;

ALTER TABLE users
ADD COLUMN IF NOT EXISTS email VARCHAR(100) NULL AFTER username;
