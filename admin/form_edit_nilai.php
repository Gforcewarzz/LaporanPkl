<?php
session_start();
require_once 'partials/db.php';

// Keamanan: Admin dan Guru boleh akses
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_admin && !$is_guru) {
    header('Location: ../login.php');
    exit();
}

// Ambil ID siswa dari URL untuk diedit
$siswa_id_to_edit = isset($_GET['siswa_id']) ? (int)$_GET['siswa_id'] : 0;
if ($siswa_id_to_edit === 0) {
    die("Error: ID Siswa tidak valid atau tidak ditemukan.");
}

// Ambil detail siswa yang akan diedit
$stmt = $koneksi->prepare("SELECT id_siswa, nama_siswa, jurusan_id FROM siswa WHERE id_siswa = ?");
$stmt->bind_param("i", $siswa_id_to_edit);
$stmt->execute();
$siswa = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$siswa) {
    die("Data siswa dengan ID tersebut tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/" data-template="vertical-menu-template-free">

<?php include 'partials/head.php'; ?>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include './partials/sidebar.php'; ?>
            <div class="layout-page">
                <?php include './partials/navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                            <h4 class="fw-bold mb-0 text-primary">
                                <span class="text-muted fw-light">Penilaian /</span> Edit Penilaian Kompetensi
                            </h4>
                            <i class="fas fa-edit fa-2x text-info" style="opacity: 0.6;"></i>
                        </div>

                        <div class="card shadow-lg">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">Edit Nilai Siswa</h5>
                                <small class="text-muted">Nilai yang sudah ada akan ditampilkan. Ubah nilai lalu simpan.</small>
                            </div>
                            <div class="card-body p-4">
                                <form action="proses_nilai.php" method="POST">
                                    <input type="hidden" name="id_siswa" value="<?= $siswa['id_siswa'] ?>">
                                    
                                    <div class="mb-4" id="info-siswa" data-siswa-id="<?= $siswa['id_siswa'] ?>" data-jurusan-id="<?= $siswa['jurusan_id'] ?>">
                                        <label for="nama_siswa" class="form-label fw-bold"><i class="bx bx-user me-1"></i>Siswa yang Dinilai:</label>
                                        <input type="text" id="nama_siswa" class="form-control" value="<?= htmlspecialchars($siswa['nama_siswa']) ?>" readonly>
                                    </div>

                                    <hr class="my-4">

                                    <div id="container-kompetensi">
                                        <div class="text-center text-muted p-4 border rounded-3 bg-light">
                                            <i class="bx bx-loader-alt bx-spin fs-1 mb-3"></i>
                                            <p class="mb-0">Memuat daftar kompetensi dan nilai yang sudah ada...</p>
                                        </div>
                                    </div>

                                    <div id="submit-wrapper" class="d-flex justify-content-end gap-2 mt-4">
                                        <a href="laporan_penilaian_siswa.php" class="btn btn-outline-secondary">
                                            <i class="bx bx-arrow-back me-1"></i> Batal
                                        </a>
                                        <button type="submit" name="submit" class="btn btn-primary">
                                            <i class="bx bx-save me-1"></i> Perbarui Penilaian
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php include './partials/footer.php'; ?>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <?php include 'partials/script.php' ?>
    <script src="penilaian_edit.js"></script>
</body>
</html>