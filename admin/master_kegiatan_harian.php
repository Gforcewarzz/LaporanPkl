<?php
session_start();
date_default_timezone_set('Asia/Jakarta'); // Pastikan zona waktu konsisten

include 'partials/db.php'; // Pastikan file koneksi database Anda benar

// --- LOGIKA KEAMANAN HALAMAN ---
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

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
    // Perbaikan: Menggunakan call_user_func_array untuk bind_param
    if (!empty($query_params)) {
        // Buat array referensi untuk bind_param
        $bind_params = array();
        $bind_params[] = &$query_types; // Parameter pertama harus string tipe
        for ($i = 0; $i < count($query_params); $i++) {
            $bind_params[] = &$query_params[$i]; // Semua parameter harus referensi
        }
        call_user_func_array(array($stmt_total, 'bind_param'), $bind_params);
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

$params_with_pagination = $query_params; // Salin parameter filter
$types_with_pagination = $query_types; // Salin tipe parameter filter

$params_with_pagination[] = $records_per_page; // Tambahkan parameter LIMIT
$params_with_pagination[] = $offset; // Tambahkan parameter OFFSET
$types_with_pagination .= "ii"; // Tambahkan tipe untuk LIMIT dan OFFSET (integer)

$stmt_laporan = $koneksi->prepare($sql_laporan);

$laporan_data = [];
if ($stmt_laporan) {
    // Perbaikan: Menggunakan call_user_func_array untuk bind_param
    if (!empty($params_with_pagination)) {
        // Buat array referensi untuk bind_param
        $bind_params_pagination = array();
        $bind_params_pagination[] = &$types_with_pagination; // Parameter pertama harus string tipe
        for ($i = 0; $i < count($params_with_pagination); $i++) {
            $bind_params_pagination[] = &$params_with_pagination[$i]; // Semua parameter harus referensi
        }
        call_user_func_array(array($stmt_laporan, 'bind_param'), $bind_params_pagination);
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

                        <div class="card bg-gradient-primary-to-secondary text-white mb-4 shadow-lg animate__animated animate__fadeInDown"
                            style="border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                            <div
                                class="card-body p-4 d-flex flex-column flex-sm-row justify-content-between align-items-center">
                                <div class="text-center text-sm-start mb-3 mb-sm-0">
                                    <h5 class="card-title text-white mb-1">Catat Progres PKLmu di Sini!</h5>
                                    <p class="card-text text-white-75 small">Setiap laporan adalah langkah menuju
                                        kesuksesan.</p>
                                </div>
                                <div class="text-center text-sm-end position-relative">
                                    <div class="rounded-circle bg-white d-flex justify-content-center align-items-center animate__animated animate__zoomIn animate__delay-0-5s"
                                        style="width: 80px; height: 80px; opacity: 0.2; position: relative; overflow: hidden; z-index: 1;">
                                        <i class="bx bx-check-circle bx-lg text-primary"
                                            style="font-size: 3rem; opacity: 1;"></i>
                                    </div>
                                    <div class="position-absolute rounded-circle bg-white"
                                        style="width: 50px; height: 50px; opacity: 0.1; top: -10px; left: -10px; transform: scale(0.6); z-index: 0;">
                                    </div>
                                    <div class="position-absolute rounded-circle bg-white"
                                        style="width: 60px; height: 60px; opacity: 0.15; bottom: -10px; right: -10px; transform: scale(0.8); z-index: 0;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4 shadow-lg p-4">
                            <div class="card-body p-0">
                                <form method="GET" action="" class="row g-3 align-items-end mb-4 border-bottom pb-4">
                                    <h6 class="mb-3 text-primary fw-semibold"><i class='bx bx-filter me-1'></i> Filter Tampilan Harian</h6>

                                    <div class="col-12 col-md-4 col-lg-3">
                                        <label for="startDateFilter" class="form-label mb-1">Dari Tanggal:</label>
                                        <input type="date" id="startDateFilter" name="start_date"
                                            class="form-control"
                                            value="<?= htmlspecialchars($start_date) ?>">
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-3">
                                        <label for="endDateFilter" class="form-label mb-1">Sampai Tanggal:</label>
                                        <input type="date" id="endDateFilter" name="end_date"
                                            class="form-control"
                                            value="<?= htmlspecialchars($end_date) ?>">
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-4">
                                        <label for="keywordSearch" class="form-label mb-1">Cari Jurnal:</label>
                                        <div class="input-group">
                                            <input type="text" id="keywordSearch" name="keyword" class="form-control"
                                                placeholder="Pekerjaan, catatan, nama siswa, atau jurusan..."
                                                value="<?= htmlspecialchars($keyword) ?>">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-search"></i> <span class="d-none d-sm-inline">Terapkan</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-auto col-lg-2 d-flex align-items-end">
                                        <?php
                                        // Cek apakah ada filter yang aktif untuk tombol Reset
                                        $is_filter_active = !empty($keyword) || !empty($start_date) || !empty($end_date);
                                        // Tambahkan kondisi untuk peran agar filter siswa_id/pembimbing_id tetap terbawa saat reset jika ada
                                        $reset_params_arr = [];
                                        if (($is_admin && !empty($_GET['siswa_id'])) || ($is_guru && !empty($_GET['siswa_id']))) {
                                            $reset_params_arr['siswa_id'] = htmlspecialchars($_GET['siswa_id']);
                                        } elseif ($is_guru && !empty($guru_id_bimbingan)) {
                                            $reset_params_arr['pembimbing_id'] = htmlspecialchars($guru_id_bimbingan);
                                        }
                                        $reset_link_query = !empty($reset_params_arr) ? '?' . http_build_query($reset_params_arr) : '';

                                        if ($is_filter_active): ?>
                                            <a href="master_kegiatan_harian.php<?= $reset_link_query ?>" class="btn btn-outline-secondary w-100">
                                                <i class="bx bx-x"></i> <span class="d-none d-sm-inline">Reset Filter</span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </form>

                                <div class="d-flex gap-2 w-100 w-md-auto mb-3">
                                    <?php if ($is_siswa || ($is_admin && !empty($id_siswa_filter)) || ($is_guru && isset($_GET['siswa_id']) && !empty($_GET['siswa_id']))): ?>
                                        <a href="master_kegiatan_harian_add.php<?php
                                                                                echo ($is_admin && !empty($id_siswa_filter)) ? '?siswa_id=' . htmlspecialchars($id_siswa_filter) : '';
                                                                                echo (($is_guru && isset($_GET['siswa_id']) && !empty($_GET['siswa_id']))) ? '?siswa_id=' . htmlspecialchars($_GET['siswa_id']) : '';
                                                                                ?>"
                                            class="btn btn-primary w-100 animate__animated animate__fadeInUp animate__delay-0-3s">
                                            <i class="bx bx-plus me-1"></i> Tambah Jurnal PKL Harian
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex gap-2 w-100 w-md-auto mb-3">
                                    <?php
                                    // Bangun query params saat ini untuk link PDF
                                    $pdf_query_params = [];
                                    if (!empty($keyword)) {
                                        $pdf_query_params['keyword'] = $keyword;
                                    }
                                    if (!empty($start_date)) {
                                        $pdf_query_params['start_date'] = $start_date;
                                    }
                                    if (!empty($end_date)) {
                                        $pdf_query_params['end_date'] = $end_date;
                                    }
                                    if ($is_siswa && $id_siswa_filter !== null) {
                                        $pdf_query_params['siswa_id'] = $id_siswa_filter;
                                    } elseif ($is_admin && $id_siswa_filter !== null) {
                                        $pdf_query_params['siswa_id'] = $id_siswa_filter;
                                    } elseif ($is_guru && isset($_GET['siswa_id']) && !empty($_GET['siswa_id'])) {
                                        $pdf_query_params['siswa_id'] = htmlspecialchars($_GET['siswa_id']);
                                    } elseif ($is_guru && $guru_id_bimbingan !== null && (!isset($_GET['siswa_id']) || empty($_GET['siswa_id']))) {
                                        $pdf_query_params['pembimbing_id'] = $guru_id_bimbingan;
                                    }

                                    $pdf_link_query_string = !empty($pdf_query_params) ? '?' . http_build_query($pdf_query_params) : '';
                                    ?>
                                    <a href="generate_laporan_harian_pdf.php<?= $pdf_link_query_string ?>"
                                        class="btn btn-outline-danger w-100 animate__animated animate__fadeInDown animate__delay-0-3s"
                                        target="_blank">
                                        <i class="bx bxs-file-pdf me-1"></i> Cetak PDF Laporan
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Daftar Jurnal PKL Harian <?= htmlspecialchars($siswa_nama_display) ?>
                                </h5>
                                <small class="text-muted">Total: <?= $total_records ?> Laporan</small>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive text-nowrap d-none d-md-block"
                                    style="min-height: calc(100vh - 450px); overflow-y: auto;">
                                    <?php if (count($laporan_data) > 0): ?>
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
                                                    $hari_indonesia = [
                                                        'Sunday' => 'Minggu',
                                                        'Monday' => 'Senin',
                                                        'Tuesday' => 'Selasa',
                                                        'Wednesday' => 'Rabu',
                                                        'Thursday' => 'Kamis',
                                                        'Friday' => 'Jumat',
                                                        'Saturday' => 'Sabtu'
                                                    ];
                                                    $nama_hari_inggris = date('l', strtotime($row['tanggal']));
                                                    $formatted_date_display = $hari_indonesia[$nama_hari_inggris] . ', ' . date('d F Y', strtotime($row['tanggal']));

                                                    // Batasi panjang teks untuk tampilan tabel desktop
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
                                                                        <i class="bx bx-edit-alt me-1"></i> Edit Jurnal PKL Harian
                                                                    </a>
                                                                    <?php if ($is_admin || $is_siswa): // Hanya admin dan siswa yang bisa delete 
                                                                    ?>
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
                                                <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    <?php else: ?>
                                        <div class="alert alert-warning text-center mt-4 mx-3" role="alert">
                                            <h5 class="alert-heading"><i class="bx bx-info-circle"></i> Data Tidak Ditemukan
                                            </h5>
                                            <p class="mb-0">
                                                <?php if (!empty($keyword) || !empty($start_date) || !empty($end_date)): ?>
                                                    Tidak ada laporan yang cocok dengan filter yang diberikan.
                                                <?php elseif ($is_siswa): ?>
                                                    Anda belum memiliki Jurnal PKL Harian yang tercatat. Silakan tambahkan
                                                    laporan pertama Anda.
                                                <?php elseif (($is_admin && $id_siswa_filter !== null && $id_siswa_filter !== "") || ($is_guru && isset($_GET['siswa_id']) && !empty($_GET['siswa_id']))): ?>
                                                    Siswa ini belum memiliki Jurnal PKL Harian.
                                                <?php else: ?>
                                                    Tidak ada laporan kegiatan harian yang ditemukan di sistem.
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="d-md-none p-3">
                                    <?php
                                    // Reset pointer hasil query untuk tampilan mobile
                                    if ($result_laporan->num_rows > 0) { // Gunakan $result_laporan
                                        $result_laporan->data_seek(0); // Reset pointer
                                        $colors = ['primary', 'warning', 'info', 'success', 'danger'];
                                        $color_index = 0;
                                        $no_mobile = $offset + 1;
                                        foreach ($laporan_data as $row_mobile) {
                                            $current_color = $colors[$color_index % count($colors)];
                                            $color_index++;
                                            $hari_indonesia = [
                                                'Sunday' => 'Minggu',
                                                'Monday' => 'Senin',
                                                'Tuesday' => 'Selasa',
                                                'Wednesday' => 'Rabu',
                                                'Thursday' => 'Kamis',
                                                'Friday' => 'Jumat',
                                                'Saturday' => 'Sabtu'
                                            ];
                                            $nama_hari_inggris_mobile = date('l', strtotime($row_mobile['tanggal']));
                                            $formatted_date_mobile = $hari_indonesia[$nama_hari_inggris_mobile] . ', ' . date('d F Y', strtotime($row_mobile['tanggal']));
                                    ?>
                                            <div
                                                class="card mb-3 shadow-sm border-start border-4 border-<?= $current_color ?> rounded-3 animate__animated animate__fadeInUp">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                                        <div>
                                                            <h6 class="mb-1 text-<?= $current_color ?>"><i
                                                                    class="bx bx-calendar-event me-1"></i>
                                                                <strong><?= htmlspecialchars($formatted_date_mobile) ?></strong>
                                                            </h6>
                                                            <span class="badge bg-label-<?= $current_color ?>"><i
                                                                    class="bx bx-file me-1"></i> Jurnal PKL Harian
                                                                #<?= $no_mobile++ ?></span>
                                                        </div>
                                                        <div class="dropdown">
                                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="bx bx-dots-vertical-rounded"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <a class="dropdown-item"
                                                                    href="master_kegiatan_harian_edit.php?id=<?= htmlspecialchars($row_mobile['id_jurnal_harian']) ?>">
                                                                    <i class="bx bx-edit-alt me-1"></i> Edit Jurnal PKL Harian
                                                                </a>
                                                                <?php if ($is_admin || $is_siswa): // Hanya admin dan siswa yang bisa delete 
                                                                ?>
                                                                    <div class="dropdown-divider"></div>
                                                                    <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                                        onclick="confirmDeleteKegiatanHarian('<?= htmlspecialchars($row_mobile['id_jurnal_harian']) ?>', '<?= htmlspecialchars($formatted_date_mobile) ?>')">
                                                                        <i class="bx bx-trash me-1"></i> Hapus
                                                                    </a>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="mb-2">
                                                        <strong class="text-dark"><i class="bx bx-task me-1"></i>
                                                            Pekerjaan:</strong><br>
                                                        <?= nl2br(htmlspecialchars($row_mobile['pekerjaan'])) ?>
                                                    </div>
                                                    <div class="mb-0 text-wrap">
                                                        <strong class="text-dark"><i class="bx bx-info-circle me-1"></i>
                                                            Catatan:</strong><br>
                                                        <?= nl2br(htmlspecialchars($row_mobile['catatan'] ?? '-')) ?>
                                                    </div>
                                                    <?php if ($is_admin || ($is_guru && (!isset($_GET['siswa_id']) || empty($_GET['siswa_id'])))): ?>
                                                        <div class="d-flex justify-content-end mt-3">
                                                            <small class="text-muted"><i class="bx bx-user me-1"></i>
                                                                Siswa:
                                                                <?= htmlspecialchars($row_mobile['nama_siswa'] ?? '-') ?></small>
                                                        </div>
                                                        <div class="d-flex justify-content-end mt-1">
                                                            <small class="text-muted"><i class="bx bx-book-open me-1"></i>
                                                                Jurusan:
                                                                <?= htmlspecialchars($row_mobile['nama_jurusan'] ?? '-') ?></small>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="d-flex justify-content-end mt-3">
                                                        <small class="text-muted"><i class="bx bx-calendar-check me-1"></i>
                                                            Dilaporkan:
                                                            <?= date('d F Y, H:i', strtotime($row_mobile['tanggal'])) ?>
                                                            WIB</small>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php }
                                    } else { ?>
                                        <div class="alert alert-info text-center mt-5 py-4 animate__animated animate__fadeInUp animate__delay-0-3s"
                                            role="alert" style="border-radius: 8px;">
                                            <h5 class="alert-heading mb-3"><i class="bx bx-list-plus bx-lg text-info"></i>
                                            </h5>
                                            <p class="mb-3">Belum ada Jurnal PKL Harian yang tercatat di sini.</p>
                                            <p class="mb-0">
                                                <?php if (!empty($keyword) || !empty($start_date) || !empty($end_date)): ?>
                                                    Tidak ada laporan yang cocok dengan filter yang diberikan.
                                                <?php elseif ($is_siswa): ?>
                                                    Anda belum memiliki Jurnal PKL Harian yang tercatat. Silakan tambahkan
                                                    laporan pertama Anda.
                                                <?php elseif (($is_admin && $id_siswa_filter !== null && $id_siswa_filter !== "") || ($is_guru && isset($_GET['siswa_id']) && !empty($_GET['siswa_id']))): ?>
                                                    Siswa ini belum memiliki Jurnal PKL Harian.
                                                <?php else: ?>
                                                    Tidak ada laporan kegiatan harian yang ditemukan di sistem.
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php if ($total_pages > 1): ?>
                                <div class="card-footer bg-light border-top pt-3 pb-2">
                                    <nav aria-label="Page navigation" class="overflow-auto pb-2" style="max-width: 100%;">
                                        <ul class="pagination mb-0">
                                            <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                                                <a class="page-link"
                                                    href="?page=<?= $current_page - 1 ?><?php
                                                                                        // Pertahankan semua filter yang ada saat navigasi paginasi
                                                                                        $pagination_query_params = $_GET;
                                                                                        $pagination_query_params['page'] = $current_page - 1;
                                                                                        echo '&' . http_build_query($pagination_query_params);
                                                                                        ?>"
                                                    aria-label="Previous">
                                                    <i class="tf-icon bx bx-chevrons-left"></i>
                                                </a>
                                            </li>
                                            <?php
                                            $num_links = 5;
                                            $start_page_link = max(1, $current_page - floor($num_links / 2));
                                            $end_page_link = min($total_pages, $current_page + floor($num_links / 2));

                                            if ($end_page_link - $start_page_link + 1 < $num_links) {
                                                if ($start_page_link == 1) {
                                                    $end_page_link = min($total_pages, $num_links);
                                                } elseif ($end_page_link == $total_pages) {
                                                    $start_page_link = max(1, $total_pages - $num_links + 1);
                                                }
                                            }

                                            if ($start_page_link > 1) {
                                                $temp_params = $_GET;
                                                $temp_params['page'] = 1;
                                                echo '<li class="page-item"><a class="page-link" href="?' . http_build_query($temp_params) . '">1</a></li>';
                                                if ($start_page_link > 2) {
                                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                                }
                                            }

                                            for ($i = $start_page_link; $i <= $end_page_link; $i++):
                                                $temp_params = $_GET;
                                                $temp_params['page'] = $i;
                                            ?>
                                                <li class="page-item <?= ($current_page == $i) ? 'active' : '' ?>">
                                                    <a class="page-link"
                                                        href="?<?= http_build_query($temp_params) ?>"><?= $i ?></a>
                                                </li>
                                            <?php endfor;

                                            if ($end_page_link < $total_pages) {
                                                if ($end_page_link < $total_pages - 1) {
                                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                                }
                                                $temp_params = $_GET;
                                                $temp_params['page'] = $total_pages;
                                                echo '<li class="page-item"><a class="page-link" href="?' . http_build_query($temp_params) . '">' . $total_pages . '</a></li>';
                                            }
                                            ?>
                                            <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                                                <a class="page-link"
                                                    href="?page=<?= $current_page + 1 ?><?php
                                                                                        $pagination_query_params = $_GET;
                                                                                        $pagination_query_params['page'] = $current_page + 1;
                                                                                        echo '&' . http_build_query($pagination_query_params);
                                                                                        ?>"
                                                    aria-label="Next">
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
                    <h5 class="modal-title" id="viewImageModalLabel">Bukti Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Bukti Foto" class="img-fluid rounded shadow-sm"
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
            // Flatpickr for "Dari Tanggal" filter (range start)
            flatpickr("#startDateFilter", {
                dateFormat: "Y-m-d",
                // Hapus wrap: true karena input type="date" native sudah punya UI sendiri
                // Jika ingin UI Flatpickr yang penuh dengan ikon, gunakan type="text" + wrap:true
                // dan tambahkan ikon manual di HTML atau via Flatpickr option for icon
                maxDate: "today" // Tanggal tidak bisa lebih dari hari ini
            });

            // Flatpickr for "Sampai Tanggal" filter (range end)
            flatpickr("#endDateFilter", {
                dateFormat: "Y-m-d",
                maxDate: "today" // Tanggal akhir tidak bisa lebih dari hari ini
            });

            // Logika untuk menyinkronkan min/max date di Flatpickr (opsional jika menggunakan type="date" native)
            // Karena menggunakan type="date" native, browser yang menangani validasi min/max.
            // Namun, jika Anda ingin Flatpickr tetap memaksakan validasi lintas input,
            // Anda bisa tambahkan onChange event seperti sebelumnya.
            // Untuk kesederhanaan dengan type="date", saya hapus sinkronisasi JS nya.

            // Modal for image viewing (if applicable to this page)
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

        // SweetAlert for delete confirmation for Jurnal Harian
        function confirmDeleteKegiatanHarian(id, tanggal) {
            Swal.fire({
                title: 'Konfirmasi Hapus Jurnal Harian',
                html: "Apakah Anda yakin ingin menghapus Jurnal Harian pada tanggal <strong>" + tanggal +
                    "</strong>?<br>Tindakan ini tidak dapat dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus Sekarang!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    let currentUrlParams = new URLSearchParams(window.location.search);
                    let deleteUrl = 'master_kegiatan_harian_delete.php?id=' + id;

                    // Tambahkan semua parameter filter yang ada ke URL redirect
                    for (let pair of currentUrlParams.entries()) {
                        if (pair[0] !== 'id') { // Pastikan tidak ada duplikasi ID jurnal
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
</html>