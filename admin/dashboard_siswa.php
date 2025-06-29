<?php
session_start();

// Logika Keamanan Halaman
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

// Hanya siswa yang boleh mengakses dashboard ini
if (!$is_siswa) {
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
$total_minggu_pkl = 0;
$last_report_date = 'Belum ada laporan';

// Ambil jumlah laporan kegiatan harian
$query_harian = "SELECT COUNT(*) AS total, MAX(tanggal) AS last_date FROM jurnal_harian WHERE siswa_id = ?";
$stmt_harian = $koneksi->prepare($query_harian);
if ($stmt_harian) {
    $stmt_harian->bind_param("i", $siswa_id);
    $stmt_harian->execute();
    $result_harian = $stmt_harian->get_result();
    $data_harian = $result_harian->fetch_assoc();
    $total_laporan_harian = $data_harian['total'] ?? 0;
    if ($data_harian['last_date']) {
        $last_report_date = date('d F Y', strtotime($data_harian['last_date']));
    }
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
    $total_tugas_proyek = $data_proyek['total'] ?? 0;
    $stmt_proyek->close();
} else {
    error_log("Error preparing proyek query: " . $koneksi->error);
}

$start_pkl_date_example = '2025-01-01';
$today = new DateTime();
$start_date_obj = new DateTime($start_pkl_date_example);
if ($start_date_obj <= $today) {
    $interval = $today->diff($start_date_obj);
    $total_minggu_pkl = floor($interval->days / 7);
}

$quotes = [
    ["Setiap tugas kecil adalah langkah besar. Jangan takut bertanya, dan teruslah belajar dari setiap pengalaman!", "text-success"],
    ["Keberhasilan adalah hasil dari serangkaian kegagalan kecil yang tidak membuatmu berhenti.", "text-primary"],
    ["Jurnalmu adalah cerminan progres. Rajin mencatat, rajin pula progresmu terlihat!", "text-warning"],
    ["Inovasi dimulai dari rasa ingin tahu. Eksplorasi setiap tantangan baru yang kamu temui!", "text-info"],
    ["Disiplin adalah jembatan antara tujuan dan pencapaian. Tetap konsisten setiap hari.", "text-danger"],
    ["Waktu PKL adalah kesempatan emas. Manfaatkan setiap detiknya untuk mengembangkan dirimu!", "text-secondary"],
    ["Kegagalan hari ini adalah pelajaran untuk kesuksesan esok. Jangan menyerah!", "text-primary"],
    ["Catatan harianmu adalah bukti nyata usahamu. Jangan lupakan detail kecil sekalipun.", "text-info"],
    ["Belajar bukan hanya di kelas, tapi juga di dunia kerja. Serap ilmunya sebanyak mungkin!", "text-success"],
    ["Komunikasi adalah kunci. Jalin hubungan baik dengan instruktur dan rekan kerjamu.", "text-warning"],
    ["Setiap hari adalah babak baru dalam perjalanan belajarmu. Jadikan produktif!", "text-primary"],
    ["Fokus pada solusi, bukan pada masalah. Sikap positif membawa hasil positif.", "text-success"],
    ["Kemampuan terbaik lahir dari latihan. Terus asah skillmu setiap saat.", "text-danger"],
    ["Hargai prosesnya, nikmati perjalanannya. Setiap usaha akan terbayar.", "text-info"],
    ["Tanggung jawab adalah cerminan kedewasaan. Lakukan tugasmu dengan sepenuh hati.", "text-warning"],
    ["Jangan takut salah, takutlah jika tidak mencoba. Berani berinovasi!", "text-primary"],
    ["Manfaatkan umpan balik. Itu adalah hadiah untuk pertumbuhanmu.", "text-success"],
    ["Networking dimulai dari sekarang. Bangun jembatan profesionalmu.", "text-info"],
    ["Ketekunan mengalahkan segalanya. Teruslah bergerak maju selangkah demi selangkah.", "text-danger"],
    ["Proyekmu adalah karyamu. Buatlah dengan bangga dan penuh dedikasi.", "text-primary"]
];

$random_quote = $quotes[array_rand($quotes)];
$quote_text = $random_quote[0];
$quote_color_class = $random_quote[1];


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
                            <div class="col-12">
                                <div class="card shadow-lg border-0 text-white"
                                    style="border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                                    <div class="card-body p-4 p-md-5 position-relative">
                                        <div
                                            class="d-flex flex-column flex-md-row align-items-center mb-3 text-center text-md-start">
                                            <div class="me-md-3 mb-3 mb-md-0 animate__animated animate__fadeInLeft">
                                                <i class="bx bx-user-circle" style="font-size: 3.5rem;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h4
                                                    class="card-title text-white mb-2 animate__animated animate__fadeInRight">
                                                    Selamat Datang, <?= htmlspecialchars($siswa_nama) ?>!
                                                </h4>
                                                <p class="card-text text-white-75 animate__animated animate__fadeInUp"
                                                    style="font-size: 0.95rem;">
                                                    Semangat menjalankan Praktik Kerja Lapanganmu. Catat setiap
                                                    progresmu di sini!
                                                </p>
                                            </div>
                                        </div>
                                        <div class="position-absolute bottom-0 end-0 p-2 p-md-3" style="opacity: 0.1;">
                                            <i class="bx bx-check-circle" style="font-size: 6rem; color: white;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-4 mb-4">
                            <div class="col-lg-4 col-md-6 col-12">
                                <div
                                    class="card h-100 shadow-sm border-0 animate__animated animate__fadeInUp animate__delay-0-5s">
                                    <div class="card-body d-flex flex-column align-items-start p-4">
                                        <div class="avatar flex-shrink-0 mb-3 rounded-circle d-flex justify-content-center align-items-center bg-label-primary"
                                            style="width: 50px; height: 50px; font-size: 1.8rem;">
                                            <i class="bx bx-receipt bx-lg"></i>
                                        </div>
                                        <span class="text-muted fw-semibold d-block mb-1 fs-6">Laporan Harian</span>
                                        <h3 class="fw-bold mb-0 display-5 text-dark"><?= $total_laporan_harian ?></h3>
                                        <small class="text-muted d-block mt-1" style="font-size: 0.85rem;">Total
                                            laporanmu</small>
                                        <a href="master_kegiatan_harian.php"
                                            class="btn btn-sm btn-outline-primary mt-3">Lihat Detail <i
                                                class="bx bx-chevron-right"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-12">
                                <div
                                    class="card h-100 shadow-sm border-0 animate__animated animate__fadeInUp animate__delay-0-7s">
                                    <div class="card-body d-flex flex-column align-items-start p-4">
                                        <div class="avatar flex-shrink-0 mb-3 rounded-circle d-flex justify-content-center align-items-center bg-label-success"
                                            style="width: 50px; height: 50px; font-size: 1.8rem;">
                                            <i class="bx bx-task bx-lg"></i>
                                        </div>
                                        <span class="text-muted fw-semibold d-block mb-1 fs-6">Tugas Proyek</span>
                                        <h3 class="fw-bold mb-0 display-5 text-dark"><?= $total_tugas_proyek ?></h3>
                                        <small class="text-muted d-block mt-1" style="font-size: 0.85rem;">Total
                                            proyekmu</small>
                                        <a href="master_tugas_project.php" class="btn btn-sm btn-outline-success">Lihat
                                            Detail <i class="bx bx-chevron-right"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-12">
                                <div
                                    class="card h-100 shadow-sm border-0 animate__animated animate__fadeInUp animate__delay-0-9s">
                                    <div class="card-body d-flex flex-column align-items-start p-4">
                                        <div class="avatar flex-shrink-0 mb-3 rounded-circle d-flex justify-content-center align-items-center bg-label-info"
                                            style="width: 50px; height: 50px; font-size: 1.8rem;">
                                            <i class="bx bx-calendar-week bx-lg"></i>
                                        </div>
                                        <span class="text-muted fw-semibold d-block mb-1 fs-6">Minggu PKL
                                            Berjalan</span>
                                        <h3 class="card-title fw-bold mb-0 display-5 text-dark"><?= $total_minggu_pkl ?>
                                        </h3>
                                        <small class="text-muted d-block mt-1" style="font-size: 0.85rem;">Minggu ke-mu
                                            di tempat PKL</small>
                                        <a href="#" class="btn btn-sm btn-outline-info mt-3">Lihat Progres <i
                                                class="bx bx-chevron-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-lg-6 col-md-12">
                                <div
                                    class="card bg-label-info shadow-sm border-0 animate__animated animate__fadeInUp animate__delay-1-1s">
                                    <div
                                        class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap">
                                        <div class="me-3">
                                            <h5 class="card-title text-info mb-2"><i
                                                    class="bx bx-star me-2"></i>Motivasi Hari Ini!</h5>
                                            <p class="card-text text-muted mb-0" style="font-size: 0.9rem;">
                                                "<?= htmlspecialchars($quote_text) ?>"
                                            </p>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-12">
                                <div
                                    class="card bg-label-success shadow-sm border-0 animate__animated animate__fadeInUp animate__delay-1-3s">
                                    <div
                                        class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap">
                                        <div class="me-3">
                                            <h5 class="card-title text-success mb-2"><i
                                                    class="bx bx-check-double me-2"></i>Progres Laporanmu</h5>
                                            <p class="card-text text-muted mb-0" style="font-size: 0.9rem;">
                                                <?php if ($total_laporan_harian > 0): ?>
                                                    Terakhir melaporkan kegiatan pada tanggal
                                                    <strong><?= $last_report_date ?></strong>.
                                                <?php else: ?>
                                                    Belum ada laporan kegiatan yang tercatat. Ayo buat laporan pertamamu
                                                    hari ini!
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0 mt-3 mt-sm-0">
                                            <i class="bx bx-calendar-check bx-lg text-success"
                                                style="font-size: 3rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-12 mb-4">
                                <h5 class="mb-3 animate__animated animate__fadeInLeft animate__delay-1-5s">Mulai Catat
                                    Kegiatanmu!</h5>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-start flex-wrap">
                                    <a href="master_kegiatan_harian_add.php"
                                        class="btn btn-info btn-lg flex-fill animate__animated animate__zoomIn animate__delay-1-7s">
                                        <i class="bx bx-plus-circle me-2"></i> Tambah Laporan Harian
                                    </a>
                                    <a href="master_tugas_project_add.php"
                                        class="btn btn-warning btn-lg flex-fill animate__animated animate__zoomIn animate__delay-1-8s">
                                        <i class="bx bx-edit-alt me-2"></i> Tambah Tugas Proyek
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div
                                class="col-12 text-center text-muted small animate__animated animate__fadeInUp animate__delay-1-9s">
                                <p>&copy; 2025 E-Jurnal PKL. Semua Hak Dilindungi.</p>
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