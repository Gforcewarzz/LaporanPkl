<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

include 'partials/db.php';

// --- LOGIKA KEAMANAN HALAMAN ---
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';

if (!$is_admin && !$is_guru) {
    if ($is_siswa) {
        header('Location: dashboard_siswa.php');
    } else {
        header('Location: ../login.php');
    }
    exit();
}

// --- INISIALISASI VARIABEL PAGINASI ---
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// --- INISIALISASI FILTER UNTUK TABEL ---
$keyword = $_GET['keyword'] ?? '';

$base_conditions = [];
$search_conditions = [];
$params_for_bind = [];
$types_for_bind = '';

// Filter Wajib untuk Guru: Hanya bisa melihat siswa bimbingannya
if ($is_guru) {
    $base_conditions[] = 'siswa.pembimbing_id = ?';
    $params_for_bind[] = $_SESSION['id_guru_pendamping'];
    $types_for_bind .= 'i';
}

if (!empty($keyword)) {
    $like_keyword = "%" . $keyword . "%";
    $searchable_columns = ['siswa.nama_siswa', 'siswa.no_induk', 'siswa.nisn', 'siswa.kelas', 'jurusan.nama_jurusan', 'guru_pembimbing.nama_pembimbing', 'tempat_pkl.nama_tempat_pkl', 'siswa.status'];

    $keyword_conditions = [];
    foreach ($searchable_columns as $column) {
        $keyword_conditions[] = "$column LIKE ?";
        $params_for_bind[] = $like_keyword;
        $types_for_bind .= 's';
    }
    $search_conditions[] = "(" . implode(" OR ", $keyword_conditions) . ")";
}

$final_conditions = array_merge($base_conditions, $search_conditions);
$filter_sql = "";
if (!empty($final_conditions)) {
    $filter_sql = "WHERE " . implode(" AND ", $final_conditions);
}

// --- DATA UNTUK DROPDOWN FILTER PDF ---
$list_kelas_pdf = [];
if ($is_admin) {
    $result_kelas = $koneksi->query("SELECT DISTINCT kelas FROM siswa ORDER BY kelas ASC");
    if ($result_kelas) {
        while ($row_kelas = $result_kelas->fetch_assoc()) {
            $list_kelas_pdf[] = $row_kelas['kelas'];
        }
    }
}


// --- FUNGSI UNTUK BINDING PARAMETER DINAMIS ---
function bind_dynamic_params($stmt, $types, $params)
{
    if (!empty($params)) {
        $bind_names = [$types];
        for ($i = 0; $i < count($params); $i++) {
            $bind_names[] = &$params[$i];
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);
    }
}

// Query untuk menghitung total data
$count_query_sql = "SELECT COUNT(siswa.id_siswa) AS total_data FROM siswa LEFT JOIN jurusan ON siswa.jurusan_id = jurusan.id_jurusan LEFT JOIN guru_pembimbing ON siswa.pembimbing_id = guru_pembimbing.id_pembimbing LEFT JOIN tempat_pkl ON siswa.tempat_pkl_id = tempat_pkl.id_tempat_pkl $filter_sql";
$stmt_count = $koneksi->prepare($count_query_sql);
bind_dynamic_params($stmt_count, $types_for_bind, $params_for_bind);
$stmt_count->execute();
$total_data = $stmt_count->get_result()->fetch_assoc()['total_data'];
$total_pages = ceil($total_data / $limit);
$stmt_count->close();

// Query untuk mengambil data dengan LIMIT dan OFFSET
$query_sql = "SELECT siswa.id_siswa, siswa.nama_siswa, siswa.no_induk, siswa.nisn, siswa.jenis_kelamin, siswa.kelas, siswa.status, jurusan.nama_jurusan, guru_pembimbing.nama_pembimbing, tempat_pkl.nama_tempat_pkl FROM siswa LEFT JOIN jurusan ON siswa.jurusan_id = jurusan.id_jurusan LEFT JOIN guru_pembimbing ON siswa.pembimbing_id = guru_pembimbing.id_pembimbing LEFT JOIN tempat_pkl ON siswa.tempat_pkl_id = tempat_pkl.id_tempat_pkl $filter_sql ORDER BY siswa.kelas ASC, siswa.nama_siswa ASC LIMIT ? OFFSET ?";
$stmt_data = $koneksi->prepare($query_sql);

$data_types = $types_for_bind . 'ii';
$data_params = array_merge($params_for_bind, [$limit, $offset]);
bind_dynamic_params($stmt_data, $data_types, $data_params);

