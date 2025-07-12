<?php
session_start();

// Variabel status peran agar konsisten dan selalu terdefinisi
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';

// Keamanan: Hanya admin atau guru yang boleh mengakses halaman ini
if (!$is_admin && !$is_guru) {
    // Redirect jika tidak memiliki akses
    if ($is_siswa) {
        header('Location: dashboard_siswa.php');
    } else {
        header('Location: ../login.php');
    }
    exit();
}

include 'partials/db.php'; // Sertakan file koneksi database

// --- Filter dan Variabel Pagination ---
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$filter_tanggal = $_GET['tanggal'] ?? date('Y-m-d'); // Default ke tanggal hari ini
$filter_status = $_GET['status'] ?? 'Semua'; // Default ke 'Semua'
$keyword = $_GET['keyword'] ?? ''; // Filter nama siswa/NISN/kelas

// --- Filter untuk Cetak PDF (Rentang Tanggal) ---
$default_tanggal_mulai = date('Y-m-01', strtotime($filter_tanggal));
$tanggal_mulai_pdf = $_GET['tanggal_mulai_pdf'] ?? $default_tanggal_mulai;
$default_tanggal_akhir = date('Y-m-t', strtotime($filter_tanggal));
$tanggal_akhir_pdf = $_GET['tanggal_akhir_pdf'] ?? $default_tanggal_akhir;

// --- Siapkan kondisi dan parameter khusus untuk GURU ---
$teacher_condition_sql = "";
$teacher_params = [];
$teacher_types = '';

if ($is_guru) {
    $teacher_condition_sql = " AND s.pembimbing_id = ?";
    $teacher_params[] = $_SESSION['id_guru_pendamping'];
    $teacher_types = 'i';
}

// --- Query untuk menghitung total data (untuk tabel harian) ---
$count_base_query = "
    SELECT
        s.id_siswa,
        COALESCE(as_abs.status_absen, 'Alfa') AS status_absensi_hari_ini
    FROM
        siswa s
    LEFT JOIN absensi_siswa as_abs ON s.id_siswa = as_abs.siswa_id AND as_abs.tanggal_absen = ?
    LEFT JOIN jurusan j ON s.jurusan_id = j.id_jurusan
    LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl
    WHERE s.status = 'Aktif'
    {$teacher_condition_sql}
";

$count_params_base = array_merge([$filter_tanggal], $teacher_params);
$count_types_base = 's' . $teacher_types;

if (!empty($keyword)) {
    $like_keyword_count = "%" . $keyword . "%";
    $search_columns_count = ['s.nama_siswa', 's.no_induk', 's.nisn', 's.kelas', 'j.nama_jurusan', 'tp.nama_tempat_pkl'];
    $search_conditions_count = [];
    foreach ($search_columns_count as $col) {
        $search_conditions_count[] = "$col LIKE ?";
        $count_params_base[] = $like_keyword_count;
        $count_types_base .= 's';
    }
    $count_base_query .= " AND (" . implode(" OR ", $search_conditions_count) . ")";
}

$final_count_query = "SELECT COUNT(*) AS total_data FROM (" . $count_base_query . ") AS subquery";

if ($filter_status !== 'Semua') {
    $final_count_query .= " WHERE status_absensi_hari_ini = ?";
    $count_params_base[] = $filter_status;
    $count_types_base .= 's';
}

$stmt_count = $koneksi->prepare($final_count_query);
if ($stmt_count === false) {
    die("Error preparing count query: " . $koneksi->error);
}
if (!empty($count_params_base)) {
    $stmt_count->bind_param($count_types_base, ...$count_params_base);
}
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$total_data = $count_result->fetch_assoc()['total_data'];
$total_pages = ceil($total_data / $limit);
$stmt_count->close();


