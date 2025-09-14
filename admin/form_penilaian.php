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

                        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                            <h4 class="fw-bold mb-0 text-primary">
                                <span class="text-muted fw-light">Penilaian /</span> Daftar Siswa
                            </h4>

                            <a href="laporan_penilaian_siswa.php" class="btn btn-secondary">
                                <i class="bx bx-file me-1"></i> Lihat Laporan Penilaian
                            </a>
                        </div>

                        <div class="card shadow-lg">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">Pilih Siswa Untuk Dinilai</h5>
                                <small class="text-muted">Gunakan pencarian untuk menemukan siswa dengan cepat, lalu
                                    klik tombol "Nilai" untuk masuk ke form penilaian.</small>
                            </div>
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <label for="search_siswa" class="form-label fw-bold"><i
                                            class="bx bx-search me-1"></i> Cari Siswa:</label>
                                    <input type="text" class="form-control" id="search_siswa"
                                        placeholder="<?= $is_guru ? 'Ketik nama siswa bimbingan Anda...' : 'Ketik nama semua siswa...' ?>"
                                        autocomplete="off">
                                </div>

                                <div class="table-responsive text-nowrap">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nama Siswa</th>
                                                <th class="text-center">Status Penilaian</th>
                                                <th class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="siswa-table-body">
                                        </tbody>
                                    </table>
                                    <div id="loading-indicator" class="text-center p-4" style="display: none;">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Mencari...</span>
                                        </div>
                                    </div>
                                    <div id="empty-state" class="text-center text-muted p-4 border rounded-3 bg-light">
                                        <i class="bx bx-user-check fs-1 mb-3"></i>
                                        <p class="mb-0">Mulai ketik nama di atas untuk menampilkan daftar siswa.</p>
                                    </div>
                                </div>
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
    <script src="penilaian.js"></script>
</body>

</html>