<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

// Logika Keamanan Halaman
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
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

$siswa_id = $_SESSION['id_siswa'] ?? null;
$siswa_nama = $_SESSION['siswa_nama'] ?? "Pengguna";

if (empty($siswa_id)) {
    session_destroy();
    header('Location: ../login.php');
    exit();
}

include 'partials/db.php';

// === AMBIL STATUS & TANGGAL SELESAI DARI TABEL SISWA ===
$status_siswa = 'Aktif';
$tanggal_selesai_pkl = null;

$query_siswa = "SELECT status, tanggal_selesai_pkl FROM siswa WHERE id_siswa = ?";
$stmt_siswa = $koneksi->prepare($query_siswa);
if ($stmt_siswa) {
    $stmt_siswa->bind_param("i", $siswa_id);
    $stmt_siswa->execute();
    $result_siswa = $stmt_siswa->get_result();
    if ($row_siswa = $result_siswa->fetch_assoc()) {
        $status_siswa = $row_siswa['status'] ?? 'Aktif';
        $tanggal_selesai_pkl = $row_siswa['tanggal_selesai_pkl']; // bisa null
    }
    $stmt_siswa->close();
}

// === LOGIKA ABSENSI HARI INI (HANYA JIKA BELUM SELESAI) ===
$sudah_absen_hari_ini = false;
$status_absen_hari_ini = '';
$keterangan_siswa = null;
$jam_datang_siswa = null;
$jam_pulang_siswa = null;
$keterangan_absen_lengkap = true;

if ($status_siswa !== 'Selesai') {
    $current_date = date('Y-m-d');
    $current_time_full = date('H:i:s');
    $possible_checkin_dates_for_display = [$current_date];

    if (strtotime($current_time_full) < strtotime('06:00:00')) {
        $yesterday_date_for_display = date('Y-m-d', strtotime('-1 day'));
        array_unshift($possible_checkin_dates_for_display, $yesterday_date_for_display);
    }

    $placeholders_for_display = implode(',', array_fill(0, count($possible_checkin_dates_for_display), '?'));
    $query_check_absen = "SELECT status_absen, keterangan, bukti_foto, jam_datang, jam_pulang 
                          FROM absensi_siswa 
                          WHERE siswa_id = ? AND tanggal_absen IN ($placeholders_for_display) 
                          ORDER BY tanggal_absen DESC LIMIT 1";

    $stmt_check_absen = $koneksi->prepare($query_check_absen);
    if ($stmt_check_absen) {
        $types_for_display = 'i' . str_repeat('s', count($possible_checkin_dates_for_display));
        $bind_params_for_display = array_merge([$siswa_id], $possible_checkin_dates_for_display);
        $stmt_check_absen->bind_param($types_for_display, ...$bind_params_for_display);
        $stmt_check_absen->execute();
        $result_check_absen = $stmt_check_absen->get_result();
        if ($result_check_absen->num_rows > 0) {
            $data_absen = $result_check_absen->fetch_assoc();
            $sudah_absen_hari_ini = true;
            $status_absen_hari_ini = $data_absen['status_absen'];
            $jam_datang_siswa = $data_absen['jam_datang'];
            $jam_pulang_siswa = $data_absen['jam_pulang'];
            $keterangan_siswa = $data_absen['keterangan'];

            if (($status_absen_hari_ini == 'Sakit' || $status_absen_hari_ini == 'Izin')
                && (empty($keterangan_siswa) || empty($data_absen['bukti_foto']))
            ) {
                $keterangan_absen_lengkap = false;
            }
        }
        $stmt_check_absen->close();
    }
}

// === DATA JURNAL (TETAP DIAMBIL MESKI SUDAH SELESAI) ===
$total_laporan_harian = 0;
$total_tugas_proyek = 0;
$last_report_date = 'Belum ada laporan';

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
}

$query_proyek = "SELECT COUNT(*) AS total FROM jurnal_kegiatan WHERE siswa_id = ?";
$stmt_proyek = $koneksi->prepare($query_proyek);
if ($stmt_proyek) {
    $stmt_proyek->bind_param("i", $siswa_id);
    $stmt_proyek->execute();
    $result_proyek = $stmt_proyek->get_result();
    $data_proyek = $result_proyek->fetch_assoc();
    $total_tugas_proyek = $data_proyek['total'] ?? 0;
    $stmt_proyek->close();
}

