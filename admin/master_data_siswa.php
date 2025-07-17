<?php
session_start();
date_default_timezone_set('Asia/Jakarta'); // Pastikan zona waktu konsisten

// Sertakan file koneksi database. File ini harus menyiapkan objek $koneksi.
include 'partials/db.php';

// --- LOGIKA KEAMANAN HALAMAN ---
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';

// Redirect jika bukan admin DAN juga bukan guru
if (!$is_admin && !$is_guru) {
    if ($is_siswa) {
        header('Location: dashboard_siswa.php');
    } else {
        header('Location: ../login.php');
    }
    exit();
}

// --- INISIALISASI VARIABEL PAGINASI ---
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// --- INISIALISASI FILTER UNTUK TABEL DAN PDF ---
$keyword = $_GET['keyword'] ?? '';
$kelas_filter_pdf = $_GET['kelas_pdf'] ?? ''; // Filter kelas untuk PDF export

$base_conditions = [];
$search_conditions = [];
$params_for_bind = []; // Array untuk menampung nilai parameter yang akan di-bind
$types_for_bind = '';  // String untuk menampung tipe parameter (e.g., 'isss')

// [TAMBAH] Filter Wajib untuk Guru: Hanya bisa melihat siswa bimbingannya
if ($is_guru) {
    $base_conditions[] = 'siswa.pembimbing_id = ?';
    $params_for_bind[] = &$_SESSION['id_guru_pendamping']; // Gunakan referensi untuk variabel sesi
    $types_for_bind .= 'i';
}

// Logika pencarian umum (untuk keyword)
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

    $keyword_params = [];
    $keyword_types = '';
    foreach ($searchable_columns as $column) {
        $search_conditions[] = "$column LIKE ?";
        $keyword_params[] = &$like_keyword; // Gunakan referensi
        $keyword_types .= 's';
    }
    $params_for_bind = array_merge($params_for_bind, $keyword_params);
    $types_for_bind .= $keyword_types;
}

// Gabungkan kondisi dasar (filter guru) dan kondisi pencarian
$final_conditions = $base_conditions;
if (!empty($search_conditions)) {
    $final_conditions[] = "(" . implode(" OR ", $search_conditions) . ")";
}

$filter_sql = "";
if (!empty($final_conditions)) {
    $filter_sql = "WHERE " . implode(" AND ", $final_conditions);
}


// Query untuk menghitung total data
$count_query_sql = "SELECT COUNT(siswa.id_siswa) AS total_data
                    FROM siswa
                    LEFT JOIN jurusan ON siswa.jurusan_id = jurusan.id_jurusan
                    LEFT JOIN guru_pembimbing ON siswa.pembimbing_id = guru_pembimbing.id_pembimbing
                    LEFT JOIN tempat_pkl ON siswa.tempat_pkl_id = tempat_pkl.id_tempat_pkl
                    $filter_sql";

$stmt_count = $koneksi->prepare($count_query_sql);
if ($stmt_count === false) {
    die("Error preparing count query: " . $koneksi->error);
}

if (!empty($params_for_bind)) {
    // Buat array referensi untuk bind_param
    $bind_args_count = [];
    $bind_args_count[] = $types_for_bind; // Parameter pertama adalah string tipe
    foreach ($params_for_bind as &$param) { // Loop menggunakan referensi ($param)
        $bind_args_count[] = &$param; // Tambahkan referensi ke argumen
    }
    call_user_func_array([$stmt_count, 'bind_param'], $bind_args_count);
}
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$total_data = $count_result->fetch_assoc()['total_data'];
$total_pages = ceil($total_data / $limit);
$stmt_count->close();


