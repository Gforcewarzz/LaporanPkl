<?php

session_start();

// Keamanan: Hanya admin yang boleh mengakses dashboard ini
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_admin) {
    if ($is_siswa) {
        header('Location: dashboard_siswa.php'); // Redirect siswa ke dashboard siswa
        exit();
    } elseif ($is_guru) {
        header('Location: ../halaman_guru.php'); // Redirect guru ke halaman guru
        exit();
    } else {
        header('Location: ../login.php'); // Jika tidak login sama sekali, redirect ke halaman login
        exit();
    }
}

include 'partials/db.php';
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// --- Start Pagination Variables ---
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
// --- End Pagination Variables ---

$sql_base = "SELECT tp.*, j.nama_jurusan
             FROM tempat_pkl tp
             LEFT JOIN jurusan j ON tp.jurusan_id = j.id_jurusan";

$params = [];
$types = "";
$where_clauses = [];

if (!empty($keyword)) {
    $where_clauses[] = "(tp.nama_tempat_pkl LIKE ? OR tp.nama_instruktur LIKE ?)";
    $params[] = "%" . $keyword . "%";
    $params[] = "%" . $keyword . "%";
    $types .= "ss";
}

if (!empty($where_clauses)) {
    $sql_base .= " WHERE " . implode(" AND ", $where_clauses);
}

