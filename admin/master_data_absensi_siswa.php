<?php
session_start();
date_default_timezone_set('Asia/Jakarta'); // Ensure timezone is set for date functions

$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';

if (!$is_admin && !$is_guru && !$is_siswa) {
    header('Location: ../login.php');
    exit();
}

include 'partials/db.php';

$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter untuk tabel tampilan (sekarang bisa rentang tanggal untuk semua role)
// Default filter_tanggal_mulai set to July 14, 2025
$filter_tanggal_mulai = $_GET['tanggal_mulai'] ?? '2025-07-14';
$filter_tanggal_akhir = $_GET['tanggal_akhir'] ?? date('Y-m-d');  // Filter universal untuk tabel, defaults to current date
$filter_status = $_GET['status'] ?? 'Semua';                    // Filter universal untuk tabel
$keyword = $_GET['keyword'] ?? '';                               // Filter universal untuk tabel
$kelas_filter_tabel = $_GET['kelas_tabel'] ?? '';              // Filter universal untuk tabel (Admin Only)


// Ambil daftar kelas untuk dropdown filter (hanya jika Admin)
$list_kelas = [];
if ($is_admin) {
    $query_kelas = "SELECT DISTINCT kelas FROM siswa ORDER BY kelas ASC";
    $result_kelas = $koneksi->query($query_kelas);
    if ($result_kelas) {
        while ($row_kelas = $result_kelas->fetch_assoc()) {
            $list_kelas[] = $row_kelas['kelas'];
        }
        $result_kelas->free();
    }
}

// =========================================================================
// LOGIKA FILTER PHP UNTUK QUERY UTAMA DAN COUNT (Diperbarui untuk Universal Filter)
// =========================================================================
$teacher_condition_sql = "";
$teacher_params = [];
$teacher_types = '';

// Filter peran dasar (siswa melihat diri sendiri, guru melihat bimbingan)
if ($is_siswa) {
    $loggedInUserId = $_SESSION['id_siswa'] ?? null;
    if ($loggedInUserId) {
        $teacher_condition_sql = " AND s.id_siswa = ?";
        $teacher_params[] = $loggedInUserId;
        $teacher_types = 'i';
    }
} elseif ($is_guru) {
    $loggedInGuruId = $_SESSION['id_guru_pendamping'] ?? null;
    $param_pembimbing_id = $_GET['pembimbing_id'] ?? null;

    if ($param_pembimbing_id !== null) {
        $teacher_condition_sql = " AND s.pembimbing_id = ?";
        $teacher_params[] = $param_pembimbing_id;
        $teacher_types = 'i';
    } elseif ($loggedInGuruId !== null) {
        $teacher_condition_sql = " AND s.pembimbing_id = ?";
        $teacher_params[] = $loggedInGuruId;
        $teacher_types = 'i';
    }
}

$base_query_sql_abs = "
    SELECT
        s.id_siswa, s.nama_siswa, s.kelas, s.no_induk, s.nisn,
        j.nama_jurusan, tp.nama_tempat_pkl, as_abs.id_absensi,
        COALESCE(as_abs.status_absen, 'Alfa') AS status_absensi_hari_ini,
        as_abs.bukti_foto, as_abs.waktu_input,
        as_abs.jam_datang, as_abs.jam_pulang,
        as_abs.tanggal_absen,
        as_abs.keterangan 
    FROM
        siswa s
    LEFT JOIN absensi_siswa as_abs ON s.id_siswa = as_abs.siswa_id AND as_abs.tanggal_absen BETWEEN ? AND ?
    LEFT JOIN jurusan j ON s.jurusan_id = j.id_jurusan
    LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl
    WHERE s.status = 'Aktif'
    {$teacher_condition_sql}
";

// Parameters for both count and main query, built universally
$common_params = array_merge([$filter_tanggal_mulai, $filter_tanggal_akhir], $teacher_params);
$common_types = 'ss' . $teacher_types;

// Filter Universal (Status, Keyword, Kelas) - Diterapkan jika BUKAN SISWA
if (!$is_siswa) {
    // Filter Status
    if ($filter_status !== 'Semua') {
        $base_query_sql_abs .= " AND COALESCE(as_abs.status_absen, 'Alfa') = ?";
        $common_params[] = $filter_status;
        $common_types .= 's';
    }

    // Filter Keyword
    if (!empty($keyword)) {
        $like_keyword = "%" . $keyword . "%";
        $search_columns = ['s.nama_siswa', 's.no_induk', 's.nisn', 's.kelas', 'j.nama_jurusan', 'tp.nama_tempat_pkl'];
        $search_conditions = [];
        foreach ($search_columns as $col) {
            $search_conditions[] = "$col LIKE ?";
            $common_params[] = $like_keyword;
            $common_types .= 's';
        }
        if (strpos($base_query_sql_abs, 'HAVING') !== false) {
            $base_query_sql_abs .= " AND (" . implode(" OR ", $search_conditions) . ")";
        } else {
            $base_query_sql_abs .= " AND (" . implode(" OR ", $search_conditions) . ")";
        }
    }

    // Filter Kelas (hanya Admin)
    if ($is_admin && !empty($kelas_filter_tabel)) {
        $base_query_sql_abs .= " AND s.kelas = ?";
        $common_params[] = $kelas_filter_tabel;
        $common_types .= 's';
    }
}