// Query untuk mengambil data dengan LIMIT dan OFFSET
$query_sql = "SELECT
                siswa.id_siswa, siswa.nama_siswa, siswa.no_induk, siswa.nisn, 
                siswa.jenis_kelamin, siswa.kelas, siswa.status,
                jurusan.nama_jurusan, guru_pembimbing.nama_pembimbing,
                tempat_pkl.nama_tempat_pkl
            FROM siswa
            LEFT JOIN jurusan ON siswa.jurusan_id = jurusan.id_jurusan
            LEFT JOIN guru_pembimbing ON siswa.pembimbing_id = guru_pembimbing.id_pembimbing
            LEFT JOIN tempat_pkl ON siswa.tempat_pkl_id = tempat_pkl.id_tempat_pkl
            $filter_sql
            ORDER BY siswa.kelas ASC, siswa.nama_siswa ASC
            LIMIT ? OFFSET ?";

$stmt_data = $koneksi->prepare($query_sql);
if ($stmt_data === false) {
    die("Error preparing data query: " . $koneksi->error);
}

// Tambahkan tipe data dan nilai untuk LIMIT dan OFFSET
$final_types_data_query = $types_for_bind . 'ii';
$final_params_data_query = array_merge($params_for_bind, [&$limit, &$offset]); // Gunakan referensi

// Perbaikan bind_param untuk query utama
$bind_args_data_query = [];
$bind_args_data_query[] = $final_types_data_query; // Parameter pertama adalah string tipe
foreach ($final_params_data_query as &$param) { // Loop menggunakan referensi ($param)
    $bind_args_data_query[] = &$param; // Tambahkan referensi ke argumen
}
call_user_func_array([$stmt_data, 'bind_param'], $bind_args_data_query);

$stmt_data->execute();
$result = $stmt_data->get_result();
$stmt_data->close(); // Tutup statement untuk fetching data

// --- Ambil daftar kelas untuk dropdown filter PDF menggunakan $koneksi yang sudah ada ---
$query_kelas = "SELECT DISTINCT kelas FROM siswa ORDER BY kelas ASC";
$result_kelas = $koneksi->query($query_kelas); // Gunakan $koneksi yang sudah ada
$list_kelas = [];
if ($result_kelas) {
    while ($row_kelas = $result_kelas->fetch_assoc()) {
        $list_kelas[] = $row_kelas['kelas'];
    }
    $result_kelas->free(); // Bebaskan hasil query
}

