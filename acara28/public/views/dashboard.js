// Otomatis mengikuti lokasi halaman (…/public/views/xxx.html -> …/public)
const BASE_URL = location.origin + location.pathname.replace(/\/views\/[^\/]*$/, '');
const token = localStorage.getItem('token');
const role = localStorage.getItem('role');

const modal = document.querySelector('#modal-mahasiswa');
const form = document.querySelector('#form-mahasiswa');
const previewFoto = document.querySelector('#preview-foto');

function notifikasi(pesan, berhasil = true) {
    const elemen = document.querySelector('#notifikasi');
    if (!elemen) return;
    elemen.textContent = pesan;
    elemen.style.display = 'block';
    elemen.style.background = berhasil ? '#e7f5ec' : '#fdecec';
    elemen.style.color = berhasil ? '#176b37' : '#a32121';
}

if (!token) {
    window.location.href = 'login.html';
} else {
    document.querySelector('#identitas').textContent =
        `Login sebagai ${localStorage.getItem('username') || 'pengguna'} (${role || 'role tidak diketahui'})`;

    if (role === 'admin') {
        document.body.classList.add('is-admin');
    }

    muatDataMahasiswa();
}

document.querySelector('#btn-logout').addEventListener('click', () => {
    localStorage.removeItem('token');
    localStorage.removeItem('role');
    localStorage.removeItem('username');
    window.location.href = 'login.html';
});

async function api(path, options = {}) {
    const headers = { ...(options.headers || {}) };
    headers.Authorization = `Bearer ${token}`;

    // Jangan memasang Content-Type secara manual untuk FormData.
    if (options.body && !(options.body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
    }

    const response = await fetch(`${BASE_URL}${path}`, { ...options, headers });
    const result = await response.json();

    if (!response.ok || !result.success) {
        throw new Error(result.message || `Permintaan gagal (HTTP ${response.status}).`);
    }

    return result;
}

function buatSel(teks) {
    const td = document.createElement('td');
    td.textContent = teks ?? '-';
    return td;
}

async function muatDataMahasiswa() {
    const pesan = document.querySelector('#pesan');
    const tbody = document.querySelector('#tabel-mahasiswa tbody');

    pesan.textContent = 'Memuat data...';
    tbody.innerHTML = '';

    try {
        const result = await api('/mahasiswa');
        const data = Array.isArray(result.data) ? result.data : [];
        document.querySelector('#total-mahasiswa').textContent = data.length;

        if (data.length === 0) {
            pesan.textContent = 'Belum ada data mahasiswa.';
            return;
        }

        data.forEach((mahasiswa) => {
            const tr = document.createElement('tr');
            tr.append(
                buatSel(mahasiswa.nim),
                buatSel(mahasiswa.nama),
                buatSel(mahasiswa.prodi),
                buatSel(mahasiswa.status),
                buatSel(mahasiswa.email)
            );

            const fotoCell = document.createElement('td');
            if (mahasiswa.foto) {
                const image = document.createElement('img');
                image.className = 'foto-mini';
                image.alt = `Foto ${mahasiswa.nama}`;
                image.src = `${BASE_URL}/uploads/${encodeURIComponent(mahasiswa.foto)}`;
                fotoCell.appendChild(image);
            } else {
                fotoCell.textContent = 'Belum ada foto';
            }
            tr.appendChild(fotoCell);

            if (role === 'admin') {
                const actionCell = document.createElement('td');

                const editButton = document.createElement('button');
                editButton.type = 'button';
                editButton.textContent = 'Ubah';
                editButton.className = 'small';
                editButton.addEventListener('click', () => window.location.href = `form_edit.html?id=${encodeURIComponent(mahasiswa.id)}`);

                const deleteButton = document.createElement('button');
                deleteButton.type = 'button';
                deleteButton.textContent = 'Hapus';
                deleteButton.className = 'small danger';
                deleteButton.addEventListener('click', () => hapusMahasiswa(mahasiswa.id));

                actionCell.append(editButton, deleteButton);
                tr.appendChild(actionCell);
            }

            tbody.appendChild(tr);
        });

        pesan.textContent = '';
    } catch (error) {
        pesan.textContent = error.message;
        console.error(error);
    }
}

function bukaForm(mahasiswa = null) {
    if (role !== 'admin') return;

    form.reset();
    document.querySelector('#mahasiswa-id').value = mahasiswa?.id ?? '';
    document.querySelector('#judul-form').textContent =
        mahasiswa ? 'Ubah Data Mahasiswa' : 'Tambah Mahasiswa';

    document.querySelector('#nim').value = mahasiswa?.nim ?? '';
    document.querySelector('#nama').value = mahasiswa?.nama ?? '';
    document.querySelector('#prodi').value = mahasiswa?.prodi ?? '';
    document.querySelector('#status').value = mahasiswa?.status ?? 'aktif';
    document.querySelector('#email').value = mahasiswa?.email ?? '';
    document.querySelector('#catatan-foto').textContent = mahasiswa?.foto
        ? 'Kosongkan jika ingin mempertahankan foto lama. Pilih foto baru untuk menggantinya.'
        : 'Foto boleh dikosongkan.';

    if (mahasiswa?.foto) {
        previewFoto.src = `${BASE_URL}/uploads/${encodeURIComponent(mahasiswa.foto)}`;
        previewFoto.style.display = 'block';
    } else {
        previewFoto.removeAttribute('src');
        previewFoto.style.display = 'none';
    }

    modal.classList.add('open');
}

function tutupForm() {
    modal.classList.remove('open');
    form.reset();
    previewFoto.removeAttribute('src');
    previewFoto.style.display = 'none';
}

document.querySelector('#btn-tambah').addEventListener('click', () => {
    window.location.href = 'form_tambah.html';
});
document.querySelector('#btn-batal').addEventListener('click', tutupForm);

document.querySelector('#foto').addEventListener('change', () => {
    const file = document.querySelector('#foto').files[0];
    if (!file) return;

    const localUrl = URL.createObjectURL(file);
    previewFoto.src = localUrl;
    previewFoto.style.display = 'block';
});

async function hapusMahasiswa(id) {
    if (role !== 'admin' || !confirm('Yakin ingin menghapus data mahasiswa beserta fotonya?')) {
        return;
    }

    try {
        const result = await api(`/mahasiswa?id=${encodeURIComponent(id)}`, {
            method: 'DELETE'
        });
        notifikasi(result.message, true);
        await muatDataMahasiswa();
    } catch (error) {
        notifikasi(error.message, false);
    }
}
