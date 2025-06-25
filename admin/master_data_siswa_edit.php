<?php
include 'partials/head.php';
include 'partials/db.php';
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/" data-template="vertical-menu-template-free">
<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include './partials/sidebar.php'; ?>
        <div class="layout-page">
            <?php include './partials/navbar.php'; ?>
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">

<?php
$id = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : null;

$query = "SELECT 
            siswa.*,
            jurusan.nama_jurusan,
            guru_pembimbing.nama_pembimbing,
            tempat_pkl.nama_tempat_pkl
          FROM siswa
          LEFT JOIN jurusan ON siswa.jurusan_id = jurusan.id_jurusan
          LEFT JOIN guru_pembimbing ON siswa.pembimbing_id = guru_pembimbing.id_pembimbing
          LEFT JOIN tempat_pkl ON siswa.tempat_pkl_id = tempat_pkl.id_tempat_pkl
          WHERE id_siswa = '$id'";

$result = mysqli_query($koneksi, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    echo '<div class="alert alert-danger">Data siswa tidak ditemukan.</div>';
    return;
}

$data = mysqli_fetch_assoc($result);

// Ambil data relasi untuk datalist
$jurusan_options = mysqli_query($koneksi, "SELECT nama_jurusan FROM jurusan");
$guru_options = mysqli_query($koneksi, "SELECT nama_pembimbing FROM guru_pembimbing");
$tempat_pkl_options = mysqli_query($koneksi, "SELECT nama_tempat_pkl FROM tempat_pkl");
?>

<!-- Judul -->
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h4 class="fw-bold mb-0 text-primary">
        <span class="text-muted fw-light">Master Data /</span> Edit Siswa
    </h4>
    <i class="fas fa-user-edit fa-2x text-info" style="opacity: 0.6;"></i>
</div>

<!-- Form -->
<div class="card shadow-lg">
    <div class="card-header border-bottom">
        <h5 class="mb-0">Formulir Edit Data Siswa</h5>
        <small class="text-muted">Silakan sesuaikan data siswa berikut.</small>
    </div>
    <div class="card-body p-4">
        <form action="master_data_siswa_edit_act.php" method="POST">
            <input type="hidden" name="id_siswa" value="<?= htmlspecialchars($data['id_siswa']) ?>">

            <div class="mb-3">
                <label class="form-label fw-bold">Nama Siswa</label>
                <input type="text" name="nama_siswa" class="form-control" required value="<?= htmlspecialchars($data['nama_siswa']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Jenis Kelamin</label><br>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="jenis_kelamin" value="Laki-laki" id="jk1" <?= $data['jenis_kelamin'] === 'Laki-laki' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="jk1">Laki-laki</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="jenis_kelamin" value="Perempuan" id="jk2" <?= $data['jenis_kelamin'] === 'Perempuan' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="jk2">Perempuan</label>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">NISN</label>
                <input type="text" name="nisn" class="form-control" required value="<?= htmlspecialchars($data['nisn']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">No Induk</label>
                <input type="text" name="no_induk" class="form-control" required value="<?= htmlspecialchars($data['no_induk']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Kelas</label>
                <input type="text" name="kelas" class="form-control" required value="<?= htmlspecialchars($data['kelas']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Jurusan</label>
                <input type="text" name="jurusan_nama" class="form-control" list="datalistJurusan" required value="<?= htmlspecialchars($data['nama_jurusan']) ?>">
                <datalist id="datalistJurusan">
                    <?php while($j = mysqli_fetch_assoc($jurusan_options)) : ?>
                        <option value="<?= htmlspecialchars($j['nama_jurusan']) ?>">
                    <?php endwhile; ?>
                </datalist>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Guru Pendamping</label>
                <input type="text" name="guru_nama" class="form-control" list="datalistGuru" required value="<?= htmlspecialchars($data['nama_pembimbing']) ?>">
                <datalist id="datalistGuru">
                    <?php while($g = mysqli_fetch_assoc($guru_options)) : ?>
                        <option value="<?= htmlspecialchars($g['nama_pembimbing']) ?>">
                    <?php endwhile; ?>
                </datalist>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Tempat PKL</label>
                <input type="text" name="tempat_pkl_nama" class="form-control" list="datalistTempatPKL" required value="<?= htmlspecialchars($data['nama_tempat_pkl']) ?>">
                <datalist id="datalistTempatPKL">
                    <?php while($t = mysqli_fetch_assoc($tempat_pkl_options)) : ?>
                        <option value="<?= htmlspecialchars($t['nama_tempat_pkl']) ?>">
                    <?php endwhile; ?>
                </datalist>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Status</label>
                <select name="status" class="form-select" required>
                    <option value="Aktif" <?= $data['status'] === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="Tidak Aktif" <?= $data['status'] === 'Tidak Aktif' ? 'selected' : '' ?>>Tidak Aktif</option>
                    <option value="Selesai" <?= $data['status'] === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                </select>
            </div>

            <!-- ✅ Tambahkan bagian untuk ubah password -->
            <div class="mb-3">
                <label class="form-label fw-bold">Password (Kosongkan jika tidak ingin mengganti)</label>
                <input type="password" name="password" class="form-control" placeholder="Masukkan password baru jika ingin mengganti">
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="master_data_siswa.php" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Kembali</a>
                <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/script.php'; ?>
</body>
</html>