$koneksi->close(); // Tutup koneksi utama setelah SEMUA operasi database selesai
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
                        // (Tidak ada perubahan di blok notifikasi ini)
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
                                <div class="row gy-3 mb-4 border-bottom pb-3">
                                    <?php if ($is_admin): ?>
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

                                    <div class="col-md d-flex justify-content-end align-items-center">
                                        <form method="GET" action="" class="d-flex w-100 justify-content-end">
                                            <div class="input-group" style="max-width: 300px;">
                                                <input type="text" name="keyword" class="form-control"
                                                    placeholder="Cari Siswa..."
                                                    value="<?= htmlspecialchars($keyword) ?>">
                                                <button type="submit" class="btn btn-primary"><i
                                                        class="bx bx-search"></i></button>
                                                <?php if (!empty($keyword)): ?>
                                                <a href="master_data_siswa.php" class="btn btn-outline-secondary"><i
                                                        class="bx bx-x"></i></a>
                                                <?php endif; ?>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <form method="GET" action="generate_siswa_pdf.php" target="_blank"
                                    class="row g-2 align-items-end mb-2 p-3 border rounded-3 bg-light">
                                    <h6 class="col-12 mb-3 text-info fw-semibold"><i class='bx bxs-file-pdf me-1'></i>
                                        Cetak Laporan Siswa (Filter PDF)</h6>

                                    <div class="col-12 col-md-6 col-lg-4"> <label for="kelasFilterPdf"
                                            class="form-label mb-1 visually-hidden">Filter Kelas:</label> <select
                                            id="kelasFilterPdf" name="kelas_pdf" class="form-select">
                                            <option value="">Semua Kelas</option>
                                            <?php foreach ($list_kelas as $kelas): ?>
                                            <option value="<?= htmlspecialchars($kelas) ?>"
                                                <?= $kelas_filter_pdf == $kelas ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($kelas) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6 col-lg-3 d-flex align-items-end"> <?php
                                                                                                    // Pastikan parameter guru_id (jika guru yang login) selalu terkirim ke PDF
                                                                                                    if ($is_guru):
                                                                                                    ?>
                                        <input type="hidden" name="pembimbing_id"
                                            value="<?= htmlspecialchars($_SESSION['id_guru_pendamping']) ?>">
                                        <?php
                                                                                                    // Jika admin melihat siswa bimbingan guru tertentu, teruskan ID guru tersebut
                                                                                                    elseif ($is_admin && isset($_GET['pembimbing_id']) && !empty($_GET['pembimbing_id'])):
                                        ?>
                                        <input type="hidden" name="pembimbing_id"
                                            value="<?= htmlspecialchars($_GET['pembimbing_id']) ?>">
                                        <?php endif; ?>

                                        <button type="submit" class="btn btn-danger w-100">
                                            <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
                                        </button>
                                    </div>
                                </form>

                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Daftar Lengkap Siswa PKL</h5>
                                <small class="text-muted">Total: <?= $total_data ?> siswa
                                    <?= $is_guru ? 'dalam bimbingan Anda' : '' ?></small>
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
                                                <?php if ($is_admin || $is_guru): ?><th>Aksi</th><?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            <?php if ($result->num_rows > 0): $no = $offset + 1;
                                                while ($row = $result->fetch_assoc()): ?>
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

                                                <?php if ($is_admin || $is_guru): ?>
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
                                            else: ?>
                                            <tr>
                                                <td colspan='<?= $is_admin || $is_guru ? 10 : 9 ?>' class='text-center'>
                                                    Tidak ada data siswa ditemukan.</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-md-none p-3">
                                    <?php
                                    if ($result) $result->data_seek(0);
                                    if ($result && $result->num_rows > 0): $no_mobile = $offset + 1;
                                        while ($row_mobile = $result->fetch_assoc()):
                                    ?>
                                    <div class="card mb-3 shadow-sm border-start border-4 border-primary">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-1">
                                                    <strong><?= $no_mobile++ . '. ' . htmlspecialchars($row_mobile['nama_siswa']) ?></strong>
                                                </h6>

                                                <?php if ($is_admin || $is_guru): ?>
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
                                    else: ?>
                                    <div class="alert alert-info text-center">Tidak ada data siswa ditemukan.</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($total_pages > 1) : ?>
                            <div class="card-footer d-flex justify-content-center">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination">
                                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                            <a class="page-link"
                                                href="<?= ($page <= 1) ? '#' : '?page=' . ($page - 1) . (!empty($keyword) ? '&keyword=' . urlencode($keyword) : '') ?>"><i
                                                    class="tf-icon bx bx-chevrons-left"></i></a>
                                        </li>
                                        <?php
                                            $start_page = max(1, $page - 2);
                                            $end_page = min($total_pages, $page + 2);

                                            if ($start_page > 1) {
                                                echo '<li class="page-item"><a class="page-link" href="?page=1' . (!empty($keyword) ? '&keyword=' . urlencode($keyword) : '') . '">1</a></li>';
                                                if ($start_page > 2) {
                                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                                }
                                            }

                                            for ($i = $start_page; $i <= $end_page; $i++) : ?>
                                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                            <a class="page-link"
                                                href="?page=<?= $i ?><?= !empty($keyword) ? '&keyword=' . urlencode($keyword) : '' ?>"><?= $i ?></a>
                                        </li>
                                        <?php endfor;

                                            if ($end_page < $total_pages) {
                                                if ($end_page < $total_pages - 1) {
                                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                                }
                                                echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . (!empty($keyword) ? '&keyword=' . urlencode($keyword) : '') . '">' . $total_pages . '</a></li>';
                                            }
                                            ?>
                                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                            <a class="page-link"
                                                href="<?= ($page >= $total_pages) ? '#' : '?page=' . ($page + 1) . (!empty($keyword) ? '&keyword=' . urlencode($keyword) : '') ?>"><i
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
    <script>
    function confirmDelete(id, nama) {
        Swal.fire({
            title: 'Confirm Deletion',
            html: `Are you sure you want to delete student <strong>${nama}</strong>?<br>This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Delete!',
            cancelButtonText: 'Cancel',
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