// Query untuk menghitung total data
$final_count_query_full = "SELECT COUNT(*) AS total_data FROM (" . $base_query_sql_abs . ") AS subquery_filtered";
$stmt_count = $koneksi->prepare($final_count_query_full);

if ($stmt_count === false) {
    die("Error preparing count query: " . $koneksi->error);
}
if (!empty($common_params)) {
    $bind_args_count = [];
    $bind_args_count[] = $common_types;
    foreach ($common_params as &$param) {
        $bind_args_count[] = &$param;
    }
    call_user_func_array([$stmt_count, 'bind_param'], $bind_args_count);
}
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$total_data = $count_result->fetch_assoc()['total_data'];
$total_pages = ceil($total_data / $limit);
$stmt_count->close();


// Query untuk mengambil data per halaman
$query_sql = $base_query_sql_abs . " ORDER BY as_abs.tanggal_absen DESC, s.kelas ASC, s.nama_siswa ASC LIMIT ? OFFSET ?";
$data_params_final = array_merge($common_params, [&$limit, &$offset]);
$data_types_final = $common_types . 'ii';

$stmt_data = $koneksi->prepare($query_sql);
if ($stmt_data === false) {
    die("Error preparing data query: " . $koneksi->error);
}
if (!empty($data_params_final)) {
    $bind_args_data = [];
    $bind_args_data[] = $data_types_final;
    foreach ($data_params_final as &$param) {
        $bind_args_data[] = &$param;
    }
    call_user_func_array([$stmt_data, 'bind_param'], $bind_args_data);
}
$stmt_data->execute();
$result_absensi = $stmt_data->get_result();
$stmt_data->close();
$koneksi->close();

?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/"
    data-template="vertical-menu-template-free">

