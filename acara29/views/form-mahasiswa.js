// Otomatis mengikuti lokasi halaman (…/public/views/xxx.html -> …/public)
const BASE_URL = location.origin + location.pathname.replace(/\/views\/[^\/]*$/, '');
const token = localStorage.getItem('token');
const role = localStorage.getItem('role');
const mode = document.body.dataset.mode;
const form = document.querySelector('#form-mahasiswa');
const pesan = document.querySelector('#pesan-form');
const judul = document.querySelector('#judul-form');
const btnSimpan = document.querySelector('#btn-simpan');
const id = new URLSearchParams(location.search).get('id');

function tampilkanPesan(message, success = false) {
    pesan.textContent = message;
    pesan.className = success ? 'success' : 'error';
}

if (!token) {
    location.href = 'login.html';
} else if (role !== 'admin') {
    tampilkanPesan('Akses ditolak. Hanya admin yang dapat mengelola data mahasiswa.');
    btnSimpan.disabled = true;
} else if (mode === 'edit') {
    if (!id) {
        tampilkanPesan('ID mahasiswa tidak ditemukan pada alamat halaman.');
        btnSimpan.disabled = true;
    } else {
        muatDataEdit();
    }
}

async function api(path, options = {}) {
    const headers = { ...(options.headers || {}), Authorization: `Bearer ${token}` };
    // Browser harus mengatur Content-Type dan boundary sendiri untuk FormData.
    if (options.body && !(options.body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
    }
    const response = await fetchRetry(`${BASE_URL}${path}`, { ...options, headers });
    const result = await ambilJSON(response);
    if (!response.ok || !result.success) {
        throw new Error(result.message || `Permintaan gagal (HTTP ${response.status}).`);
    }
    return result;
}

async function muatDataEdit() {
    try {
        tampilkanPesan('Mengambil data mahasiswa...');
        const result = await api(`/mahasiswa?id=${encodeURIComponent(id)}`);
        const data = result.data;
        form.elements.nim.value = data.nim ?? '';
        form.elements.nama.value = data.nama ?? '';
        form.elements.prodi.value = data.prodi ?? '';
        form.elements.status.value = data.status ?? 'aktif';
        form.elements.email.value = data.email ?? '';
        form.elements.dosen_id.value = data.dosen_id ?? '';
        document.querySelector('#foto-lama').textContent = data.foto
            ? `Foto saat ini: ${data.foto}. Kosongkan jika tidak ingin mengganti.`
            : 'Belum ada foto.';
        tampilkanPesan('Data berhasil dimuat.', true);
    } catch (error) {
        tampilkanPesan(error.message);
        btnSimpan.disabled = true;
    }
}

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    if (!form.reportValidity()) {
        tampilkanPesan('Periksa kembali kolom yang wajib diisi.');
        return;
    }

    const nim = form.elements.nim.value.trim();
    const nama = form.elements.nama.value.trim();
    const prodi = form.elements.prodi.value.trim();
    if (!nim || !nama || !prodi) {
        tampilkanPesan('NIM, nama, dan program studi wajib diisi.');
        return;
    }

    const foto = form.elements.foto.files[0];
    if (foto) {
        const allowed = ['image/jpeg', 'image/png'];
        if (!allowed.includes(foto.type) || !/\.(jpe?g|png)$/i.test(foto.name)) {
            tampilkanPesan('Foto harus berformat JPG, JPEG, atau PNG.');
            return;
        }
        if (foto.size > 2 * 1024 * 1024) {
            tampilkanPesan('Ukuran foto maksimal 2 MB.');
            return;
        }
    }

    const data = new FormData(form);
    let berhasil = false;
    btnSimpan.disabled = true;
    btnSimpan.textContent = 'Menyimpan...';
    tampilkanPesan('Mengirim data ke server...');

    try {
        const path = mode === 'edit'
            ? `/mahasiswa?id=${encodeURIComponent(id)}`
            : '/mahasiswa';
        const result = await api(path, {
            method: mode === 'edit' ? 'PUT' : 'POST',
            body: data
        });
        berhasil = true;
        tampilkanPesan(result.message, true);
        setTimeout(() => location.href = 'dashboard.html', 900);
    } catch (error) {
        tampilkanPesan(error.message);
    } finally {
        // Jika berhasil, tombol tetap nonaktif selama menunggu pindah halaman (cegah submit ganda)
        btnSimpan.disabled = berhasil;
        btnSimpan.textContent = mode === 'edit' ? 'Simpan Perubahan' : 'Tambah Mahasiswa';
    }
});

document.querySelector('#btn-kembali').addEventListener('click', () => {
    location.href = 'dashboard.html';
});

document.querySelector('#foto').addEventListener('change', () => {
    const file = document.querySelector('#foto').files[0];
    const preview = document.querySelector('#preview-foto');
    if (!file) {
        preview.style.display = 'none';
        return;
    }
    preview.src = URL.createObjectURL(file);
    preview.style.display = 'block';
});
