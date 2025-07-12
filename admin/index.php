<?php
session_start();

// Keamanan: Hanya admin yang boleh mengakses dashboard ini
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_admin) {
    if ($is_siswa) {
        header('Location: dashboard_siswa.php');
        exit();
    } elseif ($is_guru) {
        header('Location: dashboard_guru.php');
        exit();
    } else {
        header('Location: ../login.php');
        exit();
    }
}

include 'partials/db.php';

$admin_username = $_SESSION['user_name'] ?? 'Admin';

$total_siswa = 0;
$total_guru = 0;
$total_tempat = 0;
$total_laporan_harian_all = 0;
$total_tugas_proyek_all = 0;
$total_admin_all = 0;

// Query untuk menghitung total siswa aktif
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

// Query untuk menghitung total guru pembimbing
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

// Query untuk menghitung total tempat PKL
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

// Query untuk menghitung total laporan kegiatan harian (seluruh siswa)
$query_laporan_harian = "SELECT COUNT(*) as total_laporan FROM jurnal_harian";
$stmt_laporan_harian = $koneksi->prepare($query_laporan_harian);
if ($stmt_laporan_harian) {
    $stmt_laporan_harian->execute();
    $hasil_laporan_harian = $stmt_laporan_harian->get_result();
    $data_laporan_harian = $hasil_laporan_harian->fetch_assoc();
    $total_laporan_harian_all = $data_laporan_harian['total_laporan'] ?? 0;
    $stmt_laporan_harian->close();
} else {
    error_log("Error preparing total_laporan_harian query: " . $koneksi->error);
}

// Query untuk menghitung total laporan tugas proyek (seluruh siswa)
$query_tugas_proyek = "SELECT COUNT(*) as total_tugas FROM jurnal_kegiatan";
$stmt_tugas_proyek = $koneksi->prepare($query_tugas_proyek);
if ($stmt_tugas_proyek) {
    $stmt_tugas_proyek->execute();
    $hasil_tugas_proyek = $stmt_tugas_proyek->get_result();
    $data_tugas_proyek = $hasil_tugas_proyek->fetch_assoc();
    $total_tugas_proyek_all = $data_tugas_proyek['total_tugas'] ?? 0;
    $stmt_tugas_proyek->close();
} else {
    error_log("Error preparing total_tugas_proyek query: " . $koneksi->error);
}

// Query untuk menghitung total admin
$query_total_admin = "SELECT COUNT(*) as total_admin FROM admin";
$stmt_total_admin = $koneksi->prepare($query_total_admin);
if ($stmt_total_admin) {
    $stmt_total_admin->execute();
    $hasil_total_admin = $stmt_total_admin->get_result();
    $data_total_admin = $hasil_total_admin->fetch_assoc();
    $total_admin_all = $data_total_admin['total_admin'] ?? 0;
    $stmt_total_admin->close();
} else {
    error_log("Error preparing total_admin query: " . $koneksi->error);
}

// Query untuk data grafik tren laporan bulanan (Kegiatan Harian)
$monthly_reports_data = array_fill(1, 12, 0); // Inisialisasi array untuk 12 bulan dengan nilai 0
$bulan_indonesia = [
    1 => 'Jan',
    2 => 'Feb',
    3 => 'Mar',
    4 => 'Apr',
    5 => 'Mei',
    6 => 'Jun',
    7 => 'Jul',
    8 => 'Agu',
    9 => 'Sep',
    10 => 'Okt',
    11 => 'Nov',
    12 => 'Des'
];
$current_year = date('Y');

$query_monthly_reports = "SELECT 
                            MONTH(tanggal) as bulan, 
                            COUNT(*) as total_laporan_bulan
                          FROM jurnal_harian 
                          WHERE YEAR(tanggal) = ?
                          GROUP BY MONTH(tanggal) 
                          ORDER BY MONTH(tanggal) ASC";
$stmt_monthly_reports = $koneksi->prepare($query_monthly_reports);

if ($stmt_monthly_reports) {
    $stmt_monthly_reports->bind_param("i", $current_year);
    $stmt_monthly_reports->execute();
    $result_monthly_reports = $stmt_monthly_reports->get_result();

    while ($row = $result_monthly_reports->fetch_assoc()) {
        $monthly_reports_data[$row['bulan']] = $row['total_laporan_bulan'];
    }
    $stmt_monthly_reports->close();
} else {
    error_log("Error preparing monthly reports query: " . $koneksi->error);
}

$chart_series_data = array_values($monthly_reports_data);
$chart_categories = array_values($bulan_indonesia);

