<?php
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

include 'partials/db.php'; // Sertakan file koneksi database

$total_siswa = 0;
$total_guru = 0;
$total_tempat = 0;

// Query untuk menghitung total siswa aktif (menggunakan prepared statement)
$query_siswa = "SELECT COUNT(*) as total_siswa FROM siswa WHERE status = 'aktif'";
$stmt_siswa = $koneksi->prepare($query_siswa);
if ($stmt_siswa) {
    $stmt_siswa->execute();
    $hasil_siswa = $stmt_siswa->get_result();
    $data_siswa = $hasil_siswa->fetch_assoc();
    $total_siswa = $data_siswa['total_siswa'] ?? 0;
    $stmt_siswa->close();
} else {
    error_log("Error preparing siswa query: " . $koneksi->error);
}

// Query untuk menghitung total guru pembimbing (menggunakan prepared statement)
$query_guru = "SELECT COUNT(*) as total_guru FROM guru_pembimbing";
$stmt_guru = $koneksi->prepare($query_guru);
if ($stmt_guru) {
    $stmt_guru->execute();
    $hasil_guru = $stmt_guru->get_result();
    $data_guru = $hasil_guru->fetch_assoc();
    $total_guru = $data_guru['total_guru'] ?? 0;
    $stmt_guru->close();
} else {
    error_log("Error preparing guru query: " . $koneksi->error);
}

// Query untuk menghitung total tempat PKL (menggunakan prepared statement)
$query_tempat = "SELECT COUNT(*) as total_tempat FROM tempat_pkl";
$stmt_tempat = $koneksi->prepare($query_tempat);
if ($stmt_tempat) {
    $stmt_tempat->execute();
    $hasil_tempat = $stmt_tempat->get_result();
    $data_tempat = $hasil_tempat->fetch_assoc();
    $total_tempat = $data_tempat['total_tempat'] ?? 0;
    $stmt_tempat->close();
} else {
    error_log("Error preparing tempat_pkl query: " . $koneksi->error);
}

$koneksi->close(); // Tutup koneksi database setelah semua query selesai
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
                            <div class="col-lg-12 mb-4">
                                <div class="card bg-gradient-primary-to-secondary text-white shadow-lg border-0"
                                    style="border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                                    <div class="card-body p-5 position-relative">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="me-3 animate__animated animate__fadeInLeft">
                                                <i class="bx bx-user-shield bx-lg" style="font-size: 4rem;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h3
                                                    class="card-title text-white mb-1 animate__animated animate__fadeInRight">
                                                    Selamat Datang, Administrator!</h3>
                                                <p class="card-text text-white-75 animate__animated animate__fadeInUp">
                                                    Pantau dan kelola semua data dengan mudah di sini.
                                                </p>
                                            </div>
                                        </div>
                                        <div class="position-absolute bottom-0 end-0 p-3" style="opacity: 0.1;">
                                            <i class="bx bx-chart bx-lg" style="font-size: 8rem; color: white;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4 col-md-6 col-12 mb-4">
                                <div
                                    class="card h-100 shadow-sm border-0 animate__animated animate__fadeInUp animate__delay-0-3s">
                                    <div class="card-body d-flex flex-column align-items-start p-4">
                                        <div class="avatar flex-shrink-0 mb-3 rounded-circle d-flex justify-content-center align-items-center bg-label-primary"
                                            style="width: 50px; height: 50px; font-size: 1.8rem;">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                        <span class="text-muted fw-semibold d-block mb-1 fs-6">Total Siswa Aktif</span>
                                        <h3 class="card-title fw-bold mb-0 display-5 text-dark"><?= $total_siswa ?></h3>
                                        <small class="text-muted d-block mt-1" style="font-size: 0.85rem;">Siswa yang
                                            sedang PKL</small>
                                        <a href="master_data_siswa.php"
                                            class="btn btn-sm btn-outline-primary mt-3">Lihat Detail <i
                                                class="bx bx-chevron-right"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-12 mb-4">
                                <div
                                    class="card h-100 shadow-sm border-0 animate__animated animate__fadeInUp animate__delay-0-5s">
                                    <div class="card-body d-flex flex-column align-items-start p-4">
                                        <div class="avatar flex-shrink-0 mb-3 rounded-circle d-flex justify-content-center align-items-center bg-label-success"
                                            style="width: 50px; height: 50px; font-size: 1.8rem;">
                                            <i class="fas fa-chalkboard-teacher"></i>
                                        </div>
                                        <span class="text-muted fw-semibold d-block mb-1 fs-6">Total Guru
                                            Pembimbing</span>
                                        <h3 class="card-title fw-bold mb-0 display-5 text-dark"><?= $total_guru ?></h3>
                                        <small class="text-muted d-block mt-1" style="font-size: 0.85rem;">Guru
                                            pembimbing terdaftar</small>
                                        <a href="master_guru_pendamping.php"
                                            class="btn btn-sm btn-outline-success mt-3">Lihat Detail <i
                                                class="bx bx-chevron-right"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-12 mb-4">
                                <div
                                    class="card h-100 shadow-sm border-0 animate__animated animate__fadeInUp animate__delay-0-7s">
                                    <div class="card-body d-flex flex-column align-items-start p-4">
                                        <div class="avatar flex-shrink-0 mb-3 rounded-circle d-flex justify-content-center align-items-center bg-label-warning"
                                            style="width: 50px; height: 50px; font-size: 1.8rem;">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <span class="text-muted fw-semibold d-block mb-1 fs-6">Jumlah Tempat PKL</span>
                                        <h3 class="card-title fw-bold mb-0 display-5 text-dark"><?= $total_tempat ?>
                                        </h3>
                                        <small class="text-muted d-block mt-1" style="font-size: 0.85rem;">Mitra
                                            perusahaan terdaftar</small>
                                        <a href="master_tempat_pkl.php"
                                            class="btn btn-sm btn-outline-warning mt-3">Lihat Detail <i
                                                class="bx bx-chevron-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="layout-overlay layout-menu-toggle"></div>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <?php include './partials/script.php'; ?>
</body>

</html>