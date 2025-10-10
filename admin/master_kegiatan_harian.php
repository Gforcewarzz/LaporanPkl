<?php
session_start();
date_default_timezone_set('Asia/Jakarta'); // Pastikan zona waktu konsisten

include 'partials/db.php'; // Pastikan file koneksi database Anda benar

// --- LOGIKA KEAMANAN HALAMAN ---
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

// [PERBAIKAN] Mendefinisikan variabel $is_admin_or_guru
$is_admin_or_guru = $is_admin || $is_guru;

// Redirect jika pengguna TIDAK login sebagai admin, GURU, maupun SISWA
if (!$is_admin && !$is_guru && !$is_siswa) {
    header('Location: ../login.php'); // Redirect ke halaman login utama
    exit();
}

// --- INISIALISASI FILTER DAN PARAMETER QUERY ---
$id_siswa_filter = null; // Default null, artinya tidak ada filter siswa_id spesifik
$siswa_nama_display = ""; // Untuk ditampilkan di header
$guru_id_bimbingan = $_SESSION['id_guru_pendamping'] ?? null;

$query_params = []; // Array untuk menyimpan nilai parameter prepared statement
$query_types = ""; // String untuk menyimpan tipe parameter prepared statement
$where_clauses = []; // Array untuk menyimpan klausa WHERE

// Ambil nilai filter dari URL (GET parameters)
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
// PENTING: Mengambil start_date dan end_date dari GET
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : '';


// --- PENENTUAN HAK AKSES DAN FILTER AWAL BERDASARKAN PERAN ---
if ($is_siswa) {
    $id_siswa_filter = $_SESSION['id_siswa'] ?? null;
    $siswa_nama_display = $_SESSION['siswa_nama'] ?? "Anda";
    if ($id_siswa_filter !== null) {
        $where_clauses[] = "jh.siswa_id = ?";
        $query_params[] = $id_siswa_filter;
        $query_types .= "i";
    }
} elseif ($is_admin) {
    // Admin bisa filter per siswa (jika ada siswa_id di URL) atau lihat semua
    if (isset($_GET['siswa_id']) && !empty($_GET['siswa_id'])) {
        $id_siswa_filter = $_GET['siswa_id'];
        $where_clauses[] = "jh.siswa_id = ?";
        $query_params[] = $id_siswa_filter;
        $query_types .= "i";

        // Ambil nama siswa untuk tampilan header
        $stmt_nama_siswa_admin_view = $koneksi->prepare("SELECT nama_siswa FROM siswa WHERE id_siswa = ?");
        if ($stmt_nama_siswa_admin_view) {
            $stmt_nama_siswa_admin_view->bind_param("i", $id_siswa_filter);
            $stmt_nama_siswa_admin_view->execute();
            $res_nama_siswa_admin_view = $stmt_nama_siswa_admin_view->get_result();
            $siswa_nama_display = ($res_nama_siswa_admin_view->num_rows > 0) ? "Siswa: " . htmlspecialchars($res_nama_siswa_admin_view->fetch_assoc()['nama_siswa']) : "Siswa (ID tidak ditemukan)";
            $stmt_nama_siswa_admin_view->close();
        } else {
            error_log("Failed to prepare statement for siswa name: " . $koneksi->error);
            $siswa_nama_display = "Siswa (Error)";
        }
    } else {
        $siswa_nama_display = "Seluruh Siswa"; // Tampilan untuk admin yang melihat semua
    }
} elseif ($is_guru) {
    // Jika guru pendamping yang login, hanya tampilkan jurnal siswa bimbingannya
    if ($guru_id_bimbingan !== null) {
        $where_clauses[] = "s.pembimbing_id = ?"; // Filter berdasarkan ID guru pembimbing
        $query_params[] = $guru_id_bimbingan;
        $query_types .= "i";
        $siswa_nama_display = "Siswa Bimbingan Anda"; // Tampilan untuk guru
    } else {
        $siswa_nama_display = "Siswa Bimbingan (ID Guru Tidak Ditemukan)";
    }

    // Jika guru melihat siswa spesifik via GET, tambahkan filter siswa_id
    if (isset($_GET['siswa_id']) && !empty($_GET['siswa_id'])) {
        $temp_siswa_id = $_GET['siswa_id'];
        $where_clauses[] = "jh.siswa_id = ?";
        $query_params[] = $temp_siswa_id;
        $query_types .= "i";

        // Ambil nama siswa untuk tampilan header guru yang melihat siswa spesifik
        $stmt_nama_siswa_guru_view = $koneksi->prepare("SELECT nama_siswa FROM siswa WHERE id_siswa = ?");
        if ($stmt_nama_siswa_guru_view) {
            $stmt_nama_siswa_guru_view->bind_param("i", $temp_siswa_id);
            $stmt_nama_siswa_guru_view->execute();
            $res_nama_siswa_guru_view = $stmt_nama_siswa_guru_view->get_result();
            if ($res_nama_siswa_guru_view->num_rows > 0) {
                $siswa_nama_display = "Siswa: " . htmlspecialchars($res_nama_siswa_guru_view->fetch_assoc()['nama_siswa']);
            }
            $stmt_nama_siswa_guru_view->close();
        }
    }
}

