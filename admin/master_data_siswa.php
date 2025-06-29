<?php

session_start();

// Keamanan: Hanya admin yang boleh mengakses halaman ini
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_admin) {
    if ($is_siswa) {
        header('Location: dashboard_siswa.php');
        exit();
    } elseif ($is_guru) {
        header('Location: ../halaman_guru.php');
        exit();
    } else {
        header('Location: ../login.php');
        exit();
    }
}

include 'partials/db.php';

// Pagination Variables
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter & Query Logic
$keyword = $_GET['keyword'] ?? '';
$conditions = [];
$params = [];
$types = '';

if (!empty($keyword)) {
    $like_keyword = "%" . $keyword . "%";
    $searchable_columns = [
        'siswa.nama_siswa',
        'siswa.no_induk',
        'siswa.nisn',
        'siswa.kelas',
        'jurusan.nama_jurusan',
        'guru_pembimbing.nama_pembimbing',
        'tempat_pkl.nama_tempat_pkl',
        'siswa.status'
    ];

    foreach ($searchable_columns as $column) {
        $conditions[] = "$column LIKE ?";
        $params[] = $like_keyword;
        $types .= 's';
    }
}

$filter_sql = "";
if (!empty($conditions)) {
    $filter_sql = "WHERE " . implode(" OR ", $conditions);
}

// Query untuk menghitung total data
$count_query_sql = "SELECT COUNT(siswa.id_siswa) AS total_data
                    FROM siswa
                    LEFT JOIN jurusan ON siswa.jurusan_id = jurusan.id_jurusan
                    LEFT JOIN guru_pembimbing ON siswa.pembimbing_id = guru_pembimbing.id_pembimbing
                    LEFT JOIN tempat_pkl ON siswa.tempat_pkl_id = tempat_pkl.id_tempat_pkl
                    $filter_sql";

$stmt_count = $koneksi->prepare($count_query_sql);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$total_data = $count_result->fetch_assoc()['total_data'];
$total_pages = ceil($total_data / $limit);
$stmt_count->close();


// Query untuk mengambil data dengan LIMIT dan OFFSET
$query_sql = "SELECT
                siswa.id_siswa,
                siswa.nama_siswa,
                siswa.no_induk,
                siswa.nisn, 
                siswa.jenis_kelamin, /* Menambahkan jenis_kelamin */
                siswa.kelas,
                siswa.status,
                jurusan.nama_jurusan,
                guru_pembimbing.nama_pembimbing,
                tempat_pkl.nama_tempat_pkl
            FROM siswa
            LEFT JOIN jurusan ON siswa.jurusan_id = jurusan.id_jurusan
            LEFT JOIN guru_pembimbing ON siswa.pembimbing_id = guru_pembimbing.id_pembimbing
            LEFT JOIN tempat_pkl ON siswa.tempat_pkl_id = tempat_pkl.id_tempat_pkl
            $filter_sql
            ORDER BY siswa.nama_siswa ASC
            LIMIT ? OFFSET ?";

$stmt_data = $koneksi->prepare($query_sql);
$data_types = $types . 'ii'; // 'i' for integer (limit, offset)
$data_params = array_merge($params, [$limit, $offset]);

if (!empty($params)) {
    $stmt_data->bind_param($data_types, ...$data_params);
} else {
    // If no keyword, types for limit and offset only are 'ii'
    // $stmt_data->bind_param('ii', $limit, $offset); // This was commented out, causing issues.
    // The previous logic was if(!empty($params)), it runs bind_param... but if params IS empty, it goes to else.
    // The query template still has LIMIT ? OFFSET ? which needs binding.
    $bind_success = $stmt_data->bind_param('ii', $limit, $offset);
    if (!$bind_success) {
        error_log("Failed to bind parameters for data query (no keyword): " . $stmt_data->error);
        // Handle this error appropriately
    }
}
$stmt_data->execute();
$result = $stmt_data->get_result();