// --- Query untuk mengambil data absensi siswa per halaman ---
$query_sql = "SELECT
                s.id_siswa, s.nama_siswa, s.kelas, s.no_induk, s.nisn,
                j.nama_jurusan, tp.nama_tempat_pkl, as_abs.id_absensi,
                COALESCE(as_abs.status_absen, 'Alfa') AS status_absensi_hari_ini,
                as_abs.keterangan, as_abs.bukti_foto, as_abs.waktu_input
            FROM
                siswa s
            LEFT JOIN absensi_siswa as_abs ON s.id_siswa = as_abs.siswa_id AND as_abs.tanggal_absen = ?
            LEFT JOIN jurusan j ON s.jurusan_id = j.id_jurusan
            LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl
            WHERE s.status = 'Aktif'
            {$teacher_condition_sql}
";

$data_params = array_merge([$filter_tanggal], $teacher_params);
$data_types = 's' . $teacher_types;

if (!empty($keyword)) {
    $like_keyword_data = "%" . $keyword . "%";
    $search_columns_data = ['s.nama_siswa', 's.no_induk', 's.nisn', 's.kelas', 'j.nama_jurusan', 'tp.nama_tempat_pkl'];
    $search_conditions_data = [];
    foreach ($search_columns_data as $col) {
        $search_conditions_data[] = "$col LIKE ?";
        $data_params[] = $like_keyword_data;
        $data_types .= 's';
    }
    $query_sql .= " AND (" . implode(" OR ", $search_conditions_data) . ")";
}

if ($filter_status !== 'Semua') {
    $query_sql .= " HAVING status_absensi_hari_ini = ?";
    $data_params[] = $filter_status;
    $data_types .= 's';
}

$query_sql .= " ORDER BY s.kelas ASC, s.nama_siswa ASC LIMIT ? OFFSET ?";
$data_params[] = $limit;
$data_params[] = $offset;
$data_types .= 'ii';

