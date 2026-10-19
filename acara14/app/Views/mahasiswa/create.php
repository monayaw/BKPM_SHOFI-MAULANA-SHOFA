
<h2>Tambah Mahasiswa</h2>

<?php if (!empty($errors['general'])): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($errors['general']) ?>
    </div>
<?php endif; ?>

<form method="POST" action="/WebServer/acara14/public/mahasiswa">
    <div class="mb-3">
        <label for="nim" class="form-label">NIM</label>
        <input
            type="text"
            name="nim"
            id="nim"
            class="form-control"
            value="<?= htmlspecialchars($old['nim'] ?? '') ?>"
            required
        >
        <?php if (!empty($errors['nim'])): ?>
            <small class="text-danger">
                <?= htmlspecialchars($errors['nim']) ?>
            </small>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="nama" class="form-label">Nama</label>
        <input
            type="text"
            name="nama"
            id="nama"
            class="form-control"
            value="<?= htmlspecialchars($old['nama'] ?? '') ?>"
            required
        >
        <?php if (!empty($errors['nama'])): ?>
            <small class="text-danger">
                <?= htmlspecialchars($errors['nama']) ?>
            </small>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="prodi" class="form-label">Program Studi</label>
        <input
            type="text"
            name="prodi"
            id="prodi"
            class="form-control"
            value="<?= htmlspecialchars($old['prodi'] ?? '') ?>"
            required
        >
        <?php if (!empty($errors['prodi'])): ?>
            <small class="text-danger">
                <?= htmlspecialchars($errors['prodi']) ?>
            </small>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="status" class="form-label">Status</label>
        <select name="status" id="status" class="form-select" required>
            <option value="">-- Pilih Status --</option>
            <option value="aktif" <?= ($old['status'] ?? '') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
            <option value="cuti" <?= ($old['status'] ?? '') === 'cuti' ? 'selected' : '' ?>>Cuti</option>
            <option value="lulus" <?= ($old['status'] ?? '') === 'lulus' ? 'selected' : '' ?>>Lulus</option>
        </select>
        <?php if (!empty($errors['status'])): ?>
            <small class="text-danger">
                <?= htmlspecialchars($errors['status']) ?>
            </small>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input
            type="email"
            name="email"
            id="email"
            class="form-control"
            value="<?= htmlspecialchars($old['email'] ?? '') ?>"
        >
        <?php if (!empty($errors['email'])): ?>
            <small class="text-danger">
                <?= htmlspecialchars($errors['email']) ?>
            </small>
        <?php endif; ?>
    </div>
    
    <div class="mb-3">
        <label for="dosen_id" class="form-label">ID Dosen</label>
        <input
            type="number"
            name="dosen_id"
            id="dosen_id"
            class="form-control"
            value="<?= htmlspecialchars($old['dosen_id'] ?? '') ?>"
        >
        <?php if (!empty($errors['dosen_id'])): ?>
            <small class="text-danger">
                <?= htmlspecialchars($errors['dosen_id']) ?>
            </small>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="/WebServer/acara14/public/mahasiswa" class="btn btn-secondary">Kembali</a>
</form>