$koneksi->close(); // Tutup koneksi setelah semua data diambil

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
                        if (isset($_SESSION['excel_message'])) {
                            $alert_icon = $_SESSION['excel_message_type'];
                            $alert_title = $_SESSION['excel_message_title'];
                            $alert_text = $_SESSION['excel_message'];
                            echo "<script>
                                document.addEventListener('DOMContentLoaded', function() {
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
                                });
                            </script>";
                            unset($_SESSION['excel_message']);
                            unset($_SESSION['excel_message_type']);
                            unset($_SESSION['excel_message_title']);
                        }
                        ?>

                        <div
                            class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom position-relative">
                            <h4 class="fw-bold mb-0 text-primary animate__animated animate__fadeInLeft">
                                <span class="text-muted fw-light">Master /</span> Data Siswa
                            </h4>
                            <i class="fas fa-graduation-cap fa-2x text-info animate__animated animate__fadeInRight"
                                style="opacity: 0.6;"></i>
                        </div>

                        <div class="card mb-4 shadow-lg">
                            <div class="card-body p-3">
                                <div class="row gy-3">

                                    <div class="col-md-auto">
                                        <a href="master_data_siswa_add.php" class="btn btn-primary w-100">
                                            <i class="bx bx-plus me-1"></i> Tambah Siswa
                                        </a>
                                    </div>
                                    <div class="col-md-auto">
                                        <a href="generate_siswa_pdf.php<?= !empty($keyword) ? '?keyword=' . urlencode($keyword) : '' ?>"
                                            class="btn btn-outline-danger w-100" target="_blank">
                                            <i class="bx bxs-file-pdf me-1"></i> PDF
                                        </a>
                                    </div>
                                    <div class="col-md-auto">
                                        <a href="generate_siswa_excel.php<?= !empty($keyword) ? '?keyword=' . urlencode($keyword) : '' ?>"
                                            class="btn btn-outline-success w-100" target="_blank">
                                            <i class="bx bxs-file-excel me-1"></i> Excel
                                        </a>
                                    </div>
                                    <div class="col-md-auto">
                                        <a href="index.php" class="btn btn-outline-secondary w-100">
                                            <i class="bx bx-arrow-back me-1"></i> Kembali
                                        </a>
                                    </div>
                                    <div class="col-md">
                                        <form method="GET" action="" class="d-flex">
                                            <input type="text" name="keyword" class="form-control"
                                                placeholder="Cari Siswa..." value="<?= htmlspecialchars($keyword) ?>">
                                            <button type="submit" class="btn btn-primary ms-2">
                                                <i class="bx bx-search"></i>
                                            </button>
                                            <?php if (!empty($keyword)): ?>
                                                <a href="master_data_siswa.php" class="btn btn-outline-secondary ms-2">
                                                    <i class="bx bx-x"></i>
                                                </a>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Daftar Lengkap Siswa PKL</h5>
                                <small class="text-muted">Total: <?= $total_data ?> siswa</small>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive text-nowrap d-none d-md-block"
                                    style="min-height: calc(100vh - 450px); overflow-y: auto;">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama</th>
                                                <th>No Induk</th>
                                                <th>NISN</th>
                                                <th>Kelas</th>
                                                <th>Jurusan</th>
                                                <th>Guru</th>
                                                <th>Tempat PKL</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            <?php
                                            if ($result->num_rows > 0) {
                                                $no = $offset + 1;
                                                while ($row = $result->fetch_assoc()) {
                                                    $badgeColor = match ($row['status']) {
                                                        'Tidak Aktif' => 'bg-label-warning',
                                                        'Selesai' => 'bg-label-info',
                                                        default => 'bg-label-success',
                                                    };
                                            ?>
                                                    <tr>
                                                        <td><?= $no++ ?></td>
                                                        <td><strong><?= htmlspecialchars($row['nama_siswa']) ?></strong></td>
                                                        <td><?= htmlspecialchars($row['no_induk']) ?></td>
                                                        <td><?= htmlspecialchars($row['nisn'] ?? '-') ?></td>
                                                        <td><?= htmlspecialchars($row['kelas']) ?></td>
                                                        <td><?= htmlspecialchars($row['nama_jurusan'] ?? '-') ?></td>
                                                        <td><?= htmlspecialchars($row['nama_pembimbing'] ?? '-') ?></td>
                                                        <td><?= htmlspecialchars($row['nama_tempat_pkl'] ?? '-') ?></td>
                                                        <td><span
                                                                class='badge <?= $badgeColor ?>'><?= htmlspecialchars($row['status']) ?></span>
                                                        </td>
                                                        <td>
                                                            <div class='dropdown'>
                                                                <button class='btn p-0 dropdown-toggle hide-arrow'
                                                                    data-bs-toggle='dropdown'>
                                                                    <i class='bx bx-dots-vertical-rounded'></i>
                                                                </button>
                                                                <div class='dropdown-menu'>
                                                                    <a class='dropdown-item'
                                                                        href='master_data_siswa_edit.php?id=<?= htmlspecialchars($row['id_siswa']) ?>'>
                                                                        <i class='bx bx-edit-alt me-1'></i> Edit
                                                                    </a>
                                                                    <a class='dropdown-item text-danger'
                                                                        href='javascript:void(0);'
                                                                        onclick="confirmDelete('<?= htmlspecialchars($row['id_siswa']) ?>', '<?= htmlspecialchars(addslashes($row['nama_siswa'])) ?>')">
                                                                        <i class='bx bx-trash me-1'></i> Hapus
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php }
                                            } else { ?>
                                                <tr>
                                                    <td colspan='10' class='text-center'>Tidak ada data siswa ditemukan.
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-md-none p-3">
                                    <?php
                                    // Reset result pointer untuk tampilan mobile
                                    if ($result) {
                                        $result->data_seek(0);
                                    }

                                    if ($result && $result->num_rows > 0) {
                                        $colors = ['primary', 'warning', 'info', 'success', 'danger'];
                                        $color_index = 0;
                                        while ($row_mobile = $result->fetch_assoc()) {
                                            $current_color = $colors[$color_index % count($colors)];
                                            $color_index++;
                                            $badgeColorMobile = match ($row_mobile['status']) {
                                                'Tidak Aktif' => 'bg-label-warning',
                                                'Selesai' => 'bg-label-info',
                                                default => 'bg-label-success',
                                            };
                                    ?>
                                            <div
                                                class="card mb-4 shadow-lg border-start border-4 border-<?= $current_color ?> rounded-3 animate__animated animate__fadeInUp">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                                        <div>
                                                            <h6 class="mb-1 text-primary"><i class="bx bx-user me-1"></i>
                                                                <strong><?= htmlspecialchars($row_mobile['nama_siswa']) ?></strong>
                                                            </h6>
                                                            <span class="badge bg-label-primary"><i class="bx bx-hash me-1"></i>
                                                                No Induk:
                                                                <?= htmlspecialchars($row_mobile['no_induk']) ?></span>
                                                        </div>
                                                        <div class="dropdown">
                                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                                data-bs-toggle="dropdown">
                                                                <i class="bx bx-dots-vertical-rounded"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <a class="dropdown-item"
                                                                    href="master_data_siswa_edit.php?id=<?= htmlspecialchars($row_mobile['id_siswa']) ?>">
                                                                    <i class="bx bx-edit-alt me-1"></i> Edit Data
                                                                </a>
                                                                <div class="dropdown-divider"></div>
                                                                <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                                    onclick="confirmDelete('<?= htmlspecialchars($row_mobile['id_siswa']) ?>', '<?= htmlspecialchars(addslashes($row_mobile['nama_siswa'])) ?>')">
                                                                    <i class="bx bx-trash me-1"></i> Hapus
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="mb-2">
                                                        <strong class="text-dark"><i class="bx bx-id-card me-1"></i>
                                                            NISN:</strong>
                                                        <?= htmlspecialchars($row_mobile['nisn'] ?? '-') ?>
                                                    </div>
                                                    <div class="mb-2">
                                                        <strong class="text-dark"><i class="bx bx-info-circle me-1"></i>
                                                            Gender:</strong>
                                                        <?= htmlspecialchars($row_mobile['jenis_kelamin'] ?? '-') ?>
                                                    </div>
                                                    <div class="mb-2">
                                                        <strong class="text-dark"><i class="bx bx-award me-1"></i>
                                                            Kelas:</strong>
                                                        <?= htmlspecialchars($row_mobile['kelas']) ?>
                                                    </div>
                                                    <div class="mb-2">
                                                        <strong class="text-dark"><i class="bx bx-book-open me-1"></i>
                                                            Jurusan:</strong>
                                                        <?= htmlspecialchars($row_mobile['nama_jurusan'] ?? '-') ?>
                                                    </div>
                                                    <div class="mb-2">
                                                        <strong class="text-dark"><i class="bx bx-user-voice me-1"></i> Guru
                                                            Pendamping:</strong>
                                                        <?= htmlspecialchars($row_mobile['nama_pembimbing'] ?? '-') ?>
                                                    </div>
                                                    <div class="mb-2">
                                                        <strong class="text-dark"><i class="bx bx-building-house me-1"></i>
                                                            Tempat PKL:</strong>
                                                        <?= htmlspecialchars($row_mobile['nama_tempat_pkl'] ?? '-') ?>
                                                    </div>
                                                    <div class="d-flex justify-content-end align-items-baseline mt-3">
                                                        <small class="text-muted"><i class="bx bx-calendar-check me-1"></i>
                                                            Status: <span
                                                                class='badge <?= $badgeColorMobile ?>'><?= htmlspecialchars($row_mobile['status']) ?></span></small>
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
                                            <p class="mb-3">Tidak ada data siswa ditemukan dengan kriteria tersebut.</p>
                                            <p class="mb-0">
                                                <a href="master_data_siswa_add.php" class="alert-link fw-bold">Tambahkan
                                                    siswa baru</a> atau coba filter lainnya!
                                            </p>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php if ($total_pages > 1) : ?>
                                <div class="card-footer d-flex justify-content-center">
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination">
                                            <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                                <a class="page-link"
                                                    href="<?= ($page <= 1) ? '#' : '?page=' . ($page - 1) . (!empty($keyword) ? '&keyword=' . urlencode($keyword) : ''); ?>">
                                                    <i class="tf-icon bx bx-chevrons-left"></i>
                                                </a>
                                            </li>
                                            <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                                                <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                                                    <a class="page-link"
                                                        href="?page=<?= $i ?><?= !empty($keyword) ? '&keyword=' . urlencode($keyword) : ''; ?>"><?= $i ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                                <a class="page-link"
                                                    href="<?= ($page >= $total_pages) ? '#' : '?page=' . ($page + 1) . (!empty($keyword) ? '&keyword=' . urlencode($keyword) : ''); ?>">
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(id, nama) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                html: `Apakah Anda yakin ingin menghapus data siswa bernama <strong>${nama}</strong>?<br>Tindakan ini tidak dapat dibatalkan!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'master_data_siswa_delete.php?id=' + id;
                }
            });
        }
    </script>
    <?php include './partials/script.php'; ?>
</body>

</html>