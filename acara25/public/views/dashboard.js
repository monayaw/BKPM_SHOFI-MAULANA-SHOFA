const BASE_URL = 'http://localhost/WebServer/acara25/public';
const token = localStorage.getItem('token');
const role = localStorage.getItem('role');
if (!token) { window.location.href = 'login.html'; }
else {
  document.querySelector('#identitas').textContent = `Login sebagai ${localStorage.getItem('username') || 'pengguna'} (${role || 'role tidak diketahui'})`;
  if (role === 'admin') {
    document.body.classList.add('is-admin');
  } else {
    document.querySelector('#btn-tambah').style.display = 'none';
  }
  muatDataMahasiswa();
}

document.querySelector('#btn-logout').addEventListener('click', () => {
  localStorage.removeItem('token'); 
  localStorage.removeItem('role'); 
  localStorage.removeItem('username');
  window.location.href = 'login.html';
});

async function api(url, options = {}) {
  const headers = { ...(options.headers || {}) };
  if (token) headers.Authorization = `Bearer ${token}`;
  if (options.body) headers['Content-Type'] = 'application/json';
  const response = await fetch(`${BASE_URL}${url}`, { ...options, headers });
  const hasil = await response.json();
  if (!response.ok) throw new Error(hasil.message || `HTTP ${response.status}`);
  return hasil;
}
async function muatDataMahasiswa() {
  const pesan = document.querySelector('#pesan'); const tbody = document.querySelector('#tabel-mahasiswa tbody');
  pesan.textContent = 'Memuat data...'; tbody.innerHTML = '';
  try {
    const hasil = await api('/mahasiswa');
    const data = Array.isArray(hasil.data) ? hasil.data : [];
    document.querySelector('#total-mahasiswa').textContent = data.length;
    if (!data.length) { pesan.textContent = 'Belum ada data'; return; }
    data.forEach(m => {
      const tr = document.createElement('tr');
      [m.nim, m.nama, m.prodi, m.status, m.email].forEach(value => { const td = document.createElement('td'); td.textContent = value ?? '-'; tr.appendChild(td); });
      if (role === 'admin') {
        const td = document.createElement('td');
        const edit = document.createElement('button');
        edit.textContent = 'Ubah'; edit.className = 'small'; edit.addEventListener('click', () => ubahMahasiswa(m));
        const del = document.createElement('button');
        del.textContent = 'Hapus'; del.className = 'small danger'; del.addEventListener('click', () => hapusMahasiswa(m.id));
        td.append(edit, del);
        tr.appendChild(td);
      }
      tbody.appendChild(tr);
    });
    pesan.textContent = '';
  } catch (e) { pesan.textContent = e.message; console.error(e); }
}
async function tambahMahasiswa() {
  const data = formData(); if (!data) return;
  try { await api('/mahasiswa', { method: 'POST', body: JSON.stringify(data) }); alert('Mahasiswa berhasil ditambahkan.'); muatDataMahasiswa(); } catch (e) { alert(e.message); }
}
function formData(old = {}) {
  const nim = prompt('NIM (9 digit):', old.nim || ''); if (nim === null) return null;
  const nama = prompt('Nama:', old.nama || ''); if (nama === null) return null;
  const prodi = prompt('Program studi:', old.prodi || ''); if (prodi === null) return null;
  const status = prompt('Status (aktif/cuti/lulus):', old.status || 'aktif'); if (status === null) return null;
  const email = prompt('Email:', old.email || ''); if (email === null) return null;
  return { nim, nama, prodi, status, email, dosen_id: old.dosen_id || null };
}
async function ubahMahasiswa(m) { const data = formData(m); if (!data) return; try { await api(`/mahasiswa?id=${m.id}`, { method: 'PUT', body: JSON.stringify(data) }); alert('Data berhasil diubah.'); muatDataMahasiswa(); } catch (e) { alert(e.message); } }
async function hapusMahasiswa(id) { if (!confirm('Yakin ingin menghapus data ini?')) return; try { await api(`/mahasiswa?id=${id}`, { method: 'DELETE' }); alert('Data berhasil dihapus.'); muatDataMahasiswa(); } catch (e) { alert(e.message); } }
document.querySelector('#btn-tambah').addEventListener('click', tambahMahasiswa);
