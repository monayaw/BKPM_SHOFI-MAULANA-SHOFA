# Acara 29 - Integrasi Backend-Frontend (lanjutan Acara 27)

## File yang berubah / baru (semuanya di folder `views/`)
| File | Perubahan |
|------|-----------|
| `util.js` (baru) | `escapeHTML()`, `fetchRetry()` (retry 1x saat jaringan gagal), `ambilJSON()` (pesan jelas jika respons bukan JSON) |
| `app.js` | Loading state di tabel (colspan), `escapeHTML` + `renderTabel`, cek `response.ok`, pesan error di tabel, tombol Muat Ulang dinonaktifkan saat loading; delay buatan 2 detik dihapus |
| `dashboard.js` | Loading/kosong/error langsung di tabel + tombol "Coba lagi", try-catch-finally pada hapus (tombol nonaktif + "Menghapus..."), retry otomatis |
| `form-mahasiswa.js` | Retry otomatis, tombol tetap nonaktif setelah sukses (cegah submit ganda), pesan error jelas |
| `login.js` | Retry otomatis, tombol "Memproses...", pesan error jelas |
| `index.html`, `dashboard.html`, `form_tambah.html`, `form_edit.html`, `login.html` | Menambahkan `<script src="util.js">` |

## Catatan keamanan XSS
- `app.js` memakai template `innerHTML`, maka SEMUA data dilewatkan `escapeHTML()`.
- `dashboard.js` dan `form-mahasiswa.js` sudah memakai `textContent` / properti DOM (`img.alt`, `.value`),
  yang otomatis aman, sehingga tidak ada lagi `innerHTML` dengan data dinamis.

## Catatan retry
Retry hanya untuk kegagalan jaringan (bukan HTTP 4xx/5xx), dan hanya method GET/PUT/DELETE
agar POST tambah data tidak tersimpan dobel. Login diizinkan retry secara eksplisit (`force: true`).

## Skenario pengujian (Langkah 4)
1. Login -> dashboard -> tambah (dengan foto) -> ubah -> hapus. Amati tulisan "Memuat data...",
   tombol nonaktif ("Menyimpan..." / "Menghapus..."), dan pesan sukses/gagal.
2. Tambah mahasiswa dengan nama `<b>Coba</b>` atau `<img src=x onerror=alert(1)>`:
   di tabel harus tampil sebagai teks biasa, tanpa huruf tebal dan tanpa alert.
3. Matikan jaringan (DevTools -> Network -> Offline) lalu klik Muat Ulang:
   muncul "Gagal memuat data: Koneksi ke server gagal..." setelah 1x percobaan ulang.