$stmt_data->execute();
$result = $stmt_data->get_result();
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

                        <!-- Notifikasi -->
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
                                        confirmButtonColor: '#696cff'
                                    });
                                });
                            </script>";
                            unset($_SESSION['excel_message'], $_SESSION['excel_message_type'], $_SESSION['excel_message_title']);
                        }
                        ?>

                        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                            <h4 class="fw-bold mb-0 text-primary">
                                <span class="text-muted fw-light">Master /</span> Data Siswa
                            </h4>
                            <i class="fas fa-graduation-cap fa-2x text-info" style="opacity: 0.6;"></i>
                        </div>

                        <div class="card mb-4 shadow-lg p-3">
                            <div class="card-body p-0">
                                <!-- Tombol Aksi dan Pencarian -->
                                <div class="row gy-3 mb-4 border-bottom pb-3">
                                    <?php if ($is_admin) : ?>
                                        <div class="col-md-auto">
                                            <a href="master_data_siswa_add.php" class="btn btn-primary w-100">
                                                <i class="bx bx-plus me-1"></i> Tambah Siswa
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <div class="col-md-auto">
                                        <a href="<?= $is_guru ? 'dashboard_guru.php' : 'index.php' ?>"
                                            class="btn btn-outline-secondary w-100">
                                            <i class="bx bx-arrow-back me-1"></i> Kembali
                                        </a>
                                    </div>
                                    <?php if ($is_guru) : ?>
                                        <div class="col-md-auto">
                                            <a href="generate_siswa_pdf.php" target="_blank" class="btn btn-danger w-100">
                                                <i class="bx bxs-file-pdf me-1"></i> Cetak Laporan
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <div class="col-md d-flex justify-content-end align-items-center">
                                        <form method="GET" action="" class="d-flex w-100 justify-content-end">
                                            <div class="input-group" style="max-width: 300px;">
                                                <input type="text" name="keyword" class="form-control"
                                                    placeholder="<?= $is_guru ? 'Cari siswa bimbingan...' : 'Cari semua siswa...' ?>"
                                                    value="<?= htmlspecialchars($keyword) ?>">
                                                <button type="submit" class="btn btn-primary"><i
                                                        class="bx bx-search"></i></button>
                                                <?php if (!empty($keyword)) : ?>
                                                    <a href="master_data_siswa.php" class="btn btn-outline-secondary"><i
                                                            class="bx bx-x"></i></a>
                                                <?php endif; ?>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Form Filter PDF hanya untuk ADMIN -->
                                <?php if ($is_admin) : ?>
                                    <form method="GET" action="generate_siswa_pdf.php" target="_blank"
                                        class="mb-2 p-3 border rounded-3 bg-light">
                                        <h6 class="mb-3 text-info fw-semibold"><i class='bx bxs-file-pdf me-1'></i> Cetak
                                            Laporan Siswa per Kelas</h6>
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-9">
                                                <label for="kelas_pdf_filter" class="form-label">Filter Berdasarkan
                                                    Kelas:</label>
                                                <select id="kelas_pdf_filter" name="kelas_pdf" class="form-select">
                                                    <option value="">Cetak Semua Kelas</option>
                                                    <?php foreach ($list_kelas_pdf as $kelas) : ?>
                                                        <option value="<?= htmlspecialchars($kelas) ?>">
                                                            <?= htmlspecialchars($kelas) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <button type="submit" class="btn btn-danger w-100"><i
                                                        class="bx bxs-file-pdf me-1"></i> Cetak PDF</button>
                                            </div>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Daftar Lengkap Siswa PKL</h5>
                                <small class="text-muted">Total: <?= $total_data ?> siswa
                                    <?= $is_guru ? 'dalam bimbingan Anda' : '' ?></small>
                            </div>
                            <div class="card-body p-0">
                                <!-- Tabel Desktop -->
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
                                                <?php if ($is_admin || $is_guru) : ?><th>Aksi</th><?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            <?php if ($result->num_rows > 0) : $no = $offset + 1;
                                                while ($row = $result->fetch_assoc()) : ?>
                                                    <?php
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
                                                        <?php if ($is_admin || $is_guru) : ?>
                                                            <td>
                                                                <div class='dropdown'>
                                                                    <button class='btn p-0 dropdown-toggle hide-arrow'
                                                                        data-bs-toggle='dropdown'><i
                                                                            class='bx bx-dots-vertical-rounded'></i></button>
                                                                    <div class='dropdown-menu'>
                                                                        <a class='dropdown-item'
                                                                            href='master_data_siswa_edit.php?id=<?= $row['id_siswa'] ?>'><i
                                                                                class='bx bx-edit-alt me-1'></i> Edit</a>
                                                                        <a class='dropdown-item text-danger'
                                                                            href='javascript:void(0);'
                                                                            onclick="confirmDelete('<?= $row['id_siswa'] ?>', '<?= addslashes($row['nama_siswa']) ?>')"><i
                                                                                class='bx bx-trash me-1'></i> Hapus</a>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        <?php endif; ?>
                                                    </tr>
                                                <?php endwhile;
                                            else : ?>
                                                <tr>
                                                    <td colspan='<?= $is_admin || $is_guru ? 10 : 9 ?>' class='text-center'>
                                                        Tidak ada data siswa ditemukan.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Tampilan Mobile -->
                                <div class="d-md-none p-3">
                                    <?php
                                    if ($result) $result->data_seek(0);
                                    if ($result && $result->num_rows > 0) : $no_mobile = $offset + 1;
                                        while ($row_mobile = $result->fetch_assoc()) :
                                    ?>
                                            <div class="card mb-3 shadow-sm border-start border-4 border-primary">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="mb-1">
                                                            <strong><?= $no_mobile++ . '. ' . htmlspecialchars($row_mobile['nama_siswa']) ?></strong>
                                                        </h6>
                                                        <?php if ($is_admin || $is_guru) : ?>
                                                            <div class="dropdown">
                                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                                    data-bs-toggle="dropdown"><i
                                                                        class="bx bx-dots-vertical-rounded"></i></button>
                                                                <div class="dropdown-menu dropdown-menu-end">
                                                                    <a class="dropdown-item"
                                                                        href="master_data_siswa_edit.php?id=<?= $row_mobile['id_siswa'] ?>"><i
                                                                            class="bx bx-edit-alt me-1"></i> Edit</a>
                                                                    <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                                        onclick="confirmDelete('<?= $row_mobile['id_siswa'] ?>', '<?= addslashes($row_mobile['nama_siswa']) ?>')"><i
                                                                            class="bx bx-trash me-1"></i> Hapus</a>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <p class="mb-1"><small><strong>No Induk:</strong>
                                                            <?= htmlspecialchars($row_mobile['no_induk']) ?></small></p>
                                                    <p class="mb-1"><small><strong>Kelas:</strong>
                                                            <?= htmlspecialchars($row_mobile['kelas']) ?> -
                                                            <?= htmlspecialchars($row_mobile['nama_jurusan'] ?? '-') ?></small>
                                                    </p>
                                                    <p class="mb-1"><small><strong>Guru:</strong>
                                                            <?= htmlspecialchars($row_mobile['nama_pembimbing'] ?? '-') ?></small>
                                                    </p>
                                                    <p class="mb-1"><small><strong>Tempat PKL:</strong>
                                                            <?= htmlspecialchars($row_mobile['nama_tempat_pkl'] ?? '-') ?></small>
                                                    </p>
                                                    <p class="mb-0"><small><strong>Status:</strong> <span
                                                                class='badge bg-label-success'><?= htmlspecialchars($row_mobile['status']) ?></span></small>
                                                    </p>
                                                </div>
                                            </div>
                                        <?php endwhile;
                                    else : ?>
                                        <div class="alert alert-info text-center">Tidak ada data siswa ditemukan.</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Paginasi -->
                            <?php if ($total_pages > 1) : ?>
                                <div class="card-footer d-flex justify-content-center">
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination">
                                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>"><a class="page-link"
                                                    href="<?= ($page <= 1) ? '#' : '?page=' . ($page - 1) . (!empty($keyword) ? '&keyword=' . urlencode($keyword) : '') ?>"><i
                                                        class="tf-icon bx bx-chevrons-left"></i></a></li>
                                            <?php
                                            $start_page = max(1, $page - 2);
                                            $end_page = min($total_pages, $page + 2);
                                            if ($start_page > 1) {
                                                echo '<li class="page-item"><a class="page-link" href="?page=1' . (!empty($keyword) ? '&keyword=' . urlencode($keyword) : '') . '">1</a></li>';
                                                if ($start_page > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                            }
                                            for ($i = $start_page; $i <= $end_page; $i++) : ?>
                                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>"><a class="page-link"
                                                        href="?page=<?= $i ?><?= !empty($keyword) ? '&keyword=' . urlencode($keyword) : '' ?>"><?= $i ?></a>
                                                </li>
                                            <?php endfor;
                                            if ($end_page < $total_pages) {
                                                if ($end_page < $total_pages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                                echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . (!empty($keyword) ? '&keyword=' . urlencode($keyword) : '') . '">' . $total_pages . '</a></li>';
                                            }
                                            ?>
                                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>"><a
                                                    class="page-link"
                                                    href="<?= ($page >= $total_pages) ? '#' : '?page=' . ($page + 1) . (!empty($keyword) ? '&keyword=' . urlencode($keyword) : '') ?>"><i
                                                        class="tf-icon bx bx-chevrons-right"></i></a></li>
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
                html: `Anda yakin ingin menghapus siswa <strong>${nama}</strong>?<br>Tindakan ini tidak bisa dibatalkan!`,
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
<?php
// Tutup koneksi setelah semua operasi selesai
$stmt_data->close();
$koneksi->close();
?>