// === MOTIVASI ===
$quotes = [
    ["Setiap tugas kecil adalah langkah besar. Jangan takut bertanya, dan teruslah belajar dari setiap pengalaman!", "text-success"],
    ["Keberhasilan adalah hasil dari serangkaian kegagalan kecil yang tidak membuatmu berhenti.", "text-primary"],
];
$random_quote = $quotes[array_rand($quotes)];
$quote_text = $random_quote[0];
$quote_color_class = $random_quote[1];

$koneksi->close();
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/"
    data-template="vertical-menu-template-free">

<head>
    <?php include 'partials/head.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include './partials/sidebar.php'; ?>
            <div class="layout-page">
                <?php include './partials/navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <?php if (isset($_SESSION['alert_message'])): ?>
                            <script>
                                Swal.fire({
                                    icon: '<?= $_SESSION['alert_type'] ?>',
                                    title: '<?= $_SESSION['alert_title'] ?>',
                                    text: '<?= $_SESSION['alert_message'] ?>',
                                    confirmButtonColor: '#696cff'
                                });
                            </script>
                            <?php unset($_SESSION['alert_message'], $_SESSION['alert_type'], $_SESSION['alert_title']); ?>
                        <?php endif; ?>

                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card shadow-lg border-0 text-white"
                                    style="border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                                    <div class="card-body p-3 p-md-5 position-relative">
                                        <div
                                            class="d-flex flex-column flex-md-row align-items-center mb-3 text-center text-md-start">
                                            <div class="me-md-3 mb-3 mb-md-0 animate__animated animate__fadeInLeft">
                                                <i class="bx bx-user-circle" style="font-size: 3.5rem;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h4
                                                    class="card-title text-white mb-2 animate__animated animate__fadeInRight text-break">
                                                    Selamat Datang, <?= htmlspecialchars($siswa_nama) ?>!
                                                </h4>
                                                <?php if ($status_siswa === 'Selesai' && !empty($tanggal_selesai_pkl)): ?>
                                                    <p class="card-text text-white-75 animate__animated animate__fadeInUp"
                                                        style="font-size: 0.95rem;">
                                                        <i class="bx bx-check-circle me-1"></i>
                                                        <strong>PKL Anda telah selesai</strong> pada tanggal
                                                        <strong><?= date('d F Y', strtotime($tanggal_selesai_pkl)) ?></strong>.
                                                    </p>
                                                <?php else: ?>
                                                    <p class="card-text text-white-75 animate__animated animate__fadeInUp"
                                                        style="font-size: 0.95rem;">
                                                        Semangat menjalankan Praktik Kerja Lapanganmu. Catat setiap
                                                        progresmu di sini!
                                                    </p>
                                                <?php endif; ?>

                                                <div class="d-grid gap-2 d-md-flex justify-content-md-start mt-4">
                                                    <?php if ($status_siswa === 'Selesai'): ?>
                                                        <button type="button" class="btn btn-light" disabled>
                                                            <i class="bx bx-flag me-2"></i> PKL Telah Selesai
                                                        </button>
                                                    <?php else: ?>
                                                        <?php if ($sudah_absen_hari_ini): ?>
                                                            <button type="button" class="btn btn-light" disabled>
                                                                <i class="bx bx-check-double me-2"></i> Status:
                                                                <?= htmlspecialchars($status_absen_hari_ini) ?>
                                                            </button>
                                                            <?php if ($status_absen_hari_ini == 'Hadir' && empty($jam_pulang_siswa)): ?>
                                                                <button type="button" id="absenPulangBtn" class="btn btn-warning">
                                                                    <i class="bx bx-log-out me-2"></i> Absen Pulang
                                                                </button>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                                                data-bs-target="#absenModal">
                                                                <i class="bx bx-check-square me-2"></i> Absen Hari Ini!
                                                            </button>
                                                        <?php endif; ?>
                                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                                            data-bs-target="#selesaiPklModal">
                                                            <i class="bx bx-flag me-2"></i> Akhiri PKL
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-12 col-md-6">
                                <div class="card h-100 shadow-sm border-0 animate__animated animate__fadeInUp">
                                    <div class="card-body p-3 p-md-4">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="me-3">
                                                <div class="avatar flex-shrink-0 mb-3"><span
                                                        class="avatar-initial rounded bg-label-primary"><i
                                                            class="bx bx-receipt bx-sm"></i></span></div>
                                                <span class="text-muted fw-semibold d-block">Jurnal Harian</span>
                                                <h3 class="fw-bold mb-0 text-dark text-break h1">
                                                    <?= $total_laporan_harian ?></h3>
                                            </div>
                                            <a href="master_kegiatan_harian.php"
                                                class="btn btn-icon btn-sm btn-outline-primary rounded-pill flex-shrink-0">
                                                <span class="tf-icons bx bx-chevron-right"></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="card h-100 shadow-sm border-0 animate__animated animate__fadeInUp">
                                    <div class="card-body p-3 p-md-4">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="me-3">
                                                <div class="avatar flex-shrink-0 mb-3"><span
                                                        class="avatar-initial rounded bg-label-success"><i
                                                            class="bx bx-task bx-sm"></i></span></div>
                                                <span class="text-muted fw-semibold d-block">Jurnal Kegiatan</span>
                                                <h3 class="fw-bold mb-0 text-dark text-break h1">
                                                    <?= $total_tugas_proyek ?></h3>
                                            </div>
                                            <a href="master_tugas_project.php"
                                                class="btn btn-icon btn-sm btn-outline-success rounded-pill flex-shrink-0">
                                                <span class="tf-icons bx bx-chevron-right"></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-12 col-lg-6">
                                <div class="card bg-label-info shadow-sm border-0 animate__animated animate__fadeInUp">
                                    <div class="card-body p-4">
                                        <h5 class="card-title text-info mb-2"><i class="bx bx-star me-2"></i>Motivasi
                                            Hari Ini!</h5>
                                        <p class="card-text text-muted mb-0">"<?= htmlspecialchars($quote_text) ?>"</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <div
                                    class="card bg-label-success shadow-sm border-0 animate__animated animate__fadeInUp">
                                    <div class="card-body p-4">
                                        <h5 class="card-title text-success mb-2"><i
                                                class="bx bx-check-double me-2"></i>Progres Laporanmu</h5>
                                        <p class="card-text text-muted mb-0">
                                            <?php if ($total_laporan_harian > 0): ?>
                                                Terakhir lapor pada: <strong><?= $last_report_date ?></strong>.
                                            <?php else: ?>
                                                Belum ada laporan kegiatan yang tercatat.
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($status_siswa !== 'Selesai'): ?>
                            <div class="row">
                                <div class="col-12 mb-4">
                                    <h5 class="mb-3 animate__animated animate__fadeInLeft">Mulai Catat Kegiatanmu!</h5>
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                                        <a href="master_kegiatan_harian_add.php"
                                            class="btn btn-info btn-lg flex-fill animate__animated animate__zoomIn">
                                            <i class="bx bx-plus-circle me-2"></i> Tambah Jurnal Harian
                                        </a>
                                        <a href="master_tugas_project.php"
                                            class="btn btn-warning btn-lg flex-fill animate__animated animate__zoomIn">
                                            <i class="bx bx-edit-alt me-2"></i> Tambah Jurnal Kegiatan
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php include './partials/footer.php'; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="selesaiPklModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bx bx-flag me-2 text-danger"></i>Akhiri Kegiatan PKL</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formSelesaiPkl" action="process_selesai_pkl.php" method="POST">
                    <div class="modal-body">
                        <div class="alert alert-warning d-flex align-items-center">
                            <i class="bx bx-info-circle me-2"></i>
                            <div><strong>Perhatian!</strong> Aksi ini tidak dapat dibatalkan.</div>
                        </div>
                        <p class="mb-3">Silakan pilih tanggal resmi akhir kegiatan PKL Anda:</p>
                        <div class="mb-3">
                            <label for="tanggalSelesai" class="form-label fw-bold">Tanggal Selesai PKL <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="tanggalSelesai" name="tanggal_selesai"
                                placeholder="Pilih tanggal" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Konfirmasi Akhiri PKL</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="absenModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bx bx-calendar-check me-2 text-success"></i>Formulir Absensi PKL
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formAbsen" action="process_absen.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Status Absensi Anda Hari Ini:</label>
                            <div class="form-check mt-2"><input class="form-check-input" type="radio" name="statusAbsen"
                                    id="radioHadir" value="Hadir" checked><label class="form-check-label"
                                    for="radioHadir"><span class="badge bg-success"><i
                                            class="bx bx-check-circle me-1"></i> Hadir</span> - Anda masuk
                                    kerja/praktik.</label></div>
                            <div class="form-check mt-2"><input class="form-check-input" type="radio" name="statusAbsen"
                                    id="radioSakit" value="Sakit"><label class="form-check-label" for="radioSakit"><span
                                        class="badge bg-warning"><i class="bx bx-plus-medical me-1"></i> Sakit</span> -
                                    Tidak masuk karena sakit.</label></div>
                            <div class="form-check mt-2"><input class="form-check-input" type="radio" name="statusAbsen"
                                    id="radioIzin" value="Izin"><label class="form-check-label" for="radioIzin"><span
                                        class="badge bg-info"><i class="bx bx-receipt me-1"></i> Izin</span> - Tidak
                                    masuk karena keperluan.</label></div>
                            <div class="form-check mt-2"><input class="form-check-input" type="radio" name="statusAbsen"
                                    id="radioLibur" value="Libur"><label class="form-check-label" for="radioLibur"><span
                                        class="badge bg-secondary"><i class="bx bx-calendar-alt me-1"></i> Libur</span>
                                    - Hari libur/akhir pekan.</label></div>
                        </div>
                        <div class="mb-3" id="jamDatangField"><label for="jamDatang" class="form-label">Jam
                                Datang:</label><input type="text" class="form-control" id="jamDatang" name="jamDatang"
                                readonly><small class="form-text text-muted">Otomatis terisi saat Anda absen.</small>
                        </div>
                        <div id="additionalFields" style="display: none;" class="mt-4 p-3 border rounded-3 bg-light">
                            <p class="text-danger fw-bold"><i class="bx bx-info-circle me-1"></i> Mohon lengkapi info
                                berikut:</p>
                            <div class="mb-3"><label for="keterangan" class="form-label">Keterangan Tambahan <span
                                        class="text-danger">*</span></label><textarea class="form-control"
                                    id="keterangan" name="keterangan" rows="2"
                                    placeholder="Contoh: Sakit demam..."></textarea></div>
                            <div class="mb-3"><label for="buktiFoto" class="form-label">Unggah Bukti Foto <span
                                        class="text-danger">*</span></label><input class="form-control" type="file"
                                    id="buktiFoto" name="buktiFoto" accept="image/jpeg,image/png">
                                <div class="form-text">Unggah foto bukti (Maks. 2MB).</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Konfirmasi Absen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include './partials/script.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- FUNGSI #1: UNTUK MODAL SELESAI PKL ---
            function setupSelesaiPkl() {
                const modalEl = document.getElementById('selesaiPklModal');
                if (modalEl) {
                    flatpickr("#tanggalSelesai", {
                        locale: "id",
                        dateFormat: "Y-m-d",
                        maxDate: "today",
                        defaultDate: "today",
                        appendTo: modalEl // Menempatkan kalender di dalam modal
                    });
                }
            }

            // --- FUNGSI #2: UNTUK TOMBOL ABSEN PULANG ---
            function setupAbsenPulang() {
                const btn = document.getElementById('absenPulangBtn');
                if (btn) {
                    btn.addEventListener('click', () => {
                        Swal.fire({
                            title: 'Konfirmasi Absen Pulang',
                            html: 'Apakah Anda yakin ingin absen pulang sekarang?<br>Waktu pulang akan dicatat secara otomatis.',
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
                    });
                }
            }

            // --- FUNGSI #3: UNTUK MODAL ABSEN HARIAN ---
            function setupAbsenHarian() {
                const form = document.getElementById('formAbsen');
                if (form) {
                    const additionalFields = form.querySelector('#additionalFields');
                    const jamDatangField = form.querySelector('#jamDatangField');

                    const toggleFields = () => {
                        const status = form.querySelector('input[name="statusAbsen"]:checked').value;
                        if (status === 'Sakit' || status === 'Izin') {
                            additionalFields.style.display = 'block';
                            jamDatangField.style.display = 'none';
                            additionalFields.querySelector('#keterangan').required = true;
                            additionalFields.querySelector('#buktiFoto').required = true;
                        } else {
                            additionalFields.style.display = 'none';
                            additionalFields.querySelector('#keterangan').required = false;
                            additionalFields.querySelector('#buktiFoto').required = false;
                            if (status === 'Hadir') {
                                jamDatangField.style.display = 'block';
                                const now = new Date();
                                const hours = String(now.getHours()).padStart(2, '0');
                                const minutes = String(now.getMinutes()).padStart(2, '0');
                                form.querySelector('#jamDatang').value = `${hours}:${minutes}`;
                            } else {
                                jamDatangField.style.display = 'none';
                            }
                        }
                    };
                    form.querySelectorAll('input[name="statusAbsen"]').forEach(radio => radio.addEventListener(
                        'change', toggleFields));
                    document.getElementById('absenModal').addEventListener('show.bs.modal', toggleFields);
                }
            }

            // --- Panggil semua fungsi agar berjalan ---
            setupSelesaiPkl();
            setupAbsenPulang();
            setupAbsenHarian();
        });
    </script>
</body>

</html>