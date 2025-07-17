<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

// Logika Keamanan Halaman
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

// Hanya siswa yang boleh mengakses dashboard ini
if (!$is_siswa) {
    if ($is_admin) {
        header('Location: index.php');
        exit();
    } elseif ($is_guru) {
        header('Location: dashboard_guru.php');
        exit();
    } else {
        header('Location: ../login.php');
        exit();
    }
}

// Pastikan id_siswa dan nama siswa tersedia dari sesi
$siswa_id = $_SESSION['id_siswa'] ?? null;
$siswa_nama = $_SESSION['siswa_nama'] ?? "Pengguna";

// Jika siswa_id tidak ada, meskipun status login logged_in (kasus jarang, tapi untuk keamanan)
if (empty($siswa_id)) {
    session_destroy();
    header('Location: ../login.php');
    exit();
}

// ========================================================
// Sertakan koneksi database
// Asumsi: partials/db.php ada di dalam folder yang sama dengan dashboard_siswa.php
// ========================================================
include 'partials/db.php';

// --- Cek Absensi Harian dari Database (REAL) ---
$sudah_absen_hari_ini = false;
$status_absen_hari_ini = '';
$keterangan_absen_lengkap = true;
$jam_datang_siswa = null;
$jam_pulang_siswa = null;

$current_date = date('Y-m-d');
$current_hour_minute = date('H:i'); // Waktu saat ini untuk perbandingan

// Query menggunakan nama tabel dan kolom yang benar: absensi_siswa, status_absen, tanggal_absen, siswa_id
$query_check_absen = "SELECT status_absen, keterangan, bukti_foto, jam_datang, jam_pulang FROM absensi_siswa WHERE siswa_id = ? AND tanggal_absen = ?";
$stmt_check_absen = $koneksi->prepare($query_check_absen);

if ($stmt_check_absen) {
    $stmt_check_absen->bind_param("is", $siswa_id, $current_date);
    $stmt_check_absen->execute();
    $result_check_absen = $stmt_check_absen->get_result();

    if ($result_check_absen->num_rows > 0) {
        $data_absen = $result_check_absen->fetch_assoc();
        $sudah_absen_hari_ini = true;
        $status_absen_hari_ini = $data_absen['status_absen'];
        $jam_datang_siswa = $data_absen['jam_datang'];
        $jam_pulang_siswa = $data_absen['jam_pulang'];

        if (($status_absen_hari_ini == 'Sakit' || $status_absen_hari_ini == 'Izin') && (empty($data_absen['keterangan']) || empty($data_absen['bukti_foto']))) {
            $keterangan_absen_lengkap = false;
        }
    }
    $stmt_check_absen->close();
} else {
    error_log("Error preparing check absen query: " . $koneksi->error);
}

// --- Data Jurnal dari Database (REAL) ---
$total_laporan_harian = 0;
$total_tugas_proyek = 0;
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