// --- PENERAPAN FILTER KEYWORD ---
if (!empty($keyword)) {
    $where_clauses[] = "(jh.pekerjaan LIKE ? OR jh.catatan LIKE ? OR s.nama_siswa LIKE ? OR j.nama_jurusan LIKE ?)";
    $query_params[] = "%" . $keyword . "%";
    $query_params[] = "%" . $keyword . "%";
    $query_params[] = "%" . $keyword . "%";
    $query_params[] = "%" . $keyword . "%";
    $query_types .= "ssss";
}

// --- PENERAPAN FILTER RENTANG TANGGAL ---
// Memastikan tanggal tidak kosong sebelum menambahkannya ke klausa WHERE
if (!empty($start_date)) {
    $where_clauses[] = "jh.tanggal >= ?";
    $query_params[] = $start_date;
    $query_types .= "s";
}
if (!empty($end_date)) {
    $where_clauses[] = "jh.tanggal <= ?";
    $query_params[] = $end_date;
    $query_types .= "s";
}

// Gabungkan semua klausa WHERE
$where_sql = "";
if (!empty($where_clauses)) {
    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
}

// --- LOGIKA PAGINASI ---
$records_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// Query untuk total record (tanpa LIMIT/OFFSET)
$sql_total_records = "SELECT COUNT(*) as total_records
                      FROM jurnal_harian jh
                      LEFT JOIN siswa s ON jh.siswa_id = s.id_siswa
                      LEFT JOIN jurusan j ON s.jurusan_id = j.id_jurusan" . $where_sql;

$stmt_total = $koneksi->prepare($sql_total_records);
if ($stmt_total) {
    if (!empty($query_params)) {
        $stmt_total->bind_param($query_types, ...$query_params);
    }
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();
    $total_records = $result_total->fetch_assoc()['total_records'];
    $stmt_total->close();
} else {
    error_log("Failed to prepare total records statement: " . $koneksi->error);
    $total_records = 0;
}
$total_pages = ceil($total_records / $records_per_page);

// --- MAIN DATA QUERY UNTUK TABEL ---
$sql_laporan = "SELECT jh.id_jurnal_harian, jh.tanggal, jh.pekerjaan, jh.catatan, jh.siswa_id, s.nama_siswa, j.nama_jurusan
                FROM jurnal_harian jh
                LEFT JOIN siswa s ON jh.siswa_id = s.id_siswa
                LEFT JOIN jurusan j ON s.jurusan_id = j.id_jurusan" . $where_sql .
    " ORDER BY jh.tanggal DESC, jh.id_jurnal_harian DESC LIMIT ? OFFSET ?";

$params_with_pagination = $query_params;
$types_with_pagination = $query_types;

$params_with_pagination[] = $records_per_page;
$params_with_pagination[] = $offset;
$types_with_pagination .= "ii";

$stmt_laporan = $koneksi->prepare($sql_laporan);

$laporan_data = [];
if ($stmt_laporan) {
    if (!empty($params_with_pagination)) {
        $stmt_laporan->bind_param($types_with_pagination, ...$params_with_pagination);
    }
    $stmt_laporan->execute();
    $result_laporan = $stmt_laporan->get_result();
    $laporan_data = $result_laporan->fetch_all(MYSQLI_ASSOC);
    $stmt_laporan->close();
} else {
    error_log("Failed to prepare statement for laporan harian: " . $koneksi->error);
}

