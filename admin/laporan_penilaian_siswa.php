<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

include 'partials/db.php';

// --- LOGIKA KEAMANAN HALAMAN ---
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_admin && !$is_guru) {
    header('Location: ../login.php');
    exit();
}

// Cek dan tampilkan notifikasi dari session (misalnya setelah hapus nilai)
if (isset($_SESSION['pesan_notifikasi'])) {
    $notif = $_SESSION['pesan_notifikasi'];
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '{$notif['tipe']}',
                title: '{$notif['judul']}',
                text: '{$notif['pesan']}',
                confirmButtonColor: '#696cff'
            });
        });
    </script>
    ";
    unset($_SESSION['pesan_notifikasi']);
}


// --- INISIALISASI FILTER ---
$keyword = $_GET['keyword'] ?? '';
$kelas_filter = $_GET['kelas'] ?? '';

$base_conditions = [];
$params_for_bind = [];
$types_for_bind = '';

$base_conditions[] = 'siswa.id_siswa IN (SELECT DISTINCT siswa_id FROM nilai_siswa)';

if ($is_guru) {
    $base_conditions[] = 'siswa.pembimbing_id = ?';
    $params_for_bind[] = $_SESSION['id_guru_pendamping'];
    $types_for_bind .= 'i';
}

if ($is_admin && !empty($kelas_filter)) {
    $base_conditions[] = 'siswa.kelas = ?';
    $params_for_bind[] = $kelas_filter;
    $types_for_bind .= 's';
}

if (!empty($keyword)) {
    $like_keyword = "%" . $keyword . "%";
    $base_conditions[] = '(siswa.nama_siswa LIKE ? OR siswa.nisn LIKE ? OR siswa.kelas LIKE ?)';
    $params_for_bind[] = $like_keyword;
    $params_for_bind[] = $like_keyword;
    $params_for_bind[] = $like_keyword;
    $types_for_bind .= 'sss';
}

$filter_sql = "";
if (!empty($base_conditions)) {
    $filter_sql = "WHERE " . implode(" AND ", $base_conditions);
}

// Query untuk mengambil data siswa yang sudah dinilai
$query_sql = "SELECT siswa.id_siswa, siswa.nama_siswa, siswa.nisn, siswa.kelas
              FROM siswa
              $filter_sql
              ORDER BY siswa.kelas ASC, siswa.nama_siswa ASC";

$stmt_data = $koneksi->prepare($query_sql);

if ($stmt_data === false) {
    die("Error preparing data query: " . $koneksi->error);
}

if (!empty($params_for_bind)) {
    $stmt_data->bind_param($types_for_bind, ...$params_for_bind);
}

$stmt_data->execute();
$result = $stmt_data->get_result();
$total_data = $result->num_rows;

$list_kelas = [];
if ($is_admin) {
    $query_kelas = "SELECT DISTINCT s.kelas FROM siswa s JOIN nilai_siswa ns ON s.id_siswa = ns.siswa_id ORDER BY s.kelas ASC";
    $result_kelas = $koneksi->query($query_kelas);
    if ($result_kelas) {
        while ($row_kelas = $result_kelas->fetch_assoc()) {
            $list_kelas[] = $row_kelas['kelas'];
        }
    }
}

$koneksi->close();
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/" data-template="vertical-menu-template-free">