// Logika untuk menghitung minggu PKL
$start_pkl_date_example = '2024-01-01'; // Menggunakan tanggal hardcoded
$today = new DateTime();
$total_minggu_pkl = 0;
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
    ["Proyekmu adalah karyamu. Buatlah dengan banggang dan penuh dedikasi.", "text-primary"]
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

                        <?php
                        if (isset($_SESSION['alert_message'])) {
                            $alert_icon = $_SESSION['alert_type'];
                            $alert_title = $_SESSION['alert_title'];
                            $alert_text = $_SESSION['alert_message'];
                            echo "
                                <script>
                                    Swal.fire({
                                        icon: '{$alert_icon}',
                                        title: '{$alert_title}',
                                        text: '{$alert_text}',
                                        confirmButtonColor: '#696cff',
                                        showClass: {
                                            popup: 'animate__animated animate__fadeInDown animate__faster'
                                        },
                                        hideClass: {
                                            popup: 'animate__animated animate__fadeOutUp animate__faster'
                                        }
                                    });
                                </script>
                                ";
                            unset($_SESSION['alert_message']);
                            unset($_SESSION['alert_type']);
                            unset($_SESSION['alert_title']);
                        }
                        ?>

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
                                                <div class="mt-4 animate__animated animate__fadeInUp animate__delay-2s">
                                                    <?php if ($sudah_absen_hari_ini): ?>
                                                    <button type="button" class="btn btn-light" disabled>
                                                        <i class="bx bx-check-double me-2"></i> Anda sudah Absen Hari
                                                        Ini (Status:
                                                        <?= htmlspecialchars($status_absen_hari_ini ?? '') ?>)
                                                    </button>
                                                    <?php
                                                        $jam_datang_display = !empty($jam_datang_siswa) ? date('H:i', strtotime($jam_datang_siswa)) : '-';
                                                        $jam_pulang_display = !empty($jam_pulang_siswa) ? date('H:i', strtotime($jam_pulang_siswa)) : 'Belum Pulang';
                                                        ?>
                                                    <p class="text-white-75 mt-2 mb-0" style="font-size: 0.9rem;">
                                                        Jam Datang: <?= $jam_datang_display ?> WIB | Jam Pulang:
                                                        <?= $jam_pulang_display ?> WIB
                                                    </p>

                                                    <?php if ($status_absen_hari_ini == 'Hadir' && empty($jam_pulang_siswa)): ?>
                                                    <button type="button" id="absenPulangBtn"
                                                        class="btn btn-warning mt-3">
                                                        <i class="bx bx-log-out me-2"></i> Absen Pulang Sekarang!
                                                    </button>
                                                    <?php elseif ($status_absen_hari_ini == 'Hadir' && !empty($jam_pulang_siswa)): ?>
                                                    <button type="button" class="btn btn-info mt-3" disabled>
                                                        <i class="bx bx-check-double me-2"></i> Anda Sudah Absen Pulang
                                                    </button>
                                                    <?php endif; ?>

                                                    <?php if (($status_absen_hari_ini == 'Sakit' || $status_absen_hari_ini == 'Izin') && !$keterangan_absen_lengkap): ?>
                                                    <p class="text-warning mt-2 mb-0 fw-bold">
                                                        <i class="bx bx-error-circle me-1"></i> Absensi Sakit/Izin Anda
                                                        belum lengkap. Mohon lengkapi!
                                                    </p>
                                                    <?php endif; ?>
                                                    <?php else: ?>
                                                    <button type="button" class="btn btn-success btn-lg"
                                                        data-bs-toggle="modal" data-bs-target="#absenModal">
                                                        <i class="bx bx-check-square me-2"></i> Absen Hari Ini!
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
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
                            <div class="col-lg-6 col-md-6 col-12">
                                <div
                                    class="card h-100 shadow-sm border-0 animate__animated animate__fadeInUp animate__delay-0-5s">
                                    <div class="card-body d-flex flex-column align-items-start p-4">
                                        <div class="avatar flex-shrink-0 mb-3 rounded-circle d-flex justify-content-center align-items-center bg-label-primary"
                                            style="width: 50px; height: 50px; font-size: 1.8rem;">
                                            <i class="bx bx-receipt bx-lg"></i>
                                        </div>
                                        <span class="text-muted fw-semibold d-block mb-1 fs-6">Jurnal PKL Harian</span>
                                        <h3 class="fw-bold mb-0 display-5 text-dark"><?= $total_laporan_harian ?></h3>
                                        <small class="text-muted d-block mt-1" style="font-size: 0.85rem;">Total
                                            Jurnal PKL Harian </small>
                                        <a href="master_kegiatan_harian.php"
                                            class="btn btn-sm btn-outline-primary mt-3">Lihat Detail <i
                                                class="bx bx-chevron-right"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-12">
                                <div
                                    class="card h-100 shadow-sm border-0 animate__animated animate__fadeInUp animate__delay-0-7s">
                                    <div class="card-body d-flex flex-column align-items-start p-4">
                                        <div class="avatar flex-shrink-0 mb-3 rounded-circle d-flex justify-content-center align-items-center bg-label-success"
                                            style="width: 50px; height: 50px; font-size: 1.8rem;">
                                            <i class="bx bx-task bx-lg"></i>
                                        </div>
                                        <span class="text-muted fw-semibold d-block mb-1 fs-6">Jurnal PKL Per
                                            Kegiatan</span>
                                        <h3 class="fw-bold mb-0 display-5 text-dark"><?= $total_tugas_proyek ?></h3>
                                        <small class="text-muted d-block mt-1" style="font-size: 0.85rem;">Total
                                            Jurnal PKL Per Kegiatanmu</small>
                                        <a href="master_tugas_project.php"
                                            class="btn btn-sm btn-outline-success mt-auto">Lihat
                                            Detail <i class="bx bx-chevron-right"></i></a>
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
                                        <i class="bx bx-plus-circle me-2"></i> Tambah Jurnal PKL Harian
                                    </a>
                                    <a href="master_tugas_project.php"
                                        class="btn btn-warning btn-lg flex-fill animate__animated animate__zoomIn animate__delay-1-8s">
                                        <i class="bx bx-edit-alt me-2"></i> Tambah Jurnal PKL Per Kegiatan
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
                    <?php include './partials/footer.php'; ?>
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include './partials/script.php'; ?>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">


    <div class="modal fade" id="absenModal" tabindex="-1" aria-labelledby="absenModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="absenModalLabel"><i
                            class="bx bx-calendar-check me-2 text-success"></i>Formulir Absensi PKL</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formAbsen" action="process_absen.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Status Absensi Anda Hari Ini:</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="radio" name="statusAbsen" id="radioHadir"
                                    value="Hadir" checked>
                                <label class="form-check-label" for="radioHadir">
                                    <span class="badge bg-success"><i class="bx bx-check-circle me-1"></i> Hadir</span>
                                    - Anda masuk kerja/praktik hari ini.
                                </label>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="radio" name="statusAbsen" id="radioSakit"
                                    value="Sakit">
                                <label class="form-check-label" for="radioSakit">
                                    <span class="badge bg-warning"><i class="bx bx-plus-medical me-1"></i> Sakit</span>
                                    - Anda tidak dapat masuk karena sakit.
                                </label>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="radio" name="statusAbsen" id="radioIzin"
                                    value="Izin">
                                <label class="form-check-label" for="radioIzin">
                                    <span class="badge bg-info"><i class="bx bx-receipt me-1"></i> Izin</span> - Anda
                                    tidak dapat masuk karena ada keperluan.
                                </label>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="radio" name="statusAbsen" id="radioLibur"
                                    value="Libur">
                                <label class="form-check-label" for="radioLibur">
                                    <span class="badge bg-secondary"><i class="bx bx-calendar-alt me-1"></i>
                                        Libur</span>
                                    - Anda tidak ada jadwal masuk hari ini (misal: akhir pekan, libur nasional).
                                </label>
                            </div>
                        </div>

                        <div class="mb-3" id="jamDatangField">
                            <label for="jamDatang" class="form-label">Jam Datang:</label>
                            <input type="text" class="form-control" id="jamDatang" name="jamDatang" readonly>
                            <small class="form-text text-muted">Waktu Anda datang hari ini (otomatis terisi).</small>
                        </div>

                        <div id="additionalFields" style="display: none;" class="mt-4 p-3 border rounded-3 bg-light">
                            <p class="text-danger fw-bold"><i class="bx bx-info-circle me-1"></i> Mohon lengkapi
                                informasi berikut untuk status Sakit / Izin:</p>
                            <div class="mb-3">
                                <label for="keterangan" class="form-label">Keterangan Tambahan <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="3"
                                    placeholder="Contoh: Sakit demam, Izin ada acara keluarga, dll."
                                    maxlength="255"></textarea>
                                <div class="form-text">Jelaskan alasan Anda tidak dapat hadir.</div>
                            </div>
                            <div class="mb-3">
                                <label for="buktiFoto" class="form-label">Unggah Bukti Foto <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="file" id="buktiFoto" name="buktiFoto"
                                    accept="image/jpeg,image/png">
                                <div class="form-text">Unggah foto sebagai bukti (Contoh: Surat dokter, surat izin, dll.
                                    Maks. 2MB, format JPG/PNG).</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="submitAbsenBtn">Konfirmasi Absen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hapus Flatpickr untuk jamDatang karena kita akan mengisi manual dengan JS
        // const jamDatangPicker = flatpickr("#jamDatang", {
        //     enableTime: true,
        //     noCalendar: true,
        //     dateFormat: "H:i",
        //     time_24hr: true,
        //     defaultDate: "<?= date('H:i') ?>"
        // });

        const radioHadir = document.getElementById('radioHadir');
        const radioSakit = document.getElementById('radioSakit');
        const radioIzin = document.getElementById('radioIzin');
        const radioLibur = document.getElementById('radioLibur');
        const additionalFields = document.getElementById('additionalFields');
        const keteranganField = document.getElementById('keterangan');
        const buktiFotoField = document.getElementById('buktiFoto');
        const formAbsen = document.getElementById('formAbsen');
        const absenModalElement = document.getElementById('absenModal');
        const absenModal = new bootstrap.Modal(absenModalElement);
        const jamDatangField = document.getElementById('jamDatangField');
        const jamDatangInput = document.getElementById('jamDatang'); // Ambil elemen input jamDatang

        const absenPulangBtn = document.getElementById('absenPulangBtn');

        function toggleAdditionalFields() {
            if (radioSakit.checked || radioIzin.checked) {
                additionalFields.style.display = 'block';
                keteranganField.setAttribute('required', 'required');
                buktiFotoField.setAttribute('required', 'required');
                jamDatangField.style.display = 'none';
                jamDatangInput.removeAttribute('required'); // Hapus required jika disembunyikan
            } else if (radioHadir.checked) {
                additionalFields.style.display = 'none';
                keteranganField.removeAttribute('required');
                buktiFotoField.removeAttribute('required');
                keteranganField.value = '';
                buktiFotoField.value = '';
                jamDatangField.style.display = 'block';
                jamDatangInput.setAttribute('required', 'required'); // Tambahkan required jika terlihat
                // Isi jam datang secara otomatis saat Hadir dipilih
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                jamDatangInput.value = `${hours}:${minutes}`;
            } else if (radioLibur.checked) {
                additionalFields.style.display = 'none';
                keteranganField.removeAttribute('required');
                buktiFotoField.removeAttribute('required');
                keteranganField.value = '';
                buktiFotoField.value = '';
                jamDatangField.style.display = 'none';
                jamDatangInput.removeAttribute('required'); // Hapus required jika disembunyikan
            }
        }

        // Panggil saat DOMContentLoaded dan saat modal dibuka
        toggleAdditionalFields();

        radioHadir.addEventListener('change', toggleAdditionalFields);
        radioSakit.addEventListener('change', toggleAdditionalFields);
        radioIzin.addEventListener('change', toggleAdditionalFields);
        radioLibur.addEventListener('change', toggleAdditionalFields);

        // Saat modal dibuka, pastikan jamDatang terisi otomatis jika Hadir terpilih
        absenModalElement.addEventListener('show.bs.modal', function() {
            if (radioHadir.checked) {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                jamDatangInput.value = `${hours}:${minutes}`;
            }
        });


        formAbsen.addEventListener('submit', function(event) {
            if (radioSakit.checked || radioIzin.checked) {
                if (keteranganField.value.trim() === '' || buktiFotoField.files.length === 0) {
                    event.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Data Tidak Lengkap!',
                        text: 'Untuk status Sakit/Izin, keterangan dan bukti foto wajib diisi.',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }
            } else if (radioHadir.checked) {
                // Pastikan jamDatang terisi, meskipun diisi otomatis
                if (jamDatangInput.value.trim() === '') {
                    event.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Jam Datang Kosong!',
                        text: 'Terjadi kesalahan internal: Jam datang tidak terisi otomatis.',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }
                // TIDAK PERLU lagi ambil jam dari field, karena kita akan ambil langsung dari PHP (server-side)
                // supaya jam masuk sama dengan waktu input transaksi.
                // Namun, nilai di input tetap diperlukan untuk dikirim ke server.
            }

            // Sembunyikan modal setelah submit (jika validasi JS lolos)
            absenModal.hide();
        });

        absenModalElement.addEventListener('hidden.bs.modal', function() {
            formAbsen.reset();
            toggleAdditionalFields(); // Reset tampilan field tambahan
            // Tidak perlu reset Flatpickr karena sudah tidak pakai
        });

        // Logic for Absen Pulang button
        // Hapus cutoffTimePulang karena absen pulang bebas waktu
        // const cutoffTimePulang = "10:00"; 

        function updateAbsenPulangButtonState() {
            const isAbsenMasukHadir =
                <?= $sudah_absen_hari_ini && $status_absen_hari_ini == 'Hadir' ? 'true' : 'false' ?>;
            const isJamPulangEmpty = <?= empty($jam_pulang_siswa) ? 'true' : 'false' ?>;

            if (absenPulangBtn) { // Ensure button exists before manipulating
                if (isAbsenMasukHadir && isJamPulangEmpty) {
                    // Tidak ada lagi batasan waktu di sini
                    absenPulangBtn.disabled = false;
                    absenPulangBtn.onclick = confirmAbsenPulang;
                    absenPulangBtn.textContent = 'Absen Pulang Sekarang!';
                    absenPulangBtn.classList.remove('btn-secondary');
                    absenPulangBtn.classList.add('btn-warning');
                } else {
                    // Default state if not Hadir or already Pulang, button should be disabled from PHP initial state
                    // No change needed here, as PHP already sets disabled state for "Anda Sudah Absen Pulang"
                }
            }
        }

        updateAbsenPulangButtonState(); // Set state on page load

        function confirmAbsenPulang() {
            Swal.fire({
                title: 'Konfirmasi Absen Pulang',
                html: 'Apakah Anda yakin ingin melakukan absen pulang sekarang?<br>Waktu pulang akan dicatat secara otomatis.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Absen Pulang!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'process_absen_pulang.php';
                }
            });
        }
    });
    </script>
</body>

</html>