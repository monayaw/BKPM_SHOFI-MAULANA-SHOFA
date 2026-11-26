// Otomatis mengikuti lokasi halaman (.../views/xxx.html -> alamat dasar)
const BASE_URL = location.origin + location.pathname.replace(/\/views\/[^\/]*$/, '');
const JUMLAH_KOLOM = 3;

function tampilkanBaris(teks, kelas = 'info') {
    const tbody = document.querySelector('#tabel-mahasiswa tbody');
    tbody.innerHTML =
        `<tr><td colspan="${JUMLAH_KOLOM}" class="${kelas}">${escapeHTML(teks)}</td></tr>`;
}

function renderTabel(data) {
    const tbody = document.querySelector('#tabel-mahasiswa tbody');
    tbody.innerHTML = data.map((mhs) =>
        `<tr>
            <td>${escapeHTML(mhs.nim)}</td>
            <td>${escapeHTML(mhs.nama)}</td>
            <td>${escapeHTML(mhs.prodi)}</td>
        </tr>`
    ).join('');
}

async function muatDataMahasiswa() {
    const pesan = document.querySelector('#pesan');
    const tombol = document.querySelector('#btn-muat-ulang');

    // Loading state: tombol dinonaktifkan agar tidak diklik berulang
    tombol.disabled = true;
    tombol.textContent = 'Memuat...';
    pesan.textContent = 'Memuat data...';
    tampilkanBaris('Memuat data...');

    try {
        const response = await fetchRetry(`${BASE_URL}/mahasiswa`);
        if (!response.ok) throw new Error('Server merespons dengan status ' + response.status);

        const hasil = await ambilJSON(response);
        if (!hasil.data || hasil.data.length === 0) {
            tampilkanBaris('Belum ada data');
            return;
        }
        renderTabel(hasil.data);
    } catch (error) {
        // Error feedback: tidak gagal secara senyap
        tampilkanBaris('Gagal memuat data: ' + error.message, 'error');
        console.error('Gagal memuat data:', error);
    } finally {
        pesan.textContent = '';
        tombol.disabled = false;
        tombol.textContent = 'Muat Ulang';
    }
}

document.addEventListener('DOMContentLoaded', muatDataMahasiswa);
document.getElementById('btn-muat-ulang').addEventListener('click', muatDataMahasiswa);