$stmt_data = $koneksi->prepare($query_sql);
if ($stmt_data === false) {
    die("Error preparing data query: " . $koneksi->error);
}
if (!empty($data_params)) {
    $stmt_data->bind_param($data_types, ...$data_params);
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
                                $alert_icon = $_SESSION['alert_type'];
                                $alert_title = $_SESSION['alert_title'];
                                $alert_text = $_SESSION['alert_message'];
                                echo "
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        Swal.fire({
                                            icon: '{$alert_icon}',
                                            title: '{$alert_title}',
                                            text: '{$alert_text}',
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
                                <span class="text-muted fw-light">Absensi /</span> <?= $is_guru ? 'Siswa Bimbingan' : 'Semua Siswa' ?>
                            </h4>
                            <i class="fas fa-clipboard-check fa-2x text-info animate__animated animate__fadeInRight"
                                style="opacity: 0.6;"></i>
                        </div>

                        <div class="card mb-4 shadow-lg">
                            <div class="card-body p-3">
                                <form method="GET" action="" class="row g-3 align-items-end mb-4 border-bottom pb-3">
                                    <h6 class="mb-3 text-primary"><i class='bx bx-filter me-1'></i> Filter Tampilan
                                        Harian</h6>
                                    <div class="col-md-4 col-lg-3">
                                        <label for="tanggalFilter" class="form-label">Tanggal Absensi:</label>
                                        <input type="text" id="tanggalFilter" name="tanggal"
                                            class="form-control flatpickr-date"
                                            value="<?= htmlspecialchars($filter_tanggal) ?>">
                                    </div>
                                    <div class="col-md-4 col-lg-3">
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
                                    <div class="col-md-4 col-lg-4">
                                        <label for="keywordSearch" class="form-label">Cari Siswa:</label>
                                        <div class="input-group">
                                            <input type="text" id="keywordSearch" name="keyword" class="form-control"
                                                placeholder="Nama/NISN/Kelas..."
                                                value="<?= htmlspecialchars($keyword) ?>">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-search"></i> Terapkan Filter
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-auto col-lg-2">
                                        <?php if (!empty($keyword) || ($filter_status !== 'Semua') || $filter_tanggal !== date('Y-m-d')): ?>
                                        <a href="master_data_absensi_siswa.php" class="btn btn-outline-secondary w-100">
                                            <i class="bx bx-x"></i> Reset Filter
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </form>

                                <form method="GET" action="generate_absensi_pdf.php" target="_blank"
                                    class="row g-3 align-items-end">
                                    <h6 class="mb-3 text-info"><i class='bx bxs-file-pdf me-1'></i> Cetak Laporan Rekap
                                        PDF (Rentang Tanggal)</h6>
                                    <div class="col-md-4 col-lg-3">
                                        <label for="tanggalMulaiPdf" class="form-label">Tanggal Mulai:</label>
                                        <input type="text" id="tanggalMulaiPdf" name="tanggal_mulai"
                                            class="form-control flatpickr-date"
                                            value="<?= htmlspecialchars($tanggal_mulai_pdf) ?>">
                                    </div>
                                    <div class="col-md-4 col-lg-3">
                                        <label for="tanggalAkhirPdf" class="form-label">Tanggal Akhir:</label>
                                        <input type="text" id="tanggalAkhirPdf" name="tanggal_akhir"
                                            class="form-control flatpickr-date"
                                            value="<?= htmlspecialchars($tanggal_akhir_pdf) ?>">
                                    </div>
                                    <div class="col-md-4 col-lg-4">
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
                                    <div class="col-md-auto col-lg-2">
                                        <button type="submit" class="btn btn-danger w-100">
                                            <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Absensi Siswa Tanggal: <span
                                        class="text-primary"><?= date('d F Y', strtotime($filter_tanggal)) ?></span>
                                </h5>
                                <small class="text-muted">Total: <?= $total_data ?> siswa <?= $is_guru ? 'dalam bimbingan Anda' : '' ?></small>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive text-nowrap d-none d-md-block"
                                    style="min-height: calc(100vh - 450px); overflow-y: auto;">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Siswa</th>
                                                <th>Kelas</th>
                                                <th>Jurusan</th>
                                                <th>Tempat PKL</th>
                                                <th>Status Absen</th>
                                                <th>Keterangan</th>
                                                <th>Bukti Foto</th>
                                                <th>Waktu Input</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            <?php if ($result_absensi->num_rows > 0): $no = $offset + 1; while ($row = $result_absensi->fetch_assoc()): ?>
                                            <?php
                                                $badgeColor = match ($row['status_absensi_hari_ini']) {
                                                    'Hadir' => 'bg-label-success',
                                                    'Sakit' => 'bg-label-warning',
                                                    'Izin'  => 'bg-label-info',
                                                    'Alfa'  => 'bg-label-danger',
                                                    'Libur' => 'bg-label-secondary',
                                                    default => 'bg-label-secondary',
                                                };
                                                $keterangan_display = !empty($row['keterangan']) ? htmlspecialchars($row['keterangan']) : '-';
                                                $bukti_foto_display = !empty($row['bukti_foto']) ?
                                                    "<a href='#' class='badge bg-primary view-image-btn' data-bs-toggle='modal' data-bs-target='#viewImageModal' data-image-url='../image_absensi/" . htmlspecialchars($row['bukti_foto']) . "'>
                                                        <i class='bx bx-image'></i> Lihat
                                                    </a>" : '-';
                                                $waktu_input_display = !empty($row['waktu_input']) ? date('H:i', strtotime($row['waktu_input'])) : '-';
                                            ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><strong><?= htmlspecialchars($row['nama_siswa']) ?></strong></td>
                                                <td><?= htmlspecialchars($row['kelas']) ?></td>
                                                <td><?= htmlspecialchars($row['nama_jurusan'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($row['nama_tempat_pkl'] ?? '-') ?></td>
                                                <td><span
                                                        class='badge <?= $badgeColor ?>'><?= htmlspecialchars($row['status_absensi_hari_ini']) ?></span>
                                                </td>
                                                <td><?= $keterangan_display ?></td>
                                                <td><?= $bukti_foto_display ?></td>
                                                <td><?= $waktu_input_display ?></td>
                                                <td>
                                                    <div class='dropdown'>
                                                        <button class='btn p-0 dropdown-toggle hide-arrow'
                                                            data-bs-toggle='dropdown'>
                                                            <i class='bx bx-dots-vertical-rounded'></i>
                                                        </button>
                                                        <div class='dropdown-menu'>
                                                            <a class='dropdown-item'
                                                                href='master_data_absensi_siswa_edit.php?<?= !empty($row['id_absensi']) ? 'id=' . htmlspecialchars($row['id_absensi']) : 'siswa_id=' . htmlspecialchars($row['id_siswa']) . '&tanggal=' . urlencode($filter_tanggal) ?>'>
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
                                            </tr>
                                            <?php endwhile; else: ?>
                                            <tr>
                                                <td colspan='10' class='text-center py-4'>Tidak ada data absensi
                                                    ditemukan untuk tanggal ini atau filter yang diterapkan.</td>
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
                                            $keterangan_display_mobile = !empty($row_mobile['keterangan']) ? htmlspecialchars($row_mobile['keterangan']) : 'Tidak ada keterangan';
                                            $bukti_foto_display_mobile = !empty($row_mobile['bukti_foto']) ?
                                                "<a href='#' class='btn btn-sm btn-outline-primary mt-2 view-image-btn' data-bs-toggle='modal' data-bs-target='#viewImageModal' data-image-url='../image_absensi/" . htmlspecialchars($row_mobile['bukti_foto']) . "'>
                                                    <i class='bx bx-image'></i> Lihat Bukti
                                                </a>" : 'Tidak ada bukti';
                                            $waktu_input_display_mobile = !empty($row_mobile['waktu_input']) ? date('H:i', strtotime($row_mobile['waktu_input'])) : '-';
                                    ?>
                                    <div
                                        class="card mb-3 shadow-sm border-start border-4 border-<?= $current_color ?> rounded-3 animate__animated animate__fadeInUp">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="card-title text-primary mb-0 me-auto">
                                                    <strong><?= htmlspecialchars($row_mobile['nama_siswa']) ?></strong>
                                                </h6>
                                                <span
                                                    class="badge <?= $badgeColorMobile ?> ms-2"><?= htmlspecialchars($row_mobile['status_absensi_hari_ini']) ?></span>
                                                <div class='dropdown ms-auto'>
                                                    <button class='btn p-0 dropdown-toggle hide-arrow'
                                                        data-bs-toggle='dropdown'>
                                                        <i class='bx bx-dots-vertical-rounded'></i>
                                                    </button>
                                                    <div class='dropdown-menu dropdown-menu-end'>
                                                        <a class='dropdown-item'
                                                            href='master_data_absensi_siswa_edit.php?<?= !empty($row_mobile['id_absensi']) ? 'id=' . htmlspecialchars($row_mobile['id_absensi']) : 'siswa_id=' . htmlspecialchars($row_mobile['id_siswa']) . '&tanggal=' . urlencode($filter_tanggal) ?>'>
                                                            <i class='bx bx-edit-alt me-1'></i> Edit
                                                        </a>
                                                        <?php if (!empty($row_mobile['id_absensi'])): ?>
                                                        <a class='dropdown-item text-danger' href='javascript:void(0);'
                                                            onclick="confirmDelete('<?= htmlspecialchars($row_mobile['id_absensi']) ?>', '<?= htmlspecialchars(addslashes($row_mobile['nama_siswa'])) ?>', '<?= htmlspecialchars($row_mobile['status_absensi_hari_ini']) ?>')">
                                                            <i class='bx bx-trash me-1'></i> Hapus
                                                        </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>

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
                                                        class="bx bx-message-square-dots me-1"></i> Keterangan:
                                                    <?= $keterangan_display_mobile ?></small></p>
                                            <p class="card-text mb-1"><small class="text-muted"><i
                                                        class="bx bx-time me-1"></i> Waktu Input:
                                                    <?= $waktu_input_display_mobile ?></small></p>
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
                                                href="<?= ($page <= 1) ? '#' : '?page=' . ($page - 1) . '&tanggal=' . urlencode($filter_tanggal) . (!empty($keyword) ? '&keyword=' . urlencode($keyword) : '') . ($filter_status !== "Semua" ? '&status=' . urlencode($filter_status) : ''); ?>">
                                                <i class="tf-icon bx bx-chevrons-left"></i>
                                            </a>
                                        </li>
                                        <?php
                                            $num_links = 5;
                                            $start_page_link = max(1, $page - floor($num_links / 2));
                                            $end_page_link = min($total_pages, $page + floor($num_links / 2));

                                            if ($end_page_link - $start_page_link + 1 < $num_links) {
                                                if ($start_page_link == 1) {
                                                    $end_page_link = min($total_pages, $num_links);
                                                } elseif ($end_page_link == $total_pages) {
                                                    $start_page_link = max(1, $total_pages - $num_links + 1);
                                                }
                                            }

                                            if ($start_page_link > 1) {
                                                echo '<li class="page-item"><a class="page-link" href="?page=1&tanggal=' . urlencode($filter_tanggal) . (!empty($keyword) ? '&keyword=' . urlencode($keyword) : '') . ($filter_status !== "Semua" ? '&status=' . urlencode($filter_status) : '') . '">1</a></li>';
                                                if ($start_page_link > 2) {
                                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                                }
                                            }

                                            for ($i = $start_page_link; $i <= $end_page_link; $i++) : ?>
                                        <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                                            <a class="page-link"
                                                href="?page=<?= $i ?>&tanggal=<?= urlencode($filter_tanggal) ?><?= !empty($keyword) ? '&keyword=' . urlencode($keyword) : '' ?><?= $filter_status !== "Semua" ? '&status=' . urlencode($filter_status) : '' ?>"><?= $i ?></a>
                                        </li>
                                        <?php endfor;

                                            if ($end_page_link < $total_pages) {
                                                if ($end_page_link < $total_pages - 1) {
                                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                                }
                                                echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '&tanggal=' . urlencode($filter_tanggal) . (!empty($keyword) ? '&keyword=' . urlencode($keyword) : '') . ($filter_status !== "Semua" ? '&status=' . urlencode($filter_status) : '') . '">' . $total_pages . '</a></li>';
                                            }
                                        ?>
                                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                            <a class="page-link"
                                                href="<?= ($page >= $total_pages) ? '#' : '?page=' . ($page + 1) . '&tanggal=' . urlencode($filter_tanggal) . (!empty($keyword) ? '&keyword=' . urlencode($keyword) : '') . ($filter_status !== "Semua" ? '&status=' . urlencode($filter_status) : '') ?>">
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
        <div class="layout-overlay layout-menu-toggle"></div>
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
        flatpickr("#tanggalFilter", { dateFormat: "Y-m-d", maxDate: "today" });

        const tanggalMulaiPdfPicker = flatpickr("#tanggalMulaiPdf", {
            dateFormat: "Y-m-d",
            maxDate: "today",
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    tanggalAkhirPdfPicker.set('minDate', selectedDates[0]);
                } else {
                    tanggalAkhirPdfPicker.set('minDate', null);
                }
            }
        });

        const tanggalAkhirPdfPicker = flatpickr("#tanggalAkhirPdf", {
            dateFormat: "Y-m-d",
            maxDate: "today",
        });

        const viewImageModal = document.getElementById('viewImageModal');
        if(viewImageModal) {
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