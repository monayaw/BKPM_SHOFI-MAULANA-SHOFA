// Acara 29 - fungsi bantu bersama untuk seluruh halaman (dimuat sebelum script halaman)

// Escape karakter khusus HTML agar data dari API tidak dieksekusi sebagai tag.
function escapeHTML(teks) {
    const div = document.createElement('div');
    div.textContent = teks ?? '';
    return div.innerHTML.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

// fetch dengan retry otomatis (default 1x) hanya untuk kegagalan jaringan.
// Retry hanya dipakai untuk method idempoten (GET/HEAD/PUT/DELETE) supaya
// POST tidak terkirim dobel. Method lain boleh dipaksa dengan { force: true }.
async function fetchRetry(url, options = {}, { retries = 1, delay = 800, force = false } = {}) {
    const method = (options.method || 'GET').toUpperCase();
    const aman = ['GET', 'HEAD', 'PUT', 'DELETE'].includes(method);
    let sisa = aman || force ? retries : 0;

    while (true) {
        try {
            return await fetch(url, options);
        } catch (error) {
            // fetch hanya melempar error saat jaringan gagal (bukan saat HTTP 4xx/5xx)
            if (sisa <= 0) {
                throw new Error('Koneksi ke server gagal. Periksa jaringan Anda lalu coba lagi.');
            }
            sisa--;
            console.warn('Permintaan gagal, mencoba ulang...', error);
            await new Promise((resolve) => setTimeout(resolve, delay));
        }
    }
}

// Membaca JSON dengan pesan error yang jelas bila server membalas selain JSON
// (misalnya halaman HTML error atau proteksi anti-bot hosting).
async function ambilJSON(response) {
    const teks = await response.text();
    try {
        return JSON.parse(teks);
    } catch (error) {
        throw new Error(`Respons server bukan JSON yang valid (HTTP ${response.status}).`);
    }
}
