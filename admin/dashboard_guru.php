<?php
session_start();

// Keamanan: Hanya guru pendamping yang boleh mengakses dashboard ini
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_guru) {
    if ($is_admin) {
        header('Location: admin/dashboard_admin.php');
        exit();
    } elseif ($is_siswa) {
        header('Location: dashboard_siswa.php');
        exit();
    } else {
        header('Location: login.php');
        exit();
    }
}
include 'partials/db.php';

// Asumsi Anda menyimpan ID dan nama guru di session saat login
$guru_id = $_SESSION['id_guru_pendamping'] ?? 0;
$guru_nama = $_SESSION['nama_guru'] ?? 'Guru Pendamping';

$total_siswa_bimbingan = 0;
$total_absen_hari_ini = 0;
$total_laporan_harian_bimbingan = 0;
$total_tugas_proyek_bimbingan = 0;
$total_siswa_dinilai_bimbingan = 0; // --- VARIABEL BARU ---

// 1. Query untuk menghitung total siswa bimbingan yang aktif
$query_siswa = "SELECT COUNT(*) as total_siswa FROM siswa WHERE pembimbing_id = ? AND status = 'aktif'";
$stmt_siswa = $koneksi->prepare($query_siswa);
if ($stmt_siswa) {
    $stmt_siswa->bind_param("i", $guru_id);
    $stmt_siswa->execute();
    $total_siswa_bimbingan = $stmt_siswa->get_result()->fetch_assoc()['total_siswa'] ?? 0;
    $stmt_siswa->close();
}

// 2. Query untuk menghitung total absensi hari ini dari siswa bimbingan
$today_date = date('Y-m-d');
$query_absen = "SELECT COUNT(a.id_absensi) as total_absen
                FROM absensi_siswa a
                JOIN siswa s ON a.siswa_id = s.id_siswa
                WHERE s.pembimbing_id = ? AND a.tanggal_absen = ?";
$stmt_absen = $koneksi->prepare($query_absen);
if ($stmt_absen) {
    $stmt_absen->bind_param("is", $guru_id, $today_date);
    $stmt_absen->execute();
    $total_absen_hari_ini = $stmt_absen->get_result()->fetch_assoc()['total_absen'] ?? 0;
    $stmt_absen->close();
}

// 3. Query untuk menghitung total laporan kegiatan harian dari siswa bimbingan
$query_laporan_harian_bimbingan = "SELECT COUNT(jh.id_jurnal_harian) as total_laporan
                                   FROM jurnal_harian jh
                                   JOIN siswa s ON jh.siswa_id = s.id_siswa
                                   WHERE s.pembimbing_id = ?";
$stmt_laporan_harian_bimbingan = $koneksi->prepare($query_laporan_harian_bimbingan);
if ($stmt_laporan_harian_bimbingan) {
    $stmt_laporan_harian_bimbingan->bind_param("i", $guru_id);
    $stmt_laporan_harian_bimbingan->execute();
    $total_laporan_harian_bimbingan = $stmt_laporan_harian_bimbingan->get_result()->fetch_assoc()['total_laporan'] ?? 0;
    $stmt_laporan_harian_bimbingan->close();
}

// 4. Query untuk menghitung total laporan tugas proyek dari siswa bimbingan
$query_tugas_proyek_bimbingan = "SELECT COUNT(jk.id_jurnal_kegiatan) as total_tugas
                                 FROM jurnal_kegiatan jk
                                 JOIN siswa s ON jk.siswa_id = s.id_siswa
                                 WHERE s.pembimbing_id = ?";
$stmt_tugas_proyek_bimbingan = $koneksi->prepare($query_tugas_proyek_bimbingan);
if ($stmt_tugas_proyek_bimbingan) {
    $stmt_tugas_proyek_bimbingan->bind_param("i", $guru_id);
    $stmt_tugas_proyek_bimbingan->execute();
    $total_tugas_proyek_bimbingan = $stmt_tugas_proyek_bimbingan->get_result()->fetch_assoc()['total_tugas'] ?? 0;
    $stmt_tugas_proyek_bimbingan->close();
}

// --- KODE BARU: 5. Query untuk menghitung siswa bimbingan yang sudah dinilai ---
$query_siswa_dinilai = "SELECT COUNT(DISTINCT s.id_siswa) as total_dinilai
                        FROM nilai_siswa ns
                        JOIN siswa s ON ns.siswa_id = s.id_siswa
                        WHERE s.pembimbing_id = ?";
$stmt_siswa_dinilai = $koneksi->prepare($query_siswa_dinilai);
if ($stmt_siswa_dinilai) {
    $stmt_siswa_dinilai->bind_param("i", $guru_id);
    $stmt_siswa_dinilai->execute();
    $total_siswa_dinilai_bimbingan = $stmt_siswa_dinilai->get_result()->fetch_assoc()['total_dinilai'] ?? 0;
    $stmt_siswa_dinilai->close();
}
// --- AKHIR KODE BARU ---

