<?php
session_start();

include 'partials/db.php';


// --- LOGIKA KEAMANAN HALAMAN ---

// Keamanan: Hanya admin yang boleh mengakses dashboard ini
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_siswa && !$is_admin) {

    if ($is_guru) {
        header('Location: ../halaman_guru.php'); // Redirect guru ke halaman guru
        exit();
    } else {
        header('Location: ../login.php'); // Jika tidak login sama sekali, redirect ke halaman login
        exit();
    }
}

// --- PENENTUAN ID SISWA UNTUK QUERY ---
$id_siswa_filter = null; // Default null, artinya tidak ada filter siswa_id spesifik
$siswa_nama_display = ""; // Untuk ditampilkan di header

if ($is_siswa) {
    // Jika siswa yang login, ambil id_siswa dari sesi mereka
    $id_siswa_filter = $_SESSION['id_siswa'] ?? null;
    $siswa_nama_display = $_SESSION['siswa_nama'] ?? "Anda"; // Mengambil nama siswa jika ada di sesi
} elseif ($is_admin) {
    // Jika admin yang login
    if (isset($_GET['siswa_id']) && !empty($_GET['siswa_id'])) {
        // Jika admin datang dengan parameter siswa_id di URL, berarti ingin melihat siswa spesifik
        $id_siswa_filter = $_GET['siswa_id'];

        // Ambil nama siswa untuk tampilan header
        $stmt_nama_siswa_admin_view = $koneksi->prepare("SELECT nama_siswa FROM siswa WHERE id_siswa = ?");
        if ($stmt_nama_siswa_admin_view) {
            $stmt_nama_siswa_admin_view->bind_param("i", $id_siswa_filter);
            $stmt_nama_siswa_admin_view->execute();
            $res_nama_siswa_admin_view = $stmt_nama_siswa_admin_view->get_result();
            if ($res_nama_siswa_admin_view->num_rows > 0) {
                $siswa_nama_display = "Siswa: " . htmlspecialchars($res_nama_siswa_admin_view->fetch_assoc()['nama_siswa']);
            } else {
                $siswa_nama_display = "Siswa (ID tidak ditemukan)";
            }
            $stmt_nama_siswa_admin_view->close();
        } else {
            error_log("Failed to prepare statement for siswa name: " . $koneksi->error);
            $siswa_nama_display = "Siswa (Error)";
        }
    } else {
        // Jika admin login tanpa parameter siswa_id, berarti ingin melihat semua laporan
        $siswa_nama_display = "Seluruh Siswa"; // Tampilan untuk admin yang melihat semua
    }
}


$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// Bangun query SQL secara dinamis
$sql_laporan = "SELECT jh.id_jurnal_harian, jh.tanggal, jh.pekerjaan, jh.catatan, jh.siswa_id, s.nama_siswa
                FROM jurnal_harian jh
                LEFT JOIN siswa s ON jh.siswa_id = s.id_siswa";

$params = [];
$types = "";
$where_clauses = [];

// Tambahkan filter berdasarkan siswa_id jika ada
if ($id_siswa_filter !== null && $id_siswa_filter !== "") {
    $where_clauses[] = "jh.siswa_id = ?";
    $params[] = $id_siswa_filter;
    $types .= "i";
}

// Tambahkan filter keyword
if (!empty($keyword)) {
    $where_clauses[] = "(jh.pekerjaan LIKE ? OR jh.catatan LIKE ?)";
    $params[] = "%" . $keyword . "%";
    $params[] = "%" . $keyword . "%";
    $types .= "ss";
}

// Gabungkan klausa WHERE jika ada
if (!empty($where_clauses)) {
    $sql_laporan .= " WHERE " . implode(" AND ", $where_clauses);
}


$stmt_laporan = $koneksi->prepare($sql_laporan);

if ($stmt_laporan) {
    if (!empty($params)) {
        $stmt_laporan->bind_param($types, ...$params);
    }
    $stmt_laporan->execute();
    $result_laporan = $stmt_laporan->get_result();
    $laporan_data = $result_laporan->fetch_all(MYSQLI_ASSOC);
    $stmt_laporan->close();
} else {
    error_log("Failed to prepare statement for laporan harian: " . $koneksi->error);
    $laporan_data = []; // Pastikan $laporan_data tetap array kosong jika ada error
}

$koneksi->close(); // Tutup koneksi setelah semua query selesai
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/"
    data-template="vertical-menu-template-free">
