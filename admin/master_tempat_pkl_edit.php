<?php
// 1. Selalu mulai sesi di baris paling awal sebelum output lainnya
session_start();

// Keamanan: Hanya admin yang boleh mengakses dashboard ini
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_admin) {
    if ($is_siswa) {
        header('Location: dashboard_siswa.php'); // Redirect siswa ke dashboard siswa
        exit();
    } elseif ($is_guru) {
        header('Location: ../halaman_guru.php'); // Redirect guru ke halaman guru
        exit();
    } else {
        header('Location: ../login.php'); // Jika tidak login sama sekali, redirect ke halaman login
        exit();
    }
}

// 5. Jika lolos semua pemeriksaan di atas, maka dia adalah ADMIN yang sah.
// Tampilkan semua konten halaman ini.
?>
<?php
include 'partials/db.php';

// Ambil ID dari parameter URL
$placeId = isset($_GET['id']) ? $_GET['id'] : null;
$placeData = null;
$jurusanList = [];

// Ambil data tempat PKL
if ($placeId) {
    $result = mysqli_query($koneksi, "SELECT * FROM tempat_pkl WHERE id_tempat_pkl = '$placeId'");
    if ($result && mysqli_num_rows($result) > 0) {
        $placeData = mysqli_fetch_assoc($result);
    }
}

// Ambil semua jurusan
$jurusanResult = mysqli_query($koneksi, "SELECT * FROM jurusan");
while ($j = mysqli_fetch_assoc($jurusanResult)) {
    $jurusanList[] = $j;
}
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/"
    data-template="vertical-menu-template-free">
<?php include 'partials/head.php'; ?>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include './partials/sidebar.php'; ?>
            <div class="layout-page">
                <?php include './partials/navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <div
                            class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom position-relative">
                            <h4 class="fw-bold mb-0 text-primary"><span class="text-muted fw-light">Master /</span> Edit
                                Tempat PKL</h4>
                        </div>

                        <?php if ($placeData): ?>
                            <form action="master_tempat_pkl_edit_act.php" method="POST" class="card p-4 shadow-lg">
                                <input type="hidden" name="id_tempat_pkl"
                                    value="<?= htmlspecialchars($placeData['id_tempat_pkl']) ?>">

                                <div class="mb-3">
                                    <label for="nama_tempat_pkl" class="form-label">Nama Perusahaan / Instansi</label>
                                    <input type="text" class="form-control" id="nama_tempat_pkl" name="nama_tempat_pkl"
                                        value="<?= htmlspecialchars($placeData['nama_tempat_pkl']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="alamat" class="form-label">Alamat</label>
                                    <input type="text" class="form-control" id="alamat" name="alamat"
                                        value="<?= htmlspecialchars($placeData['alamat']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="alamat_kontak" class="form-label">Kontak</label>
                                    <input type="text" class="form-control" id="alamat_kontak" name="alamat_kontak"
                                        value="<?= htmlspecialchars($placeData['alamat_kontak']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="nama_instruktur" class="form-label">Nama Instruktur</label>
                                    <input type="text" class="form-control" id="nama_instruktur" name="nama_instruktur"
                                        value="<?= htmlspecialchars($placeData['nama_instruktur']) ?>">
                                </div>

                                

                                
                                <div class="d-flex justify-content-end">
                                    <a href="master_tempat_pkl.php" class="btn btn-secondary me-2">Batal</a>
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning mt-4">Data tempat PKL tidak ditemukan atau ID tidak valid.</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="layout-overlay layout-menu-toggle"></div>
            </div>
        </div>
    </div>

    <?php include 'partials/script.php'; ?>
</body>

</html>