// =================================================================
// BAGIAN UNTUK DATA GRAFIK
// =================================================================
$chart_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_data[$date] = 0;
}
$query_weekly_chart = "SELECT DATE(a.tanggal_absen) as tanggal_grup, COUNT(a.id_absensi) as total_harian
                       FROM absensi_siswa a
                       JOIN siswa s ON a.siswa_id = s.id_siswa
                       WHERE s.pembimbing_id = ? AND a.tanggal_absen >= CURDATE() - INTERVAL 6 DAY
                       GROUP BY DATE(a.tanggal_absen)
                       ORDER BY tanggal_grup ASC";
$stmt_weekly_chart = $koneksi->prepare($query_weekly_chart);
if ($stmt_weekly_chart) {
    $stmt_weekly_chart->bind_param("i", $guru_id);
    $stmt_weekly_chart->execute();
    $result_weekly_chart = $stmt_weekly_chart->get_result();
    while ($row = $result_weekly_chart->fetch_assoc()) {
        if (isset($chart_data[$row['tanggal_grup']])) {
            $chart_data[$row['tanggal_grup']] = $row['total_harian'];
        }
    }
    $stmt_weekly_chart->close();
}
$chart_categories = [];
foreach (array_keys($chart_data) as $date) {
    $chart_categories[] = date('D', strtotime($date));
}
$chart_series_data = array_values($chart_data);

$koneksi->close();
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/" data-template="vertical-menu-template-free">

