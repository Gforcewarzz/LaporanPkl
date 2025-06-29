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

// --- Helper function to pass array values by reference ---
// This function needs to be defined BEFORE it's called by bind_param.
if (!function_exists('ref_values')) {
    function ref_values($arr)
    {
        // Check PHP version for reference handling, though newer PHP versions
        // often handle this more gracefully without explicit references for simple arrays.
        // Keeping it for broad compatibility as seen in previous examples.
        if (strnatcmp(PHP_VERSION, '5.3.0') >= 0) { // Using PHP_VERSION constant
            $refs = [];
            foreach ($arr as $key => $value) {
                $refs[$key] = &$arr[$key];
            }
            return $refs;
        }
        return $arr;
    }
}
// --- End Helper Function ---

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// --- Start Pagination Variables ---
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
// --- End Pagination Variables ---

$filter_sql_condition = "";
$params = [];
$types = "";

if (!empty($keyword)) {
    $filter_sql_condition = "WHERE username LIKE ? OR nama_admin LIKE ? OR email LIKE ?";
    $params[] = "%" . $keyword . "%";
    $params[] = "%" . $keyword . "%";
    $params[] = "%" . $keyword . "%";
    $types .= "sss";
}

// Query untuk menghitung total data (tanpa LIMIT dan OFFSET)
$count_query = "SELECT COUNT(id_admin) AS total_data FROM admin $filter_sql_condition";
$stmt_count = $koneksi->prepare($count_query);
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
    error_log("Failed to prepare count statement for admin: " . $koneksi->error);
    $total_data = 0;
}

$total_pages = ceil($total_data / $limit);

// Query untuk mengambil data dengan LIMIT dan OFFSET
$query_data = "SELECT id_admin, username, nama_admin, email FROM admin $filter_sql_condition ORDER BY id_admin ASC LIMIT ? OFFSET ?";
$stmt_data = $koneksi->prepare($query_data);

if ($stmt_data) {
    $params_data = array_merge($params, [$limit, $offset]);
    $types_data = $types . "ii"; // Add 'ii' for integer types of limit and offset

    $bind_params_data = array_merge([$types_data], $params_data);
    call_user_func_array([$stmt_data, 'bind_param'], ref_values($bind_params_data));

    $stmt_data->execute();
    $result_data = $stmt_data->get_result();
    $data_admins = $result_data->fetch_all(MYSQLI_ASSOC);
    $stmt_data->close();
} else {
    error_log("Failed to prepare data statement for admin: " . $koneksi->error);
    $data_admins = [];
}