$koneksi->close();
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
                        <div class="row mb-4">
                            <div class="col-lg-12">
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
                                                    Selamat Datang, <?= htmlspecialchars($admin_username) ?>!</h3>
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

                        <div class="row g-4">
                            <div class="col-lg-4 col-md-6 col-12">
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
                            <div class="col-lg-4 col-md-6 col-12">
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
                            <div class="col-lg-4 col-md-6 col-12">
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

                        <div class="row g-4 mt-2">
                            <div class="col-lg-6 col-md-6 col-12">
                                <div
                                    class="card h-100 shadow-sm border-0 animate__animated animate__fadeInUp animate__delay-0-9s">
                                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                                        <div>
                                            <h5 class="card-title text-info mb-2">Total Jurnal PKL Harian</h5>
                                            <h3 class="fw-bold mb-0 display-5 text-dark">
                                                <?= $total_laporan_harian_all ?></h3>
                                            <small class="text-muted">Dari semua siswa</small>
                                        </div>
                                        <div class="avatar flex-shrink-0">
                                            <span class="avatar-initial rounded-circle bg-label-info"
                                                style="width: 50px; height: 50px; font-size: 1.8rem;">
                                                <i class="bx bx-receipt bx-lg"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-light text-end">
                                        <a href="master_kegiatan_harian.php" class="btn btn-sm btn-outline-info">Lihat
                                            Semua Jurnal Harian <i class="bx bx-chevron-right"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-12">
                                <div
                                    class="card h-100 shadow-sm border-0 animate__animated animate__fadeInUp animate__delay-1-1s">
                                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                                        <div>
                                            <h5 class="card-title text-danger mb-2">Total Jurnal Per Kegiatan</h5>
                                            <h3 class="fw-bold mb-0 display-5 text-dark"><?= $total_tugas_proyek_all ?>
                                            </h3>
                                            <small class="text-muted">Dari semua siswa</small>
                                        </div>
                                        <div class="avatar flex-shrink-0">
                                            <span class="avatar-initial rounded-circle bg-label-danger"
                                                style="width: 50px; height: 50px; font-size: 1.8rem;">
                                                <i class="bx bx-task bx-lg"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-light text-end">
                                        <a href="master_tugas_project.php" class="btn btn-sm btn-outline-danger">Lihat
                                            Semua Tugas <i class="bx bx-chevron-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mt-2">
                            <div class="col-lg-4 col-md-6 col-12">
                                <div
                                    class="card h-100 shadow-sm border-0 animate__animated animate__fadeInUp animate__delay-1-3s">
                                    <div class="card-body d-flex flex-column align-items-start p-4">
                                        <div class="avatar flex-shrink-0 mb-3 rounded-circle d-flex justify-content-center align-items-center bg-label-secondary"
                                            style="width: 50px; height: 50px; font-size: 1.8rem;">
                                            <i class="fas fa-users-cog"></i>
                                        </div>
                                        <span class="text-muted fw-semibold d-block mb-1 fs-6">Total Akun Admin</span>
                                        <h3 class="card-title fw-bold mb-0 display-5 text-dark"><?= $total_admin_all ?>
                                        </h3>
                                        <small class="text-muted d-block mt-1" style="font-size: 0.85rem;">Jumlah akun
                                            admin terdaftar</small>
                                        <a href="master_data_admin.php"
                                            class="btn btn-sm btn-outline-secondary mt-3">Lihat Detail <i
                                                class="bx bx-chevron-right"></i></a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-8 col-md-6 col-12">
                                <div
                                    class="card h-100 shadow-sm border-0 animate__animated animate__fadeInUp animate__delay-1-5s">
                                    <div class="card-header border-bottom">
                                        <h5 class="card-title mb-0">Tren Jurnal PKL Harian (Tahun
                                            <?= date('Y') ?>)</h5>
                                        <small class="text-muted">Total laporan yang diinput per bulan</small>
                                    </div>
                                    <div class="card-body">
                                        <div id="monthlyReportChart" style="min-height: 250px;"></div>
                                        <p class="text-muted text-center mt-3 mb-0" style="font-size: 0.85rem;">Grafik
                                            menunjukkan akumulasi laporan Jurnal PKL Harian dari semua siswa.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div
                                    class="card shadow-sm border-0 animate__animated animate__fadeInUp animate__delay-1-7s">
                                    <div class="card-body p-4 text-center">
                                        <h5 class="text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Status
                                            Sistem & Notifikasi Penting</h5>
                                        <p class="text-muted mb-0" style="font-size: 0.9rem;">
                                            Semua modul sistem berfungsi dengan baik. Pantau data secara berkala untuk
                                            informasi terbaru.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
    // ApexCharts script for monthlyReportChart (data dinamis dari PHP)
    document.addEventListener('DOMContentLoaded', function() {
        var options = {
            chart: {
                type: 'area',
                height: 250,
                toolbar: {
                    show: false
                }
            },
            series: [{
                name: 'Jumlah Laporan Harian',
                data: <?= json_encode($chart_series_data) ?>
            }],
            xaxis: {
                categories: <?= json_encode($chart_categories) ?>
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + " Laporan"
                    }
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth'
            },
            colors: ['#696cff'], // Warna primary Bootstrap
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.9,
                    stops: [0, 90, 100]
                }
            },
            grid: {
                borderColor: '#f1f1f1',
            }
        };

        var chart = new ApexCharts(document.querySelector("#monthlyReportChart"), options);
        chart.render();
    });
    </script>
    <?php include './partials/script.php'; ?>
</body>

</html>