// Otomatis mengikuti lokasi halaman (…/public/views/xxx.html -> …/public)
const BASE_URL = location.origin + location.pathname.replace(/\/views\/[^\/]*$/, '');
const formLogin = document.querySelector('#form-login');

formLogin.addEventListener('submit', async (event) => {
    event.preventDefault();

    const pesan = document.querySelector('#pesan');
    const tombol = formLogin.querySelector('button[type="submit"]');
    pesan.textContent = 'Memeriksa akun...';
    tombol.disabled = true;
    tombol.textContent = 'Memproses...';

    try {
        const response = await fetchRetry(`${BASE_URL}/login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                username: formLogin.username.value.trim(),
                password: formLogin.password.value
            })
        }, { force: true });

        const result = await ambilJSON(response);

        if (!response.ok || !result.success) {
            pesan.textContent = result.message || 'Login gagal.';
            return;
        }

        localStorage.setItem('token', result.data.token);
        localStorage.setItem('role', result.data.role);
        localStorage.setItem('username', formLogin.username.value.trim());
        window.location.href = 'dashboard.html';
    } catch (error) {
        pesan.textContent = error.message || 'Tidak dapat terhubung ke server API.';
        console.error(error);
    } finally {
        tombol.disabled = false;
        tombol.textContent = 'Login';
    }
});