<?php include 'partials/head.php'; ?>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include 'partials/sidebar.php'; ?>
            <div class="layout-page">
                <?php include 'partials/navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <div class="row mb-4">
                            <div class="col-lg-12">
                                <div class="card bg-gradient-primary-to-secondary text-white shadow-lg border-0" style="border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                                    <div class="card-body p-5 position-relative">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="me-3 animate__animated animate__fadeInLeft">
                                                <i class="bx bx-user-check bx-lg" style="font-size: 4rem;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h3 class="card-title text-white mb-1 animate__animated animate__fadeInRight">Selamat Datang, <?= htmlspecialchars($guru_nama) ?>!</h3>
                                                <p class="card-text text-white-75 animate__animated animate__fadeInUp">Anda dapat memantau aktivitas siswa bimbingan Anda di sini.</p>
                                            </div>
                                        </div>
                                        <div class="position-absolute bottom-0 end-0 p-3" style="opacity: 0.1;">
                                            <i class="bx bx-bar-chart-alt-2 bx-lg" style="font-size: 8rem; color: white;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-lg-4 col-md-6 col-12">
                                <div class="card h-100 shadow-sm border-0 animate__animated animate__fadeInUp animate__delay-0-3s">
                                    <div class="card-body d-flex flex-column align-items-start p-4">
                                        <div class="avatar flex-shrink-0 mb-3 rounded-circle d-flex justify-content-center align-items-center bg-label-primary" style="width: 50px; height: 50px; font-size: 1.8rem;">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <span class="text-muted fw-semibold d-block mb-1 fs-6">Total Siswa Bimbingan</span>
                                        <h3 class="card-title fw-bold mb-0 display-5 text-dark"><?= $total_siswa_bimbingan ?></h3>
                                        <small class="text-muted d-block mt-1" style="font-size: 0.85rem;">Siswa aktif di bawah bimbingan Anda</small>
                                        <a href="master_data_siswa.php?pembimbing_id=<?= $guru_id ?>" class="btn btn-sm btn-outline-primary mt-3">Lihat Detail <i class="bx bx-chevron-right"></i></a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6 col-12">
                                <div class="card h-100 shadow-sm border-0 animate__animated animate__fadeInUp animate__delay-0-5s">
                                    <div class="card-body d-flex flex-column align-items-start p-4">
                                        <div class="avatar flex-shrink-0 mb-3 rounded-circle d-flex justify-content-center align-items-center bg-label-success" style="width: 50px; height: 50px; font-size: 1.8rem;">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                        <span class="text-muted fw-semibold d-block mb-1 fs-6">Absensi Siswa Hari Ini</span>
                                        <h3 class="card-title fw-bold mb-0 display-5 text-dark"><?= $total_absen_hari_ini ?></h3>
                                        <small class="text-muted d-block mt-1" style="font-size: 0.85rem;">Siswa yang telah absen hari ini</small>
                                        <a href="master_data_absensi_siswa.php?pembimbing_id=<?= $guru_id ?>&tanggal=<?= $today_date ?>" class="btn btn-sm btn-outline-success mt-3">Lihat Detail <i class="bx bx-chevron-right"></i></a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-12 col-12">
                                <div class="card h-100 shadow-sm border-0 animate__animated animate__fadeInUp animate__delay-0-7s">
                                    <div class="card-body d-flex flex-column align-items-start p-4">
                                        <div class="avatar flex-shrink-0 mb-3 rounded-circle d-flex justify-content-center align-items-center bg-label-info" style="width: 50px; height: 50px; font-size: 1.8rem;">
                                            <i class="fas fa-user-check"></i>
                                        </div>
                                        <span class="text-muted fw-semibold d-block mb-1 fs-6">Siswa Sudah Dinilai</span>
                                        <h3 class="card-title fw-bold mb-0 display-5 text-dark"><?= $total_siswa_dinilai_bimbingan ?></h3>
                                        <small class="text-muted d-block mt-1" style="font-size: 0.85rem;">Siswa bimbingan yang telah dinilai</small>
                                        <a href="laporan_nilai.php" class="btn btn-sm btn-outline-info mt-3">Lihat Laporan Nilai <i class="bx bx-chevron-right"></i></a>
                                    </div>
                                </div>
                            </div>
                            </div>

                        <div class="row g-4 mt-2">
                            <div class="col-lg-6 col-md-6 col-12">
                                <div class="card h-100 shadow-sm border-0 animate__animated animate__fadeInUp animate__delay-0-7s">
                                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                                        <div>
                                            <h5 class="card-title text-info mb-2">Total Jurnal PKL Harian</h5>
                                            <h3 class="fw-bold mb-0 display-5 text-dark"><?= $total_laporan_harian_bimbingan ?></h3>
                                            <small class="text-muted">Dari siswa bimbingan Anda</small>
                                        </div>
                                        <div class="avatar flex-shrink-0">
                                            <span class="avatar-initial rounded-circle bg-label-info" style="width: 50px; height: 50px; font-size: 1.8rem;">
                                                <i class="bx bx-receipt bx-lg"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-light text-end">
                                        <a href="master_kegiatan_harian.php?pembimbing_id=<?= $guru_id ?>" class="btn btn-sm btn-outline-info">Lihat Jurnal <i class="bx bx-chevron-right"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-12">
                                <div class="card h-100 shadow-sm border-0 animate__animated animate__fadeInUp animate__delay-0-9s">
                                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                                        <div>
                                            <h5 class="card-title text-danger mb-2">Total Jurnal Per Kegiatan</h5>
                                            <h3 class="fw-bold mb-0 display-5 text-dark"><?= $total_tugas_proyek_bimbingan ?></h3>
                                            <small class="text-muted">Dari siswa bimbingan Anda</small>
                                        </div>
                                        <div class="avatar flex-shrink-0">
                                            <span class="avatar-initial rounded-circle bg-label-danger" style="width: 50px; height: 50px; font-size: 1.8rem;">
                                                <i class="bx bx-task bx-lg"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-light text-end">
                                        <a href="master_tugas_project.php?pembimbing_id=<?= $guru_id ?>" class="btn btn-sm btn-outline-danger">Lihat Tugas <i class="bx bx-chevron-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-4 mt-2">
                            <div class="col-12">
                                <div class="card h-100 shadow-sm border-0 animate__animated animate__fadeInUp animate__delay-0-7s">
                                    <div class="card-header border-bottom">
                                        <h5 class="card-title mb-0">Tren Absensi Mingguan (7 Hari Terakhir)</h5>
                                        <small class="text-muted">Total absensi siswa bimbingan per hari</small>
                                    </div>
                                    <div class="card-body">
                                        <div id="weeklyAttendanceChart" style="min-height: 280px;"></div>
                                        <p class="text-muted text-center mt-3 mb-0" style="font-size: 0.85rem;">Grafik menunjukkan jumlah siswa bimbingan Anda yang melakukan absensi setiap hari.</p>
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
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <?php include 'partials/script.php'; ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var options = {
            chart: { type: 'area', height: 280, toolbar: { show: false }},
            series: [{ name: 'Jumlah Siswa Absen', data: <?= json_encode(array_map('intval', $chart_series_data)) ?> }],
            xaxis: { categories: <?= json_encode($chart_categories) ?> },
            tooltip: { y: { formatter: function(val) { return val + " Siswa" }}},
            dataLabels: { enabled: true, background: { enabled: true, borderRadius: 2, padding: 4, opacity: 0.7, borderWidth: 1, borderColor: '#fff' }},
            stroke: { curve: 'smooth', width: 3 },
            colors: ['#28a745'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.3, stops: [0, 90, 100] }},
            grid: { borderColor: '#f1f1f1', row: { colors: ['transparent', 'transparent'], opacity: 0.5 }},
        };
        var chart = new ApexCharts(document.querySelector("#weeklyAttendanceChart"), options);
        chart.render();
    });
    </script>
</body>

</html>