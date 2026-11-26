const BASE_URL = 'http://localhost/WebServer/acara25/public';
const formLogin = document.querySelector('#form-login');
formLogin.addEventListener('submit', async (e) => {
  e.preventDefault();
  const pesan = document.querySelector('#pesan');
  pesan.textContent = 'Memeriksa akun...';
  try {
    const res = await fetch(`${BASE_URL}/login`, {
      method: 'POST', headers: {'Content-Type':'application/json'},
      body: JSON.stringify({username: formLogin.username.value.trim(), password: formLogin.password.value})
    });
    const hasil = await res.json();
    if (!res.ok || !hasil.success) { pesan.textContent = hasil.message || 'Login gagal.'; return; }
    localStorage.setItem('token', hasil.data.token);
    localStorage.setItem('role', hasil.data.role);
    localStorage.setItem('username', formLogin.username.value.trim());
    window.location.href = 'dashboard.html';
  } catch (error) { pesan.textContent = 'Tidak dapat terhubung ke server API.'; console.error(error); }
});