$koneksi->close(); // Tutup koneksi setelah semua query selesai
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
                        // Tampilkan SweetAlert jika ada pesan di sesi
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
                                <span class="text-muted fw-light">
                                    <?php
                                    if ($is_siswa) {
                                        echo "Siswa /";
                                    } elseif ($is_admin) {
                                        echo "Admin /";
                                    } elseif ($is_guru) {
                                        echo "Guru /";
                                    }
                                    ?>
                                </span> Jurnal Harian
                            </h4>
                            <i class="fas fa-edit fa-2x text-info animate__animated animate__fadeInRight"
                                style="opacity: 0.6;"></i>
                        </div>

                        <div class="card mb-4 shadow-lg p-4">
                            <div class="card-body p-0">
                                <form method="GET" action="" class="row g-3 align-items-end mb-4 border-bottom pb-4">
                                    <h6 class="mb-3 text-primary fw-semibold"><i class='bx bx-filter me-1'></i> Filter
                                        Tampilan Jurnal</h6>

                                    <div class="col-12 col-md-4 col-lg-3">
                                        <label for="startDateFilter" class="form-label mb-1">Dari Tanggal:</label>
                                        <input type="date" id="startDateFilter" name="start_date" class="form-control"
                                            value="<?= htmlspecialchars($start_date) ?>">
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-3">
                                        <label for="endDateFilter" class="form-label mb-1">Sampai Tanggal:</label>
                                        <input type="date" id="endDateFilter" name="end_date" class="form-control"
                                            value="<?= htmlspecialchars($end_date) ?>">
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-4">
                                        <label for="keywordSearch" class="form-label mb-1">Cari Jurnal:</label>
                                        <div class="input-group">
                                            <input type="text" id="keywordSearch" name="keyword" class="form-control"
                                                placeholder="Pekerjaan, nama siswa, atau jurusan..."
                                                value="<?= htmlspecialchars($keyword) ?>">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-search"></i> <span
                                                    class="d-none d-sm-inline">Terapkan</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-auto col-lg-2 d-flex align-items-end">
                                        <?php
                                        $is_filter_active = !empty($keyword) || !empty($start_date) || !empty($end_date);
                                        $reset_params_arr = [];
                                        if (($is_admin && !empty($_GET['siswa_id'])) || ($is_guru && !empty($_GET['siswa_id']))) {
                                            $reset_params_arr['siswa_id'] = htmlspecialchars($_GET['siswa_id']);
                                        }
                                        $reset_link_query = !empty($reset_params_arr) ? '?' . http_build_query($reset_params_arr) : '';

                                        if ($is_filter_active): ?>
                                            <a href="master_kegiatan_harian.php<?= $reset_link_query ?>"
                                                class="btn btn-outline-secondary w-100">
                                                <i class="bx bx-x"></i> <span class="d-none d-sm-inline">Reset</span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Kartu untuk Aksi: Tambah dan Cetak PDF -->
                        <div class="card mb-4 shadow-sm" id="opsiLanjutan">
                            <div class="card-header">
                                <h6 class="mb-0 text-primary fw-semibold"><i class='bx bx-cog me-1'></i> Opsi Lanjutan
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Bagian Tambah Jurnal -->
                                    <div class="col-12 col-lg-6 border-end-lg">
                                        <p class="mb-2"><strong>Tambah Jurnal Baru</strong></p>
                                        <p class="text-muted small">Klik tombol di bawah untuk menambahkan catatan
                                            kegiatan harian Anda.</p>
                                        <?php if ($is_siswa || ($is_admin && !empty($id_siswa_filter)) || ($is_guru && isset($_GET['siswa_id']) && !empty($_GET['siswa_id']))): ?>
                                            <a href="master_kegiatan_harian_add.php<?php
                                                                                    echo ($is_admin && !empty($id_siswa_filter)) ? '?siswa_id=' . htmlspecialchars($id_siswa_filter) : '';
                                                                                    echo (($is_guru && isset($_GET['siswa_id']) && !empty($_GET['siswa_id']))) ? '?siswa_id=' . htmlspecialchars($_GET['siswa_id']) : '';
                                                                                    ?>" class="btn btn-primary w-100">
                                                <i class="bx bx-plus me-1"></i> Tambah Jurnal Harian
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-outline-secondary w-100" disabled>
                                                <i class="bx bx-plus me-1"></i> Pilih siswa untuk menambah jurnal
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Bagian Cetak PDF dengan Filter Tanggal -->
                                    <div class="col-12 col-lg-6 mt-4 mt-lg-0">
                                        <p class="mb-2"><strong>Cetak Laporan PDF</strong></p>
                                        <p class="text-muted small">Pilih rentang tanggal untuk mencetak laporan.
                                            Kosongkan untuk mencetak semua.</p>
                                        <form action="generate_laporan_harian_pdf.php" method="GET">
                                            <?php
                                            // Sisipkan siswa_id jika ada (untuk admin/guru)
                                            if ($is_admin_or_guru && !empty($id_siswa_filter)) {
                                                echo '<input type="hidden" name="siswa_id" value="' . htmlspecialchars($id_siswa_filter) . '">';
                                            }
                                            ?>
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <label for="startDatePdf" class="form-label-sm">Dari
                                                        Tanggal:</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i
                                                                class="bx bx-calendar"></i></span>
                                                        <input type="text" id="startDatePdf" name="start_date"
                                                            class="form-control" placeholder="Pilih Tanggal...">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="endDatePdf" class="form-label-sm">Sampai
                                                        Tanggal:</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i
                                                                class="bx bx-calendar"></i></span>
                                                        <input type="text" id="endDatePdf" name="end_date"
                                                            class="form-control" placeholder="Pilih Tanggal...">
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-outline-danger w-100 mt-3"
                                                <?= (!$is_siswa && !$id_siswa_filter) ? 'disabled' : '' ?>>
                                                <i class="bx bxs-file-pdf me-1"></i>
                                                <?= (!$is_siswa && !$id_siswa_filter) ? 'Pilih siswa untuk mencetak' : 'Cetak PDF' ?>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Daftar Jurnal Harian: <?= htmlspecialchars($siswa_nama_display) ?>
                                </h5>
                                <small class="text-muted">Total: <?= $total_records ?> Laporan</small>
                            </div>
                            <div class="card-body p-0">
                                <?php if (count($laporan_data) > 0): ?>
                                    <!-- Tampilan Tabel untuk Desktop -->
                                    <div class="table-responsive text-nowrap d-none d-md-block" style="min-height: 400px;">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Hari/Tanggal</th>
                                                    <?php if ($is_admin || ($is_guru && (!isset($_GET['siswa_id']) || empty($_GET['siswa_id'])))): ?>
                                                        <th>Siswa</th>
                                                        <th>Jurusan</th>
                                                    <?php endif; ?>
                                                    <th>Pekerjaan</th>
                                                    <th>Catatan</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="table-border-bottom-0">
                                                <?php
                                                $no = $offset + 1;
                                                foreach ($laporan_data as $row) {
                                                    $hari_indonesia = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
                                                    $nama_hari_inggris = date('l', strtotime($row['tanggal']));
                                                    $formatted_date_display = $hari_indonesia[$nama_hari_inggris] . ', ' . date('d F Y', strtotime($row['tanggal']));

                                                    $pekerjaan_display = htmlspecialchars($row['pekerjaan']);
                                                    $catatan_display = htmlspecialchars($row['catatan'] ?? '-');

                                                    if (mb_strlen($pekerjaan_display) > 50) {
                                                        $pekerjaan_display = mb_strimwidth($pekerjaan_display, 0, 50, "...");
                                                    }
                                                    if (mb_strlen($catatan_display) > 70) {
                                                        $catatan_display = mb_strimwidth($catatan_display, 0, 70, "...");
                                                    }
                                                ?>
                                                    <tr>
                                                        <td><?= $no++ ?></td>
                                                        <td><strong><?= $formatted_date_display ?></strong></td>
                                                        <?php if ($is_admin || ($is_guru && (!isset($_GET['siswa_id']) || empty($_GET['siswa_id'])))): ?>
                                                            <td><?= htmlspecialchars($row['nama_siswa'] ?? '-') ?></td>
                                                            <td><?= htmlspecialchars($row['nama_jurusan'] ?? '-') ?></td>
                                                        <?php endif; ?>
                                                        <td><?= $pekerjaan_display ?></td>
                                                        <td><?= $catatan_display ?></td>
                                                        <td>
                                                            <div class="dropdown">
                                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                                </button>
                                                                <div class="dropdown-menu" style='z-index: 1050;'>
                                                                    <a class="dropdown-item"
                                                                        href="master_kegiatan_harian_edit.php?id=<?= htmlspecialchars($row['id_jurnal_harian']) ?>">
                                                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                                                    </a>
                                                                    <?php if ($is_admin || $is_siswa): ?>
                                                                        <div class="dropdown-divider"></div>
                                                                        <a class="dropdown-item text-danger"
                                                                            href="javascript:void(0);"
                                                                            onclick="confirmDeleteKegiatanHarian('<?= htmlspecialchars($row['id_jurnal_harian']) ?>', '<?= htmlspecialchars($formatted_date_display) ?>')">
                                                                            <i class="bx bx-trash me-1"></i> Hapus
                                                                        </a>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- [PEMBARUAN UI] Tampilan Kartu untuk Mobile -->
                                    <div class="d-md-none p-3">
                                        <?php
                                        $no_mobile = $offset + 1;
                                        // [PEMBARUAN] Array warna untuk border kartu
                                        $card_colors = ['primary', 'danger', 'success', 'warning', 'info'];
                                        $color_index = 0;
                                        foreach ($laporan_data as $row_mobile) {
                                            // [PEMBARUAN] Logika untuk mengganti warna
                                            $current_color = $card_colors[$color_index % count($card_colors)];
                                            $color_index++;

                                            $hari_indonesia = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
                                            $nama_hari_inggris_mobile = date('l', strtotime($row_mobile['tanggal']));
                                            $formatted_date_mobile = $hari_indonesia[$nama_hari_inggris_mobile] . ', ' . date('d F Y', strtotime($row_mobile['tanggal']));
                                        ?>
                                            <!-- [PEMBARUAN UI] Desain kartu baru dengan warna dinamis -->
                                            <div class="card mb-3 shadow-sm animate__animated animate__fadeInUp">
                                                <div
                                                    class="card-header d-flex justify-content-between align-items-center bg-label-<?= $current_color ?> p-2">
                                                    <h6 class="mb-0 text-<?= $current_color ?> fw-bold">
                                                        <i class="bx bx-calendar-event me-1"></i>
                                                        <?= htmlspecialchars($formatted_date_mobile) ?>
                                                    </h6>
                                                    <div class="dropdown">
                                                        <button type="button"
                                                            class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="bx bx-dots-vertical-rounded"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <a class="dropdown-item"
                                                                href="master_kegiatan_harian_edit.php?id=<?= htmlspecialchars($row_mobile['id_jurnal_harian']) ?>">
                                                                <i class="bx bx-edit-alt me-1"></i> Edit Jurnal
                                                            </a>
                                                            <?php if ($is_admin || $is_siswa): ?>
                                                                <div class="dropdown-divider"></div>
                                                                <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                                    onclick="confirmDeleteKegiatanHarian('<?= htmlspecialchars($row_mobile['id_jurnal_harian']) ?>', '<?= htmlspecialchars($formatted_date_mobile) ?>')">
                                                                    <i class="bx bx-trash me-1"></i> Hapus Jurnal
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body p-3">
                                                    <?php if ($is_admin || ($is_guru && (!isset($_GET['siswa_id']) || empty($_GET['siswa_id'])))): ?>
                                                        <div
                                                            class="d-flex justify-content-between text-muted border-bottom pb-2 mb-2">
                                                            <small>
                                                                <i class="bx bx-user me-1"></i>
                                                                <?= htmlspecialchars($row_mobile['nama_siswa'] ?? '-') ?>
                                                            </small>
                                                            <small>
                                                                <i class="bx bx-buildings me-1"></i>
                                                                <?= htmlspecialchars($row_mobile['nama_jurusan'] ?? '-') ?>
                                                            </small>
                                                        </div>
                                                    <?php endif; ?>

                                                    <div class="mb-3">
                                                        <strong class="d-block mb-1"><i
                                                                class="bx bx-briefcase-alt-2 me-1"></i>Pekerjaan
                                                            Dilakukan:</strong>
                                                        <div class="text-muted ps-3 border-start ms-1">
                                                            <?= nl2br(htmlspecialchars($row_mobile['pekerjaan'])) ?>
                                                        </div>
                                                    </div>
                                                    <div class="mb-0">
                                                        <strong class="d-block mb-1"><i class="bx bx-note me-1"></i>Catatan
                                                            Pembimbing:</strong>
                                                        <div class="text-muted ps-3 border-start ms-1">
                                                            <?= !empty($row_mobile['catatan']) ? nl2br(htmlspecialchars($row_mobile['catatan'])) : '<i>Tidak ada catatan.</i>' ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-footer text-center p-1 bg-light">
                                                    <small class="text-muted">Jurnal #<?= $no_mobile++ ?></small>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning text-center mt-4 mx-3" role="alert">
                                        <h5 class="alert-heading"><i class="bx bx-info-circle"></i> Data Tidak Ditemukan
                                        </h5>
                                        <p class="mb-0">
                                            <?php if ($is_filter_active): ?>
                                                Tidak ada laporan yang cocok dengan filter yang diberikan.
                                            <?php elseif ($is_siswa): ?>
                                                Anda belum memiliki Jurnal Harian. Silakan tambahkan laporan pertama Anda.
                                            <?php elseif (($is_admin && $id_siswa_filter) || ($is_guru && isset($_GET['siswa_id']))): ?>
                                                Siswa ini belum memiliki Jurnal Harian.
                                            <?php else: ?>
                                                Tidak ada laporan kegiatan harian yang ditemukan di sistem.
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if ($total_pages > 1): ?>
                                <div class="card-footer bg-light border-top pt-3 pb-2">
                                    <nav aria-label="Page navigation" class="overflow-auto pb-2" style="max-width: 100%;">
                                        <ul class="pagination mb-0">
                                            <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                                                <a class="page-link" href="?page=<?= $current_page - 1 ?><?php
                                                                                                            $pagination_query_params = $_GET;
                                                                                                            unset($pagination_query_params['page']);
                                                                                                            echo '&' . http_build_query($pagination_query_params);
                                                                                                            ?>"><i
                                                        class="tf-icon bx bx-chevrons-left"></i></a>
                                            </li>
                                            <?php
                                            // Logic for pagination links
                                            for ($i = 1; $i <= $total_pages; $i++):
                                                $pagination_query_params = $_GET;
                                                $pagination_query_params['page'] = $i;
                                            ?>
                                                <li class="page-item <?= ($current_page == $i) ? 'active' : '' ?>">
                                                    <a class="page-link"
                                                        href="?<?= http_build_query($pagination_query_params) ?>"><?= $i ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                                                <a class="page-link" href="?page=<?= $current_page + 1 ?><?php
                                                                                                            $pagination_query_params = $_GET;
                                                                                                            unset($pagination_query_params['page']);
                                                                                                            echo '&' . http_build_query($pagination_query_params);
                                                                                                            ?>"><i
                                                        class="tf-icon bx bx-chevrons-right"></i></a>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include './partials/script.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi Flatpickr untuk filter utama (jika diperlukan)
            flatpickr("#startDateFilter", {
                dateFormat: "Y-m-d",
                maxDate: "today"
            });
            flatpickr("#endDateFilter", {
                dateFormat: "Y-m-d",
                maxDate: "today"
            });

            // [PEMBARUAN] Inisialisasi Flatpickr untuk filter cetak PDF
            flatpickr("#startDatePdf", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d F Y",
                maxDate: "today",
                placeholder: "Pilih tanggal mulai..."
            });
            flatpickr("#endDatePdf", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d F Y",
                maxDate: "today",
                placeholder: "Pilih tanggal akhir..."
            });
        });

        // SweetAlert untuk konfirmasi hapus
        function confirmDeleteKegiatanHarian(id, tanggal) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                html: "Anda yakin ingin menghapus jurnal pada tanggal <strong>" + tanggal +
                    "</strong>?<br>Tindakan ini tidak dapat dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    let currentUrlParams = new URLSearchParams(window.location.search);
                    let deleteUrl = 'master_kegiatan_harian_delete.php?id=' + id;
                    for (let pair of currentUrlParams.entries()) {
                        if (pair[0] !== 'id') {
                            deleteUrl += '&redirect_' + pair[0] + '=' + encodeURIComponent(pair[1]);
                        }
                    }
                    window.location.href = deleteUrl;
                }
            });
        }
    </script>
</body>

</html>