<?php include 'partials/head.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">

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
                            echo "
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        Swal.fire({
                                            icon: '{$_SESSION['alert_type']}',
                                            title: '{$_SESSION['alert_title']}',
                                            text: '{$_SESSION['alert_message']}',
                                            confirmButtonColor: '#696cff'
                                        });
                                    });
                                </script>
                                ";
                            unset($_SESSION['alert_message'], $_SESSION['alert_type'], $_SESSION['alert_title']);
                        }
                        ?>

                        <div
                            class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom position-relative">
                            <h4 class="fw-bold mb-0 text-primary animate__animated animate__fadeInLeft">
                                <span class="text-muted fw-light">Absensi /</span>
                                <?php
                                if ($is_siswa) {
                                    echo "Saya";
                                } elseif ($is_guru) {
                                    echo "Siswa Bimbingan";
                                } elseif ($is_admin) {
                                    echo "Semua Siswa";
                                }
                                ?>
                            </h4>
                            <i class="fas fa-clipboard-check fa-2x text-info animate__animated animate__fadeInRight"
                                style="opacity: 0.6;"></i>
                        </div>

                        <?php if (!$is_siswa): // Only show filter section for Admin and Guru 
                        ?>
                            <div class="card mb-4 shadow-lg">
                                <div class="card-body p-3">
                                    <form method="GET" action="" class="row g-2 align-items-end">
                                        <h6 class="col-12 mb-3 text-primary"><i class='bx bx-filter me-1'></i> Filter
                                            Tampilan Harian</h6>

                                        <div class="col-12 col-md-4 col-lg-3">
                                            <label for="tanggalMulaiTabel" class="form-label">Tanggal Mulai:</label>
                                            <input type="text" id="tanggalMulaiTabel" name="tanggal_mulai"
                                                class="form-control flatpickr-date"
                                                value="<?= htmlspecialchars($filter_tanggal_mulai) ?>">
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-3">
                                            <label for="tanggalAkhirTabel" class="form-label">Tanggal Akhir:</label>
                                            <input type="text" id="tanggalAkhirTabel" name="tanggal_akhir"
                                                class="form-control flatpickr-date"
                                                value="<?= htmlspecialchars($filter_tanggal_akhir) ?>">
                                        </div>

                                        <div class="col-12 col-md-4 col-lg-2">
                                            <label for="statusFilter" class="form-label">Status Absen:</label>
                                            <select id="statusFilter" name="status" class="form-select">
                                                <option value="Semua" <?= $filter_status == 'Semua' ? 'selected' : '' ?>>
                                                    Semua Status</option>
                                                <option value="Hadir" <?= $filter_status == 'Hadir' ? 'selected' : '' ?>>
                                                    Hadir</option>
                                                <option value="Sakit" <?= $filter_status == 'Sakit' ? 'selected' : '' ?>>
                                                    Sakit</option>
                                                <option value="Izin" <?= $filter_status == 'Izin' ? 'selected' : '' ?>>Izin
                                                </option>
                                                <option value="Libur" <?= $filter_status == 'Libur' ? 'selected' : '' ?>>
                                                    Libur</option>
                                                <option value="Alfa" <?= $filter_status == 'Alfa' ? 'selected' : '' ?>>Alfa
                                                </option>
                                            </select>
                                        </div>

                                        <?php if ($is_admin): // Dropdown kelas only for Admin 
                                        ?>
                                            <div class="col-12 col-md-4 col-lg-2">
                                                <label for="kelasTabelFilter" class="form-label">Filter Kelas:</label>
                                                <select id="kelasTabelFilter" name="kelas_tabel" class="form-select">
                                                    <option value="">Semua Kelas</option>
                                                    <?php foreach ($list_kelas as $kelas_option): ?>
                                                        <option value="<?= htmlspecialchars($kelas_option) ?>"
                                                            <?= $kelas_filter_tabel == $kelas_option ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($kelas_option) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        <?php endif; ?>

                                        <div class="col-12 col-md-4 col-lg-3">
                                            <label for="keywordSearch" class="form-label">Cari Siswa:</label>
                                            <input type="text" id="keywordSearch" name="keyword" class="form-control"
                                                placeholder="Nama/NISN/Kelas..." value="<?= htmlspecialchars($keyword) ?>">
                                        </div>

                                        <div
                                            class="col-12 d-flex flex-wrap justify-content-end align-items-end pt-3 border-top mt-3">
                                            <div class="col-12 col-md-auto mb-2 mb-md-0 me-md-2">
                                                <button type="submit" class="btn btn-primary w-100">
                                                    <i class="bx bx-filter-alt"></i> Terapkan Filter
                                                </button>
                                            </div>

                                            <?php
                                            $reset_params_current = [];
                                            if ($is_guru && isset($_GET['pembimbing_id']) && !empty($_GET['pembimbing_id'])) {
                                                $reset_params_current['pembimbing_id'] = htmlspecialchars($_GET['pembimbing_id']);
                                            }
                                            $reset_link_current = 'master_data_absensi_siswa.php';
                                            if (!empty($reset_params_current)) {
                                                $reset_link_current .= '?' . http_build_query($reset_params_current);
                                            }

                                            $is_filter_active_for_reset = (!empty($keyword) && !$is_siswa) || ($filter_status !== 'Semua' && !$is_siswa) || $filter_tanggal_mulai !== '2025-07-14' || $filter_tanggal_akhir !== date('Y-m-d') || (!empty($kelas_filter_tabel) && $is_admin);

                                            if ($is_filter_active_for_reset): ?>
                                                <div class="col-12 col-md-auto mb-2 mb-md-0">
                                                    <a href="<?= htmlspecialchars($reset_link_current) ?>"
                                                        class="btn btn-outline-secondary w-100">
                                                        <i class="bx bx-x"></i> Reset Filter
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endif; // End of filter section for Admin and Guru 
                        ?>

                        <div class="card mb-4 shadow-lg">
                            <div class="card-body p-3">
                                <form method="GET" action="generate_absensi_pdf.php" target="_blank"
                                    class="row g-2 align-items-end">
                                    <h6 class="col-12 mb-3 text-info"><i class='bx bxs-file-pdf me-1'></i> Cetak Laporan
                                        Rekap
                                        PDF (Rentang Tanggal)</h6>

                                    <div class="col-md-4 col-lg-3">
                                        <label for="tanggalMulaiPdf" class="form-label">Tanggal Mulai:</label>
                                        <input type="text" id="tanggalMulaiPdf" name="tanggal_mulai"
                                            class="form-control flatpickr-date-pdf"
                                            value="<?= htmlspecialchars($filter_tanggal_mulai) ?>" readonly>
                                    </div>
                                    <div class="col-md-4 col-lg-3">
                                        <label for="tanggalAkhirPdf" class="form-label">Tanggal Akhir:</label>
                                        <input type="text" id="tanggalAkhirPdf" name="tanggal_akhir"
                                            class="form-control flatpickr-date-pdf"
                                            value="<?= htmlspecialchars($filter_tanggal_akhir) ?>" readonly>
                                    </div>
                                    <?php if (!$is_siswa): // Filter Status and Kelas for PDF only for Admin/Guru 
                                    ?>
                                        <div class="col-md-4 col-lg-3">
                                            <label for="statusFilterPdf" class="form-label">Filter Status (PDF):</label>
                                            <select id="statusFilterPdf" name="status" class="form-select">
                                                <option value="Semua" <?= $filter_status == 'Semua' ? 'selected' : '' ?>>
                                                    Semua Status</option>
                                                <option value="Hadir" <?= $filter_status == 'Hadir' ? 'selected' : '' ?>>
                                                    Hadir</option>
                                                <option value="Sakit" <?= $filter_status == 'Sakit' ? 'selected' : '' ?>>
                                                    Sakit</option>
                                                <option value="Izin" <?= $filter_status == 'Izin' ? 'selected' : '' ?>>Izin
                                                </option>
                                                <option value="Libur" <?= $filter_status == 'Libur' ? 'selected' : '' ?>>
                                                    Libur</option>
                                                <option value="Alfa" <?= $filter_status == 'Alfa' ? 'selected' : '' ?>>Alfa
                                                </option>
                                            </select>
                                        </div>
                                        <?php if ($is_admin): // Filter kelas PDF only if admin 
                                        ?>
                                            <div class="col-md-4 col-lg-3">
                                                <label for="kelasPdfFilter" class="form-label">Filter Kelas (PDF):</label>
                                                <select id="kelasPdfFilter" name="kelas_pdf" class="form-select">
                                                    <option value="">Semua Kelas</option>
                                                    <?php foreach ($list_kelas as $kelas_option): ?>
                                                        <option value="<?= htmlspecialchars($kelas_option) ?>"
                                                            <?= $kelas_filter_tabel == $kelas_option ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($kelas_option) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; // End filter status and kelas for PDF 
                                    ?>


                                    <div
                                        class="col-12 d-flex flex-wrap justify-content-end align-items-end pt-3 border-top mt-3">
                                        <?php if ($is_siswa): // If student, send ID 
                                        ?>
                                            <input type="hidden" name="siswa_id_pdf"
                                                value="<?= htmlspecialchars($_SESSION['id_siswa']) ?>">
                                        <?php elseif ($is_guru): // If guru, send supervisor ID 
                                        ?>
                                            <input type="hidden" name="pembimbing_id_pdf"
                                                value="<?= htmlspecialchars($_SESSION['id_guru_pendamping']) ?>">
                                        <?php elseif ($is_admin && isset($_GET['pembimbing_id']) && !empty($_GET['pembimbing_id'])): // Admin viewing a specific teacher's students 
                                        ?>
                                            <input type="hidden" name="pembimbing_id_pdf"
                                                value="<?= htmlspecialchars($_GET['pembimbing_id']) ?>">
                                        <?php endif; ?>

                                        <input type="hidden" name="keyword_pdf"
                                            value="<?= htmlspecialchars($keyword) ?>">

                                        <div class="col-12 col-md mb-2 mb-md-0 me-md-2">
                                            <button type="submit" class="btn btn-danger w-100">
                                                <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
                                            </button>
                                        </div>

                                        <?php
                                        $back_link = 'index.php'; // Default for admin
                                        if ($is_siswa) {
                                            $back_link = 'dashboard_siswa.php';
                                        } elseif ($is_guru) {
                                            $back_link = 'dashboard_guru.php';
                                        }
                                        ?>
                                        <div class="col-12 col-md ms-md-2">
                                            <a href="<?= htmlspecialchars($back_link) ?>"
                                                class="btn btn-outline-secondary w-100">
                                                <i class="bx bx-arrow-back me-1"></i> Kembali
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Absensi Siswa Tanggal: <span
                                        class="text-primary"><?= date('d F Y', strtotime($filter_tanggal_mulai)) ?> s.d.
                                        <?= date('d F Y', strtotime($filter_tanggal_akhir)) ?></span>
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive text-nowrap d-none d-md-block"
                                    style="min-height: calc(100vh - 450px); overflow-y: auto;">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <?php if ($is_admin || $is_guru): ?>
                                                    <th>Nama Siswa</th>
                                                    <th>Kelas</th>
                                                    <th>Jurusan</th>
                                                    <th>Tempat PKL</th>
                                                <?php endif; ?>
                                                <th>Tanggal</th>
                                                <th>Status Absen</th>
                                                <th>Keterangan</th>
                                                <th>Jam Datang</th>
                                                <th>Jam Pulang</th>
                                                <th>Bukti Foto</th>
                                                <?php if ($is_admin): // Show Aksi only for Admin 
                                                ?>
                                                    <th>Aksi</th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            <?php if ($result_absensi->num_rows > 0): $no = $offset + 1;
                                                while ($row = $result_absensi->fetch_assoc()): ?>
                                                    <?php
                                                    $badgeColor = match ($row['status_absensi_hari_ini']) {
                                                        'Hadir' => 'bg-label-success',
                                                        'Sakit' => 'bg-label-warning',
                                                        'Izin'  => 'bg-label-info',
                                                        'Alfa'  => 'bg-label-danger',
                                                        'Libur' => 'bg-label-secondary',
                                                        default => 'bg-label-secondary',
                                                    };
                                                    // PERBAIKAN: Keterangan dari kolom 'keterangan' database
                                                    $keterangan_display_for_column = !empty($row['keterangan']) ? htmlspecialchars($row['keterangan']) : '-';
                                                    // PERBAIKAN: Status Absen from status_absensi_hari_ini
                                                    $status_absen_display_table = htmlspecialchars($row['status_absensi_hari_ini']);

                                                    $bukti_foto_display = !empty($row['bukti_foto']) ?
                                                        "<a href='#' class='badge bg-primary view-image-btn' data-bs-toggle='modal' data-bs-target='#viewImageModal' data-image-url='image_absensi/" . htmlspecialchars($row['bukti_foto']) . "'>
                                                        <i class='bx bx-image'></i> Lihat
                                                    </a>" : '-';

                                                    $jam_datang_display_table = !empty($row['jam_datang']) ? date('H:i', strtotime($row['jam_datang'])) : '-';
                                                    $jam_pulang_display_table = !empty($row['jam_pulang']) ? date('H:i', strtotime($row['jam_pulang'])) : '-';
                                                    ?>
                                                    <tr>
                                                        <td><?= $no++ ?></td>
                                                        <?php if ($is_admin || $is_guru): ?>
                                                            <td><strong><?= htmlspecialchars($row['nama_siswa']) ?></strong></td>
                                                            <td><?= htmlspecialchars($row['kelas']) ?></td>
                                                            <td><?= htmlspecialchars($row['nama_jurusan'] ?? '-') ?></td>
                                                            <td><?= htmlspecialchars($row['nama_tempat_pkl'] ?? '-') ?></td>
                                                        <?php endif; ?>
                                                        <td><?= !empty($row['tanggal_absen']) ? date('d F Y', strtotime($row['tanggal_absen'])) : '-' ?>
                                                        </td>
                                                        <td><span
                                                                class='badge <?= $badgeColor ?>'><?= $status_absen_display_table ?></span>
                                                        </td>
                                                        <td><?= $keterangan_display_for_column ?></td>
                                                        <td><?= $jam_datang_display_table ?></td>
                                                        <td><?= $jam_pulang_display_table ?></td>
                                                        <td><?= $bukti_foto_display ?></td>
                                                        <?php if ($is_admin): // Show Aksi only for Admin 
                                                        ?>
                                                            <td>
                                                                <div class='dropdown'>
                                                                    <button class='btn p-0 dropdown-toggle hide-arrow'
                                                                        data-bs-toggle='dropdown'>
                                                                        <i class='bx bx-dots-vertical-rounded'></i>
                                                                    </button>
                                                                    <div class='dropdown-menu'>
                                                                        <a class='dropdown-item'
                                                                            href='master_data_absensi_siswa_edit.php?<?= !empty($row['id_absensi']) ? 'id=' . htmlspecialchars($row['id_absensi']) : 'siswa_id=' . htmlspecialchars($row['id_siswa']) . '&tanggal=' . urlencode($filter_tanggal_mulai) ?>'>
                                                                            <i class='bx bx-edit-alt me-1'></i> Edit
                                                                        </a>
                                                                        <?php if (!empty($row['id_absensi'])): ?>
                                                                            <a class='dropdown-item text-danger'
                                                                                href='javascript:void(0);'
                                                                                onclick="confirmDelete('<?= htmlspecialchars($row['id_absensi']) ?>', '<?= htmlspecialchars(addslashes($row['nama_siswa'])) ?>', '<?= htmlspecialchars($row['status_absensi_hari_ini']) ?>')">
                                                                                <i class='bx bx-trash me-1'></i> Hapus
                                                                            </a>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        <?php endif; ?>
                                                    </tr>
                                                <?php endwhile;
                                            else: ?>
                                                <tr>
                                                    <td colspan='<?= ($is_admin) ? 12 : (($is_guru) ? 11 : 7) ?>'
                                                        class='text-center py-4'>Tidak ada data absensi
                                                        ditemukan untuk tanggal ini atau filter yang diterapkan.<br>
                                                        <?php if ($is_siswa && ($filter_tanggal_mulai !== '2025-07-14' || $filter_tanggal_akhir !== date('Y-m-d'))): ?>
                                                            Coba sesuaikan rentang tanggal.
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-md-none p-3">
                                    <?php
                                    if ($result_absensi->num_rows > 0) {
                                        $result_absensi->data_seek(0);
                                        $colors = ['primary', 'warning', 'info', 'success', 'danger', 'secondary'];
                                        $color_index = 0;
                                        while ($row_mobile = $result_absensi->fetch_assoc()) {
                                            $current_color = $colors[$color_index % count($colors)];
                                            $color_index++;

                                            $badgeColorMobile = match ($row_mobile['status_absensi_hari_ini']) {
                                                'Hadir' => 'bg-label-success',
                                                'Sakit' => 'bg-label-warning',
                                                'Izin' => 'bg-label-info',
                                                'Alfa' => 'bg-label-danger',
                                                'Libur' => 'bg-label-secondary',
                                                default => 'bg-label-secondary',
                                            };
                                            $keterangan_display_mobile = !empty($row_mobile['keterangan']) ? htmlspecialchars($row_mobile['keterangan']) : '-'; // PERBAIKAN: Keterangan from 'keterangan'
                                            $status_absen_display_mobile = htmlspecialchars($row_mobile['status_absensi_hari_ini']); // Status Absen

                                            $bukti_foto_display_mobile = !empty($row_mobile['bukti_foto']) ?
                                                "<a href='#' class='btn btn-sm btn-outline-primary mt-2 view-image-btn' data-bs-toggle='modal' data-bs-target='#viewImageModal' data-image-url='image_absensi/" . htmlspecialchars($row_mobile['bukti_foto']) . "'>
                                                    <i class='bx bx-image'></i> Lihat Bukti
                                                </a>" : ' ';

                                            $jam_datang_display_mobile = !empty($row_mobile['jam_datang']) ? date('H:i', strtotime($row_mobile['jam_datang'])) : '-';
                                            $jam_pulang_display_mobile = !empty($row_mobile['jam_pulang']) ? date('H:i', strtotime($row_mobile['jam_pulang'])) . " WIB" : '-';
                                    ?>
                                            <div
                                                class="card mb-3 shadow-sm border-start border-4 border-<?= $current_color ?> rounded-3 animate__animated animate__fadeInUp">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h6 class="card-title text-primary mb-0 me-auto">
                                                            <strong><?= htmlspecialchars($row_mobile['nama_siswa']) ?></strong>
                                                        </h6>
                                                        <span
                                                            class="badge <?= $badgeColorMobile ?> ms-2"><?= $status_absen_display_mobile ?></span>
                                                        <?php if ($is_admin): // Show dropdown only for Admin 
                                                        ?>
                                                            <div class='dropdown ms-auto'>
                                                                <button class='btn p-0 dropdown-toggle hide-arrow'
                                                                    data-bs-toggle='dropdown'>
                                                                    <i class='bx bx-dots-vertical-rounded'></i>
                                                                </button>
                                                                <div class='dropdown-menu'>
                                                                    <a class='dropdown-item'
                                                                        href='master_data_absensi_siswa_edit.php?<?= !empty($row_mobile['id_absensi']) ? 'id=' . htmlspecialchars($row_mobile['id_absensi']) : 'siswa_id=' . htmlspecialchars($row_mobile['id_siswa']) . '&tanggal=' . urlencode($filter_tanggal_mulai) ?>'>
                                                                        <i class='bx bx-edit-alt me-1'></i> Edit
                                                                    </a>
                                                                    <?php if (!empty($row_mobile['id_absensi'])): ?>
                                                                        <a class='dropdown-item text-danger' href='javascript:void(0);'
                                                                            onclick="confirmDelete('<?= htmlspecialchars($row_mobile['id_absensi']) ?>', '<?= htmlspecialchars(addslashes($row_mobile['nama_siswa'])) ?>', '<?= htmlspecialchars($row['status_absensi_hari_ini']) ?>')">
                                                                            <i class='bx bx-trash me-1'></i> Hapus
                                                                        </a>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <?php if ($is_admin || $is_guru): ?>
                                                        <p class="card-text mb-1"><small class="text-muted"><i
                                                                    class="bx bx-hash me-1"></i> No Induk:
                                                                <?= htmlspecialchars($row_mobile['no_induk']) ?? '-' ?></small>
                                                        </p>
                                                        <p class="card-text mb-1"><small class="text-muted"><i
                                                                    class="bx bx-award me-1"></i> Kelas:
                                                                <?= htmlspecialchars($row_mobile['kelas']) ?></small></p>
                                                        <p class="card-text mb-1"><small class="text-muted"><i
                                                                    class="bx bx-book-open me-1"></i> Jurusan:
                                                                <?= htmlspecialchars($row_mobile['nama_jurusan'] ?? '-') ?></small>
                                                        </p>
                                                        <p class="card-text mb-1"><small class="text-muted"><i
                                                                    class="bx bx-map-pin me-1"></i> Tempat PKL:
                                                                <?= htmlspecialchars($row_mobile['nama_tempat_pkl'] ?? '-') ?></small>
                                                        </p>
                                                    <?php endif; ?>
                                                    <p class="card-text mb-1"><small class="text-muted"><i
                                                                class="bx bx-calendar me-1"></i> Tanggal:
                                                            <?= !empty($row_mobile['tanggal_absen']) ? date('d F Y', strtotime($row_mobile['tanggal_absen'])) : '-' ?></small>
                                                    </p>
                                                    <p class="card-text mb-1"><small class="text-muted"><i
                                                                class="bx bx-time me-1"></i> Jam Datang:
                                                            <?= $jam_datang_display_mobile ?></small></p>
                                                    <p class="card-text mb-1"><small class="text-muted"><i
                                                                class="bx bx-time-five me-1"></i> Jam Pulang:
                                                            <?= $jam_pulang_display_mobile ?></small></p>
                                                    <p class="card-text mb-1"><small class="text-muted"><i
                                                                class="bx bx-message-square-dots me-1"></i> Keterangan:
                                                            <?= $keterangan_display_mobile ?></small></p>
                                                    <div class="mt-2 text-center">
                                                        <?= $bukti_foto_display_mobile ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php }
                                    } else { ?>
                                        <div class="alert alert-info text-center mt-3 py-4 animate__animated animate__fadeInUp"
                                            role="alert" style="border-radius: 8px;">
                                            <h5 class="alert-heading mb-3"><i class="bx bx-info-circle bx-lg text-info"></i>
                                            </h5>
                                            <p class="mb-0">Tidak ada data absensi ditemukan untuk tanggal ini atau filter
                                                yang diterapkan.</p>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php if ($total_pages > 1) : ?>
                                <div class="card-footer d-flex justify-content-center">
                                    <nav aria-label="Page navigation" class="overflow-auto pb-2" style="max-width: 100%;">
                                        <ul class="pagination mb-0">
                                            <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                                <a class="page-link"
                                                    href="<?= ($page <= 1) ? '#' : '?page=' . ($page - 1) . '&tanggal_mulai=' . urlencode($filter_tanggal_mulai) . '&tanggal_akhir=' . urlencode($filter_tanggal_akhir) . (!empty($keyword) ? '&keyword=' . urlencode($keyword) : '') . ($filter_status !== "Semua" ? '&status=' . urlencode($filter_status) : '') . ($is_admin && !empty($kelas_filter_tabel) ? '&kelas_tabel=' . urlencode($kelas_filter_tabel) : '') . ($is_guru && isset($_GET['pembimbing_id']) && !empty($_GET['pembimbing_id']) ? '&pembimbing_id=' . urlencode($_GET['pembimbing_id']) : ''); ?>">
                                                    <i class="tf-icon bx bx-chevrons-left"></i>
                                                </a>
                                            </li>
                                            <?php
                                            $num_links = 5;
                                            $start_page_link = max(1, $page - floor($num_links / 2));
                                            $end_page_link = min($total_pages, $page + floor($num_links / 2));

                                            $current_get_params = $_GET;
                                            unset($current_get_params['page']);

                                            for ($i = $start_page_link; $i <= $end_page_link; $i++) :
                                                $current_get_params['page'] = $i;
                                                $pagination_link = '?' . http_build_query($current_get_params);
                                            ?>
                                                <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                                                    <a class="page-link"
                                                        href="<?= htmlspecialchars($pagination_link) ?>"><?= $i ?></a>
                                                </li>
                                            <?php endfor;

                                            if ($end_page_link < $total_pages) {
                                                if ($end_page_link < $total_pages - 1) {
                                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                                }
                                                $current_get_params['page'] = $total_pages;
                                                $pagination_link = '?' . http_build_query($current_get_params);
                                                echo '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($pagination_link) . '">' . $total_pages . '</a></li>';
                                            }
                                            ?>
                                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                                <a class="page-link"
                                                    href="<?= ($page >= $total_pages) ? '#' : '?page=' . ($page + 1) . '&tanggal_mulai=' . urlencode($filter_tanggal_mulai) . '&tanggal_akhir=' . urlencode($filter_tanggal_akhir) . (!empty($keyword) ? '&keyword=' . urlencode($keyword) : '') . ($filter_status !== "Semua" ? '&status=' . urlencode($filter_status) : '') . ($is_admin && !empty($kelas_filter_tabel) ? '&kelas_tabel=' . urlencode($kelas_filter_tabel) : '') . ($is_guru && isset($_GET['pembimbing_id']) && !empty($_GET['pembimbing_id']) ? '&pembimbing_id=' . urlencode($_GET['pembimbing_id']) : ''); ?>">
                                                    <i class="tf-icon bx bx-chevrons-right"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                    <?php include './partials/footer.php'; ?>
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewImageModal" tabindex="-1" aria-labelledby="viewImageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewImageModalLabel">Bukti Foto Absensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Bukti Foto Absensi" class="img-fluid rounded shadow-sm"
                        style="max-height: 80vh;">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <a id="downloadImageLink" href="" download class="btn btn-primary">Unduh Gambar</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include './partials/script.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Safely initialize Flatpickr for "Tanggal Mulai Tabel" and "Tanggal Akhir Tabel"
            // These elements are only present for Admin and Guru roles.
            const tanggalMulaiTabelInput = document.getElementById('tanggalMulaiTabel');
            const tanggalAkhirTabelInput = document.getElementById('tanggalAkhirTabel');

            let tanggalMulaiTabelPicker = null;
            let tanggalAkhirTabelPicker = null;

            if (tanggalMulaiTabelInput && tanggalAkhirTabelInput) {
                tanggalMulaiTabelPicker = flatpickr(tanggalMulaiTabelInput, {
                    dateFormat: "Y-m-d",
                    maxDate: "today",
                    onChange: function(selectedDates, dateStr, instance) {
                        if (selectedDates.length > 0) {
                            tanggalAkhirTabelPicker.set('minDate', selectedDates[0]);
                        } else {
                            tanggalAkhirTabelPicker.set('minDate', null);
                        }
                    }
                });

                tanggalAkhirTabelPicker = flatpickr(tanggalAkhirTabelInput, {
                    dateFormat: "Y-m-d",
                    maxDate: "today",
                    defaultDate: "<?= date('Y-m-d') ?>",
                });

                // Set initial min/max dates for tabel pickers
                if (tanggalMulaiTabelPicker.selectedDates.length > 0) {
                    tanggalAkhirTabelPicker.set('minDate', tanggalMulaiTabelPicker.selectedDates[0]);
                }
                if (tanggalAkhirTabelPicker.selectedDates.length > 0) {
                    tanggalMulaiTabelPicker.set('maxDate', tanggalAkhirTabelPicker.selectedDates[0]);
                }
            }


            // Initialize Flatpickr for "Tanggal Mulai PDF" - always present
            const tanggalMulaiPdfPicker = flatpickr("#tanggalMulaiPdf", {
                dateFormat: "Y-m-d",
                maxDate: "today",
                disableMobile: true, // Force desktop picker on mobile
                allowInput: false, // Prevent manual typing
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length > 0) {
                        tanggalAkhirPdfPicker.set('minDate', selectedDates[0]);
                    } else {
                        tanggalAkhirPdfPicker.set('minDate', null);
                    }
                }
            });

            // Initialize Flatpickr for "Tanggal Akhir PDF" - always present
            const tanggalAkhirPdfPicker = flatpickr("#tanggalAkhirPdf", {
                dateFormat: "Y-m-d",
                maxDate: "today",
                disableMobile: true, // Force desktop picker on mobile
                allowInput: false, // Prevent manual typing
                defaultDate: "<?= date('Y-m-d') ?>",
            });

            // Set initial min/max dates for PDF pickers
            if (tanggalMulaiPdfPicker.selectedDates.length > 0) {
                tanggalAkhirPdfPicker.set('minDate', tanggalMulaiPdfPicker.selectedDates[0]);
            }
            if (tanggalAkhirPdfPicker.selectedDates.length > 0) {
                tanggalMulaiPdfPicker.set('maxDate', tanggalAkhirPdfPicker.selectedDates[0]);
            }


            const viewImageModal = document.getElementById('viewImageModal');
            if (viewImageModal) {
                const modalImage = document.getElementById('modalImage');
                const downloadImageLink = document.getElementById('downloadImageLink');

                viewImageModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const imageUrl = button.getAttribute('data-image-url');
                    modalImage.src = imageUrl;
                    downloadImageLink.href = imageUrl;
                    downloadImageLink.download = imageUrl.substring(imageUrl.lastIndexOf('/') + 1);
                });

                viewImageModal.addEventListener('hidden.bs.modal', function() {
                    modalImage.src = '';
                    downloadImageLink.href = '#';
                    downloadImageLink.removeAttribute('download');
                });
            }
        });

        function confirmDelete(id_absensi, nama_siswa, status_absen) {
            Swal.fire({
                title: 'Konfirmasi Hapus Absensi',
                html: `Apakah Anda yakin ingin menghapus absensi <strong>${status_absen}</strong> untuk siswa <strong>${nama_siswa}</strong>?<br>Tindakan ini tidak dapat dibatalkan!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'master_data_absensi_siswa_delete.php?id=' + id_absensi;
                }
            });
        }
    </script>
</body>

</html>