// Query untuk menghitung total data (tanpa LIMIT dan OFFSET)
$count_sql = "SELECT COUNT(tp.id_tempat_pkl) AS total_data FROM tempat_pkl tp";
if (!empty($where_clauses)) {
    $count_sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$stmt_count = $koneksi->prepare($count_sql);
if ($stmt_count) {
    if (!empty($params)) {
        // Pass parameters by reference for bind_param
        $bind_params_count = array_merge([$types], $params);
        call_user_func_array([$stmt_count, 'bind_param'], ref_values($bind_params_count));
    }
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $total_data = $result_count->fetch_assoc()['total_data'];
    $stmt_count->close();
} else {
    error_log("Failed to prepare count statement for tempat_pkl: " . $koneksi->error);
    $total_data = 0;
}

$total_pages = ceil($total_data / $limit);

// Query untuk mengambil data dengan LIMIT dan OFFSET
$sql_data = $sql_base . " ORDER BY tp.nama_tempat_pkl ASC LIMIT ? OFFSET ?";

$stmt_data = $koneksi->prepare($sql_data);

if ($stmt_data) {
    // Add limit and offset parameters to the binding
    $params_data = array_merge($params, [$limit, $offset]);
    $types_data = $types . "ii"; // Add 'ii' for integer types of limit and offset

    // Use call_user_func_array for binding with dynamic parameters
    // Ensure all parameters are passed by reference
    $bind_params_data = array_merge([$types_data], $params_data);
    call_user_func_array([$stmt_data, 'bind_param'], ref_values($bind_params_data));

    $stmt_data->execute();
    $result_data = $stmt_data->get_result();
    $data_tempat_pkl = $result_data->fetch_all(MYSQLI_ASSOC);
    $stmt_data->close();
} else {
    error_log("Failed to prepare data statement for tempat_pkl: " . $koneksi->error);
    $data_tempat_pkl = [];
}

// Helper function to pass array values by reference
function ref_values($arr)
{
    if (strnatcmp(phpversion(), '5.3') >= 0) { // Reference is used only when PHP version is 5.3 or greater
        $refs = [];
        foreach ($arr as $key => $value) {
            $refs[$key] = &$arr[$key];
        }
        return $refs;
    }
    return $arr;
}

// Note: Closing $koneksi here would cause issues if it's used by other partials or later in the script.
// It's usually better to close the connection once all database operations for the page are complete,
// often at the very end of the main script or within a `finally` block if using try-catch.
// For this script, since it's a standalone page, closing it at the end is fine.
// Removed $koneksi->close() from here, as it's typically handled after all includes and rendering.
// If your 'partials/db.php' handles connection closing at a later point, this is not needed here.
// If it only opens the connection, then closing it after all DB operations in this script is appropriate.

?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default">
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
                                <span class="text-muted fw-light">Master /</span> Data Tempat PKL
                            </h4>
                            <i class="fas fa-building fa-2x text-info animate__animated animate__fadeInRight"
                                style="opacity: 0.6;"></i>
                        </div>

                        <div class="card bg-gradient-primary-to-secondary text-white mb-4 shadow-lg animate__animated animate__fadeInDown"
                            style="border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                            <div
                                class="card-body p-4 d-flex flex-column flex-sm-row justify-content-between align-items-center">
                                <div class="text-center text-sm-start mb-3 mb-sm-0">
                                    <h5 class="card-title text-white mb-1">Manajemen Data Tempat PKL</h5>
                                    <p class="card-text text-white-75 small">Kelola informasi detail seluruh mitra
                                        perusahaan/instansi.</p>
                                </div>
                                <div class="text-center text-sm-end">
                                    <div class="rounded-circle bg-white d-flex justify-content-center align-items-center animate__animated animate__zoomIn animate__delay-0-5s"
                                        style="width: 80px; height: 80px; opacity: 0.2; position: relative; z-index: 1;">
                                        <i class="bx bx-building-house bx-lg text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4 shadow-lg">
                            <div
                                class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                                <div class="d-flex flex-column flex-md-row gap-2 w-100 w-md-auto order-1">
                                    <a href="master_tempat_pkl_add.php" class="btn btn-primary w-100">
                                        <i class="bx bx-plus me-1"></i> Tambah Tempat PKL
                                    </a>
                                </div>

                                <div class="d-flex flex-column flex-md-row gap-2 w-100 w-md-auto order-2 order-md-2">
                                    <a href="generate_tempat_pkl_pdf.php<?= !empty($keyword) ? '?keyword=' . htmlspecialchars($keyword) : '' ?>"
                                        class="btn btn-outline-danger w-100" target="_blank">
                                        <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
                                    </a>
                                    <a href="generate_tempat_pkl_excel.php<?= !empty($keyword) ? '?keyword=' . htmlspecialchars($keyword) : '' ?>"
                                        class="btn btn-outline-success w-100" target="_blank">
                                        <i class="bx bxs-file-excel me-1"></i> Ekspor Excel
                                    </a>
                                </div>

                                <div class="d-flex flex-column flex-md-row gap-2 w-100 w-md-auto order-3 order-md-3">
                                    <a href="index.php" class="btn btn-outline-secondary w-100">
                                        <i class="bx bx-arrow-back me-1"></i> Kembali
                                    </a>
                                </div>
                            </div>

                            <div class="card-footer bg-light border-top p-3">
                                <form method="GET" action="">
                                    <div class="row align-items-center">
                                        <div class="col-md-8 mb-2 mb-md-0">
                                            <input type="text" name="keyword" class="form-control"
                                                value="<?= htmlspecialchars($keyword) ?>"
                                                placeholder="Cari Tempat PKL berdasarkan nama tempat atau instruktur...">
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <button type="submit" class="btn btn-outline-dark w-100 w-md-auto">
                                                <i class="bx bx-filter-alt me-1"></i> Filter Tempat PKL
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Daftar Lengkap Tempat PKL</h5>
                                <small class="text-muted">Informasi detail seluruh mitra perusahaan/instansi</small>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive text-nowrap d-none d-md-block"
                                    style="min-height: calc(100vh - 450px); overflow-y: auto;">
                                    <?php if (!empty($data_tempat_pkl)): ?>
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Nama Perusahaan</th>
                                                    <th>Alamat</th>
                                                    <th>Kontak</th>
                                                    <th>Instruktur</th>
                                                    <th>Kuota</th>
                                                    <th>Jurusan</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="table-border-bottom-0">
                                                <?php
                                                $no_table = $offset + 1;
                                                foreach ($data_tempat_pkl as $data) {
                                                ?>
                                                    <tr>
                                                        <td><?= $no_table++ ?></td>
                                                        <td><strong><?= htmlspecialchars($data['nama_tempat_pkl']) ?></strong>
                                                        </td>
                                                        <td><?= htmlspecialchars($data['alamat']) ?></td>
                                                        <td><?= htmlspecialchars($data['alamat_kontak']) ?></td>
                                                        <td><?= htmlspecialchars($data['nama_instruktur']) ?></td>
                                                        <td><span class='badge bg-label-info me-1'><?= $data['kuota_siswa'] ?>
                                                                Siswa</span></td>
                                                        <td><?= htmlspecialchars($data['nama_jurusan'] ?: '-') ?></td>
                                                        <td>
                                                            <div class='dropdown'>
                                                                <button type='button' class='btn p-0 dropdown-toggle hide-arrow'
                                                                    data-bs-toggle='dropdown'>
                                                                    <i class='bx bx-dots-vertical-rounded'></i>
                                                                </button>
                                                                <div class='dropdown-menu'>
                                                                    <a class='dropdown-item'
                                                                        href='master_tempat_pkl_edit.php?id=<?= htmlspecialchars($data['id_tempat_pkl']) ?>'>
                                                                        <i class='bx bx-edit-alt me-1'></i> Edit
                                                                    </a>
                                                                    <a class='dropdown-item text-danger'
                                                                        href='javascript:void(0);'
                                                                        onclick="confirmDeleteTempatPKL('<?= htmlspecialchars($data['id_tempat_pkl']) ?>', '<?= htmlspecialchars($data['nama_tempat_pkl']) ?>')">
                                                                        <i class='bx bx-trash me-1'></i> Hapus
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
                                        <div class="alert alert-info text-center mt-5 py-4 animate__animated animate__fadeInUp"
                                            role="alert"
                                            style="border-radius: 8px; min-height: 200px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                            <h5 class="alert-heading mb-3"><i class="bx bx-info-circle bx-lg text-info"></i>
                                            </h5>
                                            <p class="mb-3">Tidak ada data tempat PKL ditemukan dengan kriteria tersebut.
                                            </p>
                                            <p class="mb-0">
                                                <a href="master_tempat_pkl_add.php" class="alert-link fw-bold">Tambahkan
                                                    tempat PKL baru</a> atau coba filter lainnya!
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="d-none d-md-block">
                                    <?php if ($total_data > 0 && $total_pages > 1) : ?>
                                        <nav aria-label="Page navigation" class="mt-3">
                                            <ul class="pagination justify-content-center">
                                                <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                                    <a class="page-link"
                                                        href="<?= ($page <= 1) ? '#' : '?page=' . ($page - 1) . (!empty($keyword) ? '&keyword=' . htmlspecialchars($keyword) : ''); ?>">Previous</a>
                                                </li>
                                                <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                                                    <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                                                        <a class="page-link"
                                                            href="?page=<?= $i ?><?= !empty($keyword) ? '&keyword=' . htmlspecialchars($keyword) : ''; ?>"><?= $i ?></a>
                                                    </li>
                                                <?php endfor; ?>
                                                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                                    <a class="page-link"
                                                        href="<?= ($page >= $total_pages) ? '#' : '?page=' . ($page + 1) . (!empty($keyword) ? '&keyword=' . htmlspecialchars($keyword) : ''); ?>">Next</a>
                                                </li>
                                            </ul>
                                        </nav>
                                    <?php endif; ?>
                                </div>

                                <div class="d-md-none p-3">
                                    <?php
                                    if (!empty($data_tempat_pkl)) {
                                        $colors = ['primary', 'warning', 'info', 'success', 'danger'];
                                        $color_index = 0;
                                        foreach ($data_tempat_pkl as $data) {
                                            $current_color = $colors[$color_index % count($colors)];
                                            $color_index++;
                                    ?>
                                            <div
                                                class="card mb-3 shadow-sm border-start border-4 border-<?= $current_color ?> rounded-3 animate__animated animate__fadeInUp">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                                        <div>
                                                            <h6 class="mb-1 text-primary"><i class="bx bx-building me-1"></i>
                                                                <strong><?= htmlspecialchars($data['nama_tempat_pkl']) ?></strong>
                                                            </h6>
                                                            <span class="badge bg-label-<?= $current_color ?>"><i
                                                                    class="bx bx-group me-1"></i>
                                                                Kuota: <?= htmlspecialchars($data['kuota_siswa']) ?>
                                                                Siswa</span>
                                                        </div>
                                                        <div class="dropdown">
                                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                                data-bs-toggle="dropdown">
                                                                <i class="bx bx-dots-vertical-rounded"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <a class="dropdown-item"
                                                                    href="master_tempat_pkl_edit.php?id=<?= htmlspecialchars($data['id_tempat_pkl']) ?>">
                                                                    <i class="bx bx-edit-alt me-1"></i> Edit Data
                                                                </a>
                                                                <div class="dropdown-divider"></div>
                                                                <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                                    onclick="confirmDeleteTempatPKL('<?= htmlspecialchars($data['id_tempat_pkl']) ?>', '<?= htmlspecialchars($data['nama_tempat_pkl']) ?>')">
                                                                    <i class="bx bx-trash me-1"></i> Hapus
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="mb-2">
                                                        <strong class="text-dark"><i class="bx bx-map-alt me-1"></i>
                                                            Alamat:</strong><br>
                                                        <?= htmlspecialchars($data['alamat']) ?>
                                                    </div>
                                                    <div class="mb-2">
                                                        <strong class="text-dark"><i class="bx bx-phone me-1"></i>
                                                            Kontak:</strong><br>
                                                        <?= htmlspecialchars($data['alamat_kontak']) ?>
                                                    </div>
                                                    <div class="mb-2">
                                                        <strong class="text-dark"><i class="bx bx-user-circle me-1"></i>
                                                            Instruktur:</strong><br>
                                                        <?= htmlspecialchars($data['nama_instruktur']) ?>
                                                    </div>
                                                    <div class="mb-0">
                                                        <strong class="text-dark"><i class="bx bx-book-open me-1"></i>
                                                            Jurusan:</strong><br>
                                                        <?= htmlspecialchars($data['nama_jurusan'] ?: '-') ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                    } else {
                                        ?>
                                        <div class="alert alert-info text-center mt-5 py-4 animate__animated animate__fadeInUp"
                                            role="alert"
                                            style="border-radius: 8px; min-height: 200px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                            <h5 class="alert-heading mb-3"><i class="bx bx-info-circle bx-lg text-info"></i>
                                            </h5>
                                            <p class="mb-3">Tidak ada data tempat PKL ditemukan dengan kriteria tersebut.
                                            </p>
                                            <p class="mb-0">
                                                <a href="master_tempat_pkl_add.php" class="alert-link fw-bold">Tambahkan
                                                    tempat PKL baru</a> atau coba filter lainnya!
                                            </p>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </div>

                                <div class="d-md-none">
                                    <?php if ($total_data > 0 && $total_pages > 1) : ?>
                                        <nav aria-label="Page navigation" class="mt-3">
                                            <ul class="pagination justify-content-center">
                                                <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                                    <a class="page-link"
                                                        href="<?= ($page <= 1) ? '#' : '?page=' . ($page - 1) . (!empty($keyword) ? '&keyword=' . htmlspecialchars($keyword) : ''); ?>">Previous</a>
                                                </li>
                                                <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                                                    <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                                                        <a class="page-link"
                                                            href="?page=<?= $i ?><?= !empty($keyword) ? '&keyword=' . htmlspecialchars($keyword) : ''; ?>"><?= $i ?></a>
                                                    </li>
                                                <?php endfor; ?>
                                                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                                    <a class="page-link"
                                                        href="<?= ($page >= $total_pages) ? '#' : '?page=' . ($page + 1) . (!empty($keyword) ? '&keyword=' . htmlspecialchars($keyword) : ''); ?>">Next</a>
                                                </li>
                                            </ul>
                                        </nav>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="layout-overlay layout-menu-toggle"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDeleteTempatPKL(id, namaPerusahaan) {
            Swal.fire({
                title: 'Konfirmasi Hapus Data Tempat PKL',
                html: "Apakah Anda yakin ingin menghapus <strong>" + namaPerusahaan +
                    "</strong>?<br>Tindakan ini tidak dapat dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'master_tempat_pkl_delete.php?id=' + id;
                }
            });
        }
    </script>

    <?php include './partials/script.php'; ?>
</body>

</html>