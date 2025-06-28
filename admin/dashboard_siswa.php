<?php
session_start();

// Logika Keamanan Halaman
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

// Hanya siswa yang boleh mengakses dashboard ini
if (!$is_siswa) {
    // Jika bukan siswa, cek peran lain untuk redirect
    if ($is_admin) {
        header('Location: index.php'); // Redirect admin ke dashboard admin
        exit();
    } elseif ($is_guru) {
        header('Location: ../halaman_guru.php'); // Redirect guru ke halaman guru
        exit();
    } else {
        header('Location: ../login.php'); // Jika tidak login sama sekali, redirect ke halaman login
        exit();
    }
}

// Pastikan id_siswa dan nama siswa tersedia dari sesi
$siswa_id = $_SESSION['id_siswa'] ?? null;
$siswa_nama = $_SESSION['siswa_nama'] ?? "Pengguna"; // Default nama jika tidak ada

// Jika siswa_id tidak ada, meskipun status login logged_in (kasus jarang, tapi untuk keamanan)
if (empty($siswa_id)) {
    session_destroy(); // Hancurkan sesi yang tidak valid
    header('Location: ../login.php');
    exit();
}

include 'partials/db.php'; // Sertakan file koneksi database

$total_laporan_harian = 0;
$total_tugas_proyek = 0;

// Ambil jumlah laporan kegiatan harian
$query_harian = "SELECT COUNT(*) AS total FROM jurnal_harian WHERE siswa_id = ?";
$stmt_harian = $koneksi->prepare($query_harian);
if ($stmt_harian) {
    $stmt_harian->bind_param("i", $siswa_id);
    $stmt_harian->execute();
    $result_harian = $stmt_harian->get_result();
    $data_harian = $result_harian->fetch_assoc();
    $total_laporan_harian = $data_harian['total'];
    $stmt_harian->close();
} else {
    error_log("Error preparing harian query: " . $koneksi->error);
}

// Ambil jumlah laporan tugas proyek
$query_proyek = "SELECT COUNT(*) AS total FROM jurnal_kegiatan WHERE siswa_id = ?";
$stmt_proyek = $koneksi->prepare($query_proyek);
if ($stmt_proyek) {
    $stmt_proyek->bind_param("i", $siswa_id);
    $stmt_proyek->execute();
    $result_proyek = $stmt_proyek->get_result();
    $data_proyek = $result_proyek->fetch_assoc();
    $total_tugas_proyek = $data_proyek['total'];
    $stmt_proyek->close();
} else {
    error_log("Error preparing proyek query: " . $koneksi->error);
}

$koneksi->close(); // Tutup koneksi database
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
                                                <i class="bx bx-user-circle bx-lg" style="font-size: 4rem;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h3
                                                    class="card-title text-white mb-1 animate__animated animate__fadeInRight">
                                                    Selamat Datang, <?= htmlspecialchars($siswa_nama) ?>!</h3>
                                                <p class="card-text text-white-75 animate__animated animate__fadeInUp">
                                                    Semangat menjalankan Praktik Kerja Lapanganmu. Catat setiap
                                                    progresmu di sini!
                                                </p>
                                            </div>
                                        </div>
                                        <div class="position-absolute bottom-0 end-0 p-3" style="opacity: 0.1;">
                                            <i class="bx bx-check-circle bx-lg"
                                                style="font-size: 8rem; color: white;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
                                <div class="card shadow-md animate__animated animate__fadeInUp animate__delay-0-5s">
                                    <div class="card-body d-flex align-items-center justify-content-between">
                                        <div>
                                            <h5 class="card-title text-primary mb-2">Laporan Kegiatan Harian</h5>
                                            <h2 class="fw-bold mb-0"><?= $total_laporan_harian ?></h2>
                                            <small class="text-muted">Total laporan yang kamu buat</small>
                                        </div>
                                        <div class="avatar avatar-md flex-shrink-0">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                <i class="bx bx-receipt bx-lg"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-light text-end">
                                        <a href="master_kegiatan_harian.php"
                                            class="btn btn-sm btn-outline-primary">Lihat Detail <i
                                                class="bx bx-chevron-right"></i></a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
                                <div class="card shadow-md animate__animated animate__fadeInUp animate__delay-0-7s">
                                    <div class="card-body d-flex align-items-center justify-content-between">
                                        <div>
                                            <h5 class="card-title text-success mb-2">Laporan Tugas Proyek</h5>
                                            <h2 class="fw-bold mb-0"><?= $total_tugas_proyek ?></h2>
                                            <small class="text-muted">Total tugas proyek yang kamu selesaikan</small>
                                        </div>
                                        <div class="avatar avatar-md flex-shrink-0">
                                            <span class="avatar-initial rounded-circle bg-label-success">
                                                <i class="bx bx-task bx-lg"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-light text-end">
                                        <a href="master_tugas_project.php" class="btn btn-sm btn-outline-success">Lihat
                                            Detail <i class="bx bx-chevron-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-4">
                                <h5 class="mb-3 animate__animated animate__fadeInLeft animate__delay-1s">Akses Cepat
                                </h5>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                                    <a href="master_kegiatan_harian_add.php"
                                        class="btn btn-info btn-lg flex-fill animate__animated animate__zoomIn animate__delay-1-1s">
                                        <i class="bx bx-plus-circle me-2"></i> Tambah Laporan Harian
                                    </a>
                                    <a href="master_tugas_project_add.php"
                                        class="btn btn-warning btn-lg flex-fill animate__animated animate__zoomIn animate__delay-1-2s">
                                        <i class="bx bx-edit-alt me-2"></i> Tambah Tugas Proyek
                                    </a>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include './partials/script.php'; ?>
</body>

</html>