<?php include 'partials/head.php'; ?>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include './partials/sidebar.php'; ?>
            <div class="layout-page">
                <?php include './partials/navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                            <h4 class="fw-bold mb-0 text-primary">
                                <span class="text-muted fw-light">Laporan /</span> Penilaian Siswa
                            </h4>
                            <i class="fas fa-file-signature fa-2x text-info" style="opacity: 0.6;"></i>
                        </div>

                        <div class="card mb-4 shadow-lg p-3">
                            <div class="card-body">
                                <div class="mb-4">
                                    <a href="form_penilaian.php" class="btn btn-primary">
                                        <i class="bx bx-edit me-1"></i> Input Nilai Siswa
                                    </a>
                                </div>
                                <form method="GET" action="">
                                    <div class="row g-3 align-items-end">
                                        <?php if ($is_admin): ?>
                                        <div class="col-md-4">
                                            <label for="kelas_filter" class="form-label">Filter Kelas:</label>
                                            <select id="kelas_filter" name="kelas" class="form-select">
                                                <option value="">Semua Kelas</option>
                                                <?php foreach ($list_kelas as $kelas): ?>
                                                <option value="<?= htmlspecialchars($kelas) ?>" <?= ($kelas_filter == $kelas) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($kelas) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <?php endif; ?>
                                        <div class="col-md-5">
                                            <label for="keyword" class="form-label">Cari Siswa/NISN/Kelas:</label>
                                            <input type="text" id="keyword" name="keyword" class="form-control" placeholder="Masukkan kata kunci..." value="<?= htmlspecialchars($keyword) ?>">
                                        </div>
                                        <div class="col-md-3 d-flex">
                                            <button type="submit" class="btn btn-primary me-2 w-100"><i class="bx bx-search me-1"></i> Cari</button>
                                            <a href="<?= basename($_SERVER['PHP_SELF']) ?>" class="btn btn-outline-secondary"><i class="bx bx-reset"></i></a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Daftar Siswa Sudah Dinilai</h5>
                                <small class="text-muted">Total: <?= $total_data ?> siswa ditemukan</small>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive text-nowrap d-none d-md-block">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Siswa</th>
                                                <th>NISN</th>
                                                <th>Kelas</th>
                                                <th class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            <?php if ($result && $result->num_rows > 0): ?>
                                                <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><strong><?= htmlspecialchars($row['nama_siswa']) ?></strong></td>
                                                    <td><?= htmlspecialchars($row['nisn']) ?></td>
                                                    <td><?= htmlspecialchars($row['kelas']) ?></td>
                                                    <td class="text-center">
                                                        <div class="btn-group">
                                                            <a href="laporan_tabel_lengkap.php?siswa_id=<?= $row['id_siswa'] ?>" class="btn btn-sm btn-info">
                                                                <i class="bx bx-show me-1"></i> Detail
                                                            </a>
                                                            <button type="button" class="btn btn-info dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <span class="visually-hidden">Toggle Dropdown</span>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li><a class="dropdown-item" href="form_edit_nilai.php?siswa_id=<?= $row['id_siswa'] ?>"><i class="bx bx-edit-alt me-1"></i> Edit Nilai</a></li>
                                                                <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="confirmDelete('<?= $row['id_siswa'] ?>', '<?= addslashes($row['nama_siswa']) ?>')"><i class="bx bx-trash me-1"></i> Hapus Nilai</a></li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class='text-center'>Tidak ada data siswa yang cocok dengan filter.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-md-none p-3">
                                <?php
                                if ($result) $result->data_seek(0);
                                if ($result && $result->num_rows > 0):
                                    $no_mobile = 1;
                                    while ($row_mobile = $result->fetch_assoc()):
                                ?>
                                    <div class="card mb-3 shadow-sm border-start border-4 border-info">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-1"><strong><?= $no_mobile++ . '. ' . htmlspecialchars($row_mobile['nama_siswa']) ?></strong></h6>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="form_edit_nilai.php?siswa_id=<?= $row_mobile['id_siswa'] ?>"><i class="bx bx-edit-alt me-1"></i> Edit Nilai</a>
                                                        <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="confirmDelete('<?= $row_mobile['id_siswa'] ?>', '<?= addslashes($row_mobile['nama_siswa']) ?>')"><i class="bx bx-trash me-1"></i> Hapus Nilai</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="mb-1"><small><strong>NISN:</strong> <?= htmlspecialchars($row_mobile['nisn']) ?></small></p>
                                            <p class="mb-2"><small><strong>Kelas:</strong> <?= htmlspecialchars($row_mobile['kelas']) ?></small></p>
                                            <a href="laporan_tabel_lengkap.php?siswa_id=<?= $row_mobile['id_siswa'] ?>" class="btn btn-sm btn-info w-100">
                                                <i class="bx bx-show me-1"></i> Lihat Detail Nilai
                                            </a>
                                        </div>
                                    </div>
                                <?php 
                                    endwhile;
                                else: 
                                ?>
                                    <div class="alert alert-info text-center">Tidak ada data siswa ditemukan.</div>
                                <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    </div>
                    <?php include './partials/footer.php'; ?>
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    
    <?php include './partials/script.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function confirmDelete(id, nama) {
        Swal.fire({
            title: 'Anda Yakin?',
            html: `Semua data nilai untuk siswa <strong>${nama}</strong> akan dihapus. <br>Aksi ini tidak dapat dibatalkan!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus Nilai!',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                // Arahkan ke skrip hapus nilai
                window.location.href = 'hapus_nilai_siswa.php?siswa_id=' + id;
            }
        });
    }
    </script>
</body>
</html>