<?php include 'partials/head.php'; ?>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include './partials/sidebar.php'; ?>
            <div class="layout-page">
                <?php include './partials/navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <div
                            class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom position-relative">
                            <h4 class="fw-bold mb-0 text-primary animate__animated animate__fadeInLeft">
                                <span class="text-muted fw-light">
                                    <?php
                                    if ($is_siswa) {
                                        echo "Siswa /";
                                    } elseif ($is_admin) {
                                        echo "Admin /";
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

                        <div class="card mb-4 shadow-lg position-relative" style="border-radius: 10px;">
                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
                                style="pointer-events: none; z-index: 0; opacity: 0.05;">
                                <svg width="100%" height="100%" viewBox="0 0 200 100" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 20 C 50 0, 150 0, 200 20 L 200 80 C 150 100, 50 100, 0 80 Z"
                                        fill="currentColor" opacity="0.1"
                                        class="text-primary animate__animated animate__fadeIn animate__delay-0-1s" />
                                    <path d="M0 30 C 50 10, 150 10, 200 30 L 200 70 C 150 90, 50 90, 0 70 Z"
                                        fill="currentColor" opacity="0.15"
                                        class="text-info animate__animated animate__fadeIn animate__delay-0-2s" />
                                </svg>
                            </div>
                            <div class="card mb-4 shadow-lg">
                                <div
                                    class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                    <div class="d-flex gap-2 w-100 w-md-auto order-1">
                                        <a href="master_kegiatan_harian_add.php<?php echo ($is_admin && !empty($id_siswa_filter)) ? '?siswa_id=' . htmlspecialchars($id_siswa_filter) : ''; ?>"
                                            class="btn btn-primary w-100 animate__animated animate__fadeInUp animate__delay-0-3s">
                                            <i class="bx bx-plus me-1"></i> Tambah Jurnal PKL Harian
                                        </a>
                                    </div>

                                    <div class="d-flex gap-2 w-100 w-md-auto order-2 order-md-2">
                                        <?php
                                        $current_query_params = [];
                                        if (!empty($keyword)) {
                                            $current_query_params['keyword'] = $keyword;
                                        }
                                        if ($id_siswa_filter !== null && $id_siswa_filter !== "") {
                                            $current_query_params['siswa_id'] = $id_siswa_filter;
                                        }
                                        $pdf_link_query_string = !empty($current_query_params) ? '?' . http_build_query($current_query_params) : '';
                                        ?>
                                        <a href="generate_laporan_harian_pdf.php<?= $pdf_link_query_string ?>"
                                            class="btn btn-outline-danger w-100 animate__animated animate__fadeInDown animate__delay-0-3s"
                                            target="_blank">
                                            <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
                                        </a>
                                    </div>

                                    <div class="d-flex gap-2 w-100 w-md-auto order-3 order-md-3">
                                        <a href="<?php echo $is_siswa ? 'dashboard_siswa.php' : 'index.php'; ?>"
                                            class="btn btn-outline-secondary w-100 animate__animated animate__fadeInUp animate__delay-0-2s">
                                            <i class="bx bx-arrow-back me-1"></i> Kembali
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-light border-top p-3 pt-md-2 pb-md-2 position-relative z-1">
                                <div
                                    class="row align-items-center animate__animated animate__fadeInUp animate__delay-0-4s">
                                    <div class="col-12 col-md-8 mb-2 mb-md-0">
                                        <form method="GET" action="" id="filterForm">
                                            <input type="text" name="keyword" class="form-control"
                                                placeholder="Cari laporan berdasarkan kata kunci..." aria-label="Search"
                                                value="<?= htmlspecialchars($keyword) ?>" />
                                            <?php if ($is_admin && $id_siswa_filter !== null && $id_siswa_filter !== ""): ?>
                                            <input type="hidden" name="siswa_id"
                                                value="<?= htmlspecialchars($id_siswa_filter) ?>">
                                            <?php endif; ?>
                                    </div>
                                    <div class="col-12 col-md-4 text-md-end">
                                        <button type="submit" class="btn btn-outline-dark w-100 w-md-auto"><i
                                                class="bx bx-filter-alt me-1"></i> Filter Laporan</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Daftar Jurnal PKL Harian <?= htmlspecialchars($siswa_nama_display) ?>
                                </h5>
                                <small class="text-muted">Total: <?= count($laporan_data) ?> Laporan</small>
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
                                                <?php if ($is_admin && ($id_siswa_filter === null || $id_siswa_filter === "")): ?>
                                                <th>Siswa</th>
                                                <?php endif; ?>
                                                <th>Pekerjaan</th>
                                                <th>Catatan</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            <?php
                                                $no = 1;
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

                                                    // Menggunakan mb_strimwidth untuk pemotongan yang aman untuk multibyte characters
                                                    // Batasi Pekerjaan hingga sekitar 50 karakter
                                                    if (mb_strlen($pekerjaan_display) > 50) {
                                                        $pekerjaan_display = mb_strimwidth($pekerjaan_display, 0, 50, "...");
                                                    }
                                                    // Batasi Catatan hingga sekitar 70 karakter
                                                    if (mb_strlen($catatan_display) > 70) {
                                                        $catatan_display = mb_strimwidth($catatan_display, 0, 70, "...");
                                                    }
                                                ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><strong><?= $formatted_date_display ?></strong></td>
                                                <?php if ($is_admin && ($id_siswa_filter === null || $id_siswa_filter === "")): ?>
                                                <td><?= htmlspecialchars($row['nama_siswa'] ?? '-') ?></td>
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
                                                                <i class="bx bx-edit-alt me-1"></i> Edit Jurnal PKL
                                                                Harian
                                                            </a>
                                                            <div class="dropdown-divider"></div>
                                                            <a class="dropdown-item text-danger"
                                                                href="javascript:void(0);"
                                                                onclick="confirmDeleteKegiatanHarian('<?= htmlspecialchars($row['id_jurnal_harian']) ?>', '<?= htmlspecialchars($formatted_date_display) ?>')">
                                                                <i class="bx bx-trash me-1"></i> Hapus
                                                            </a>
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
                                            <?php if (!empty($keyword)): ?>
                                            Tidak ada laporan yang cocok dengan kata kunci
                                            "<strong><?php echo htmlspecialchars($keyword); ?></strong>".
                                            <?php elseif ($is_siswa): ?>
                                            Anda belum memiliki Jurnal PKL Harian yang tercatat. Silakan tambahkan
                                            laporan pertama Anda.
                                            <?php elseif ($is_admin && $id_siswa_filter !== null && $id_siswa_filter !== ""): ?>
                                            Siswa ini belum memiliki Jurnal PKL Harian.
                                            <?php elseif ($is_admin && ($id_siswa_filter === null || $id_siswa_filter === "")): ?>
                                            Tidak ada laporan kegiatan harian yang ditemukan di sistem.
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <div class="d-md-none p-3">
                                    <div class="text-center text-muted mb-4 animate__animated animate__fadeInUp">
                                        <small><i class="bx bx-mobile me-1"></i> Geser ke bawah untuk melihat Jurnal PKL
                                            Harian Anda</small>
                                    </div>

                                    <?php
                                    if (count($laporan_data) > 0) {
                                        $colors = ['primary', 'warning', 'info', 'success', 'danger'];
                                        $color_index = 0;
                                        $no_mobile = 1;
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
                                        class="card mb-4 shadow-lg border-start border-4 border-<?= $current_color ?> rounded-3 animate__animated animate__fadeInUp">
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
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                            onclick="confirmDeleteKegiatanHarian('<?= htmlspecialchars($row_mobile['id_jurnal_harian']) ?>', '<?= htmlspecialchars($formatted_date_mobile) ?>')">
                                                            <i class="bx bx-trash me-1"></i> Hapus
                                                        </a>
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
                                            <?php if ($is_admin && ($id_siswa_filter === null || $id_siswa_filter === "")): ?>
                                            <div class="d-flex justify-content-end mt-3">
                                                <small class="text-muted"><i class="bx bx-user me-1"></i>
                                                    Siswa:
                                                    <?= htmlspecialchars($row_mobile['nama_siswa'] ?? '-') ?></small>
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
                                    <?php
                                        }
                                    } else {
                                        ?>
                                    <div class="alert alert-info text-center mt-5 py-4 animate__animated animate__fadeInUp animate__delay-0-3s"
                                        role="alert" style="border-radius: 8px;">
                                        <h5 class="alert-heading mb-3"><i class="bx bx-list-plus bx-lg text-info"></i>
                                        </h5>
                                        <p class="mb-3">Belum ada Jurnal PKL Harian yang tercatat di sini.</p>
                                        <p class="mb-0">
                                            Ayo, <a href="master_kegiatan_harian_add.php"
                                                class="alert-link fw-bold">tambahkan Jurnal PKL Harian pertama Anda</a>
                                            sekarang!
                                        </p>
                                    </div>
                                    <?php
                                    }
                                    ?>
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
    <script>
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
                // Mendapatkan parameter URL saat ini
                let currentUrlParams = new URLSearchParams(window.location.search);
                let siswaIdParam = currentUrlParams.get('siswa_id'); // Mengambil siswa_id jika ada

                let deleteUrl = 'master_kegiatan_harian_delete.php?id=' + id;
                if (siswaIdParam) {
                    deleteUrl += '&redirect_siswa_id=' +
                        siswaIdParam; // Menambahkan siswa_id untuk redirect admin
                }
                window.location.href = deleteUrl;
            }
        });
    }
    </script>
    <?php include './partials/script.php'; ?>
</body>

</html>