$koneksi->close(); // Close connection after all queries are done
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

                        <?php
                        if (isset($_SESSION['admin_message'])) {
                            echo '<script>
                                Swal.fire({
                                    icon: "' . htmlspecialchars($_SESSION['admin_message_type']) . '",
                                    title: "' . htmlspecialchars($_SESSION['admin_message_title']) . '",
                                    text: "' . htmlspecialchars($_SESSION['admin_message']) . '",
                                    confirmButtonColor: "#3085d6",
                                    confirmButtonText: "OK"
                                });
                            </script>';
                            unset($_SESSION['admin_message']);
                            unset($_SESSION['admin_message_type']);
                            unset($_SESSION['admin_message_title']);
                        }
                        ?>

                        <div
                            class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom position-relative">
                            <h4 class="fw-bold mb-0 text-primary animate__animated animate__fadeInLeft">
                                <span class="text-muted fw-light">Master /</span> Data Admin
                            </h4>
                            <i class="fas fa-user-shield fa-2x text-info animate__animated animate__fadeInRight"
                                style="opacity: 0.6;"></i>
                        </div>

                        <div class="card bg-gradient-primary-to-secondary text-white mb-4 shadow-lg animate__animated animate__fadeInDown"
                            style="border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                            <div
                                class="card-body p-4 d-flex flex-column flex-sm-row justify-content-between align-items-center">
                                <div class="text-center text-sm-start mb-3 mb-sm-0">
                                    <h5 class="card-title text-white mb-1">Kelola Data Akun Administrator</h5>
                                    <p class="card-text text-white-75 small">Informasi lengkap akun admin sistem.</p>
                                </div>
                                <div class="text-center text-sm-end position-relative">
                                    <div class="rounded-circle bg-white d-flex justify-content-center align-items-center animate__animated animate__zoomIn animate__delay-0-5s"
                                        style="width: 80px; height: 80px; opacity: 0.2; position: relative; z-index: 1;">
                                        <i class="bx bx-user-plus bx-lg text-primary" style="font-size: 3rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4 shadow-lg">
                            <div
                                class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                                <div class="d-flex flex-column flex-md-row gap-2 w-100 w-md-auto order-1">
                                    <a href="master_data_admin_add.php" class="btn btn-primary w-100">
                                        <i class="bx bx-plus me-1"></i> Tambah Admin
                                    </a>
                                </div>

                                <div class="d-flex flex-column flex-md-row gap-2 w-100 w-md-auto order-2 order-md-2">
                                    <a href="generate_admin_pdf.php<?= !empty($keyword) ? '?keyword=' . htmlspecialchars($keyword) : '' ?>"
                                        class="btn btn-outline-danger w-100" target="_blank">
                                        <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
                                    </a>
                                    <a href="generate_admin_excel.php<?= !empty($keyword) ? '?keyword=' . htmlspecialchars($keyword) : '' ?>"
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
                                                placeholder="Cari Admin berdasarkan username, nama, atau email..."
                                                value="<?= htmlspecialchars($keyword) ?>">
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <button type="submit" class="btn btn-outline-dark w-100 w-md-auto">
                                                <i class="bx bx-filter-alt me-1"></i> Filter Admin
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Daftar Lengkap Admin</h5>
                                <small class="text-muted">Informasi detail seluruh admin</small>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive text-nowrap d-none d-md-block"
                                    style="min-height: calc(100vh - 450px); overflow-y: auto;">
                                    <?php if (!empty($data_admins)): ?>
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Username</th>
                                                <th>Nama Admin</th>
                                                <th>Email</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            <?php
                                                $no_table = $offset + 1;
                                                foreach ($data_admins as $row) {
                                                ?>
                                            <tr>
                                                <td><?= $no_table++ ?></td>
                                                <td><strong><?= htmlspecialchars($row['username']) ?></strong></td>
                                                <td><?= htmlspecialchars($row['nama_admin']) ?></td>
                                                <td><?= htmlspecialchars($row['email'] ?? '-') ?></td>
                                                <td>
                                                    <div class='dropdown'>
                                                        <button class='btn p-0 dropdown-toggle hide-arrow'
                                                            data-bs-toggle='dropdown'>
                                                            <i class='bx bx-dots-vertical-rounded'></i>
                                                        </button>
                                                        <div class='dropdown-menu'>
                                                            <a class='dropdown-item'
                                                                href='master_data_admin_edit.php?id=<?= htmlspecialchars($row['id_admin']) ?>'>
                                                                <i class='bx bx-edit-alt me-1'></i> Edit
                                                            </a>
                                                            <a class='dropdown-item text-danger'
                                                                href='javascript:void(0);'
                                                                onclick="confirmDeleteAdmin('<?= htmlspecialchars($row['id_admin']) ?>', '<?= htmlspecialchars($row['nama_admin']) ?>')">
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
                                        <h5 class="alert-heading mb-3"><i class="bx bx-user-plus bx-lg text-info"></i>
                                        </h5>
                                        <p class="mb-3">Tidak ada data admin ditemukan dengan kriteria tersebut.</p>
                                        <p class="mb-0">
                                            <a href="master_data_admin_add.php" class="alert-link fw-bold">Tambahkan
                                                admin baru</a> atau coba filter lainnya!
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
                                    if (!empty($data_admins)) {
                                        foreach ($data_admins as $row) {
                                    ?>
                                    <div
                                        class="card mb-4 shadow-lg border-start border-4 border-primary rounded-3 animate__animated animate__fadeInUp">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="mb-1 text-primary"><i class="bx bx-user me-1"></i>
                                                        <strong><?= htmlspecialchars($row['nama_admin']) ?></strong>
                                                    </h6>
                                                    <span class="badge bg-label-primary"><i
                                                            class="bx bx-id-card me-1"></i>
                                                        Username: <?= htmlspecialchars($row['username']) ?></span>
                                                </div>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                        data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item"
                                                            href="master_data_admin_edit.php?id=<?= htmlspecialchars($row['id_admin']) ?>">
                                                            <i class="bx bx-edit-alt me-1"></i> Edit Data
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                            onclick="confirmDeleteAdmin('<?= htmlspecialchars($row['id_admin']) ?>', '<?= htmlspecialchars($row['nama_admin']) ?>')">
                                                            <i class="bx bx-trash me-1"></i> Hapus
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-2">
                                                <strong class="text-dark"><i class="bx bx-envelope me-1"></i>
                                                    Email:</strong><br>
                                                <?= htmlspecialchars($row['email'] ?? '-') ?>
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
                                        <h5 class="alert-heading mb-3"><i class="bx bx-user-plus bx-lg text-info"></i>
                                        </h5>
                                        <p class="mb-3">Tidak ada data admin ditemukan dengan kriteria tersebut.</p>
                                        <p class="mb-0">
                                            <a href="master_data_admin_add.php" class="alert-link fw-bold">Tambahkan
                                                admin baru</a> atau coba filter lainnya!
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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function confirmDeleteAdmin(id, nama) {
        Swal.fire({
            title: 'Konfirmasi Hapus Data Admin',
            html: `Apakah Anda yakin ingin menghapus akun admin bernama <strong>${nama}</strong>?<br>Tindakan ini tidak dapat dibatalkan!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus Sekarang!',
            cancelButtonText: 'Batal',
            reverseButtons: true // This reverses the button order
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'master_data_admin_delete.php?id=' + id;
            }
        });
    }
    </script>
    <?php include './partials/script.php'; ?>
</body>

</html>