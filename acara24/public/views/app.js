const BASE_URL = 'http://localhost/WebServer/acara24/public';

async function muatDataMahasiswa() {
    const pesan = document.querySelector('#pesan');
    const tbody = document.querySelector('#tabel-mahasiswa tbody');

    // Menampilkan pesan saat mengambil data
    pesan.textContent = 'Memuat data...';

    try {
        const response = await fetch(`${BASE_URL}/mahasiswa`);
        await new Promise(resolve => setTimeout(resolve, 2000));
        const hasil = await response.json();

        tbody.innerHTML = '';

        // Memeriksa apakah data kosong
        if (!hasil.data || hasil.data.length === 0) {
            pesan.textContent = 'Belum ada data';
            return;
        }

        // Menampilkan data mahasiswa
        hasil.data.forEach((mhs) => {
            tbody.innerHTML += `
                <tr>
                    <td>${mhs.nim}</td>
                    <td>${mhs.nama}</td>
                    <td>${mhs.prodi}</td>
                </tr>
            `;
        });

        // Menghilangkan pesan setelah data berhasil ditampilkan
        pesan.textContent = '';

    } catch (error) {
        pesan.textContent = 'Gagal memuat data mahasiswa';
        console.error('Gagal memuat data:', error);
    }
}

document.addEventListener('DOMContentLoaded', muatDataMahasiswa);
document.getElementById('btn-muat-ulang')
    .addEventListener('click', muatDataMahasiswa);