<?php
session_start();

// 1. Aturan utama: Cek apakah pengguna yang mengakses BUKAN seorang ADMIN.
if (!isset($_SESSION['admin_status_login']) || $_SESSION['admin_status_login'] !== 'logged_in') {

    // 2. Jika bukan admin, cek apakah dia adalah SISWA.
    if (isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in') {
        // Jika benar siswa, kembalikan ke halaman siswa.
        header('Location: master_kegiatan_harian.php');
        exit();
    }
    // 3. TAMBAHAN: Jika bukan siswa, cek apakah dia adalah GURU.
    elseif (isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in') {
        // Jika benar guru, kembalikan ke halaman guru.
        header('Location: ../../halaman_guru.php'); //belum di atur
        exit();
    }
    // 4. Jika bukan salah satu dari role di atas (admin, siswa, guru),
    // artinya pengguna belum login sama sekali. Arahkan ke halaman login.
    else {
        header('Location: ../login.php');
        exit();
    }
}
// 1. Sertakan file koneksi database
include 'partials/db.php'; // Pastikan path ini benar

// 2. Query untuk menghitung total siswa aktif
// Asumsi Anda punya kolom 'status' di tabel 'siswa'
$query_siswa = "SELECT COUNT(*) as total_siswa FROM siswa WHERE status = 'aktif'";
$hasil_siswa = mysqli_query($koneksi, $query_siswa);
$data_siswa = mysqli_fetch_assoc($hasil_siswa);
$total_siswa = $data_siswa['total_siswa'];

// 3. Query untuk menghitung total guru pembimbing
$query_guru = "SELECT COUNT(*) as total_guru FROM guru_pembimbing";
$hasil_guru = mysqli_query($koneksi, $query_guru);
$data_guru = mysqli_fetch_assoc($hasil_guru);
$total_guru = $data_guru['total_guru'];

// 4. Query untuk menghitung total tempat PKL
$query_tempat = "SELECT COUNT(*) as total_tempat FROM tempat_pkl";
$hasil_tempat = mysqli_query($koneksi, $query_tempat);
$data_tempat = mysqli_fetch_assoc($hasil_tempat);
$total_tempat = $data_tempat['total_tempat'];
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/"
    data-template="vertical-menu-template-free">

<?php include 'partials/head.php' ?>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include './partials/sidebar.php'; ?>
            <div class="layout-page">
                <?php include './partials/navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 col-12 mb-4">
                                <div class="card h-100 shadow-sm border-0">
                                    <div class="card-body d-flex flex-column align-items-start p-4">
                                        <div class="avatar flex-shrink-0 mb-3 rounded-circle d-flex justify-content-center align-items-center"
                                            style="background-color: #007bff; width: 50px; height: 50px; font-size: 1.8rem; color: white;">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                        <span class="text-muted fw-semibold d-block mb-1 fs-6">Total Siswa</span>
                                        <h3 class="card-title fw-bold mb-0 display-5 text-dark"><?php echo $total_siswa; ?></h3>
                                        <small class="text-muted d-block mt-1" style="font-size: 0.85rem;">Siswa aktif terdaftar</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-12 mb-4">
                                <div class="card h-100 shadow-sm border-0">
                                    <div class="card-body d-flex flex-column align-items-start p-4">
                                        <div class="avatar flex-shrink-0 mb-3 rounded-circle d-flex justify-content-center align-items-center"
                                            style="background-color: #28a745; width: 50px; height: 50px; font-size: 1.8rem; color: white;">
                                            <i class="fas fa-chalkboard-teacher"></i>
                                        </div>
                                        <span class="text-muted fw-semibold d-block mb-1 fs-6">Total Guru Pembimbing</span>
                                        <h3 class="card-title fw-bold mb-0 display-5 text-dark"><?php echo $total_guru; ?></h3>
                                        <small class="text-muted d-block mt-1" style="font-size: 0.85rem;">Guru pembimbing yang tersedia</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-12 mb-4">
                                <div class="card h-100 shadow-sm border-0">
                                    <div class="card-body d-flex flex-column align-items-start p-4">
                                        <div class="avatar flex-shrink-0 mb-3 rounded-circle d-flex justify-content-center align-items-center"
                                            style="background-color: #ffc107; width: 50px; height: 50px; font-size: 1.8rem; color: white;">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <span class="text-muted fw-semibold d-block mb-1 fs-6">Jumlah Tempat PKL</span>
                                        <h3 class="card-title fw-bold mb-0 display-5 text-dark"><?php echo $total_tempat; ?></h3>
                                        <small class="text-muted d-block mt-1" style="font-size: 0.85rem;">Mitra perusahaan aktif</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="layout-overlay layout-menu-toggle"></div>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
                <?php include './partials/script.php'; ?>
</body>
</html>