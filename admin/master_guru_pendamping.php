<?php

session_start();

// Keamanan: Hanya admin yang boleh mengakses halaman ini
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_admin) {
    if ($is_siswa) {
        header('Location: dashboard_siswa.php'); // Redirect siswa ke dashboard siswa
        exit();
    } elseif ($is_guru) {
        header('Location: ../../halaman_guru.php'); // Redirect guru ke halaman guru
        exit();
    } else {
        header('Location: ../login.php'); // Jika tidak login sama sekali, redirect ke halaman login
        exit();
    }
}

include 'partials/db.php';

// Pagination Variables
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Query untuk menghitung total data
$count_query = "SELECT COUNT(id_pembimbing) AS total_data FROM guru_pembimbing";
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : ''; // Keyword untuk count query

if (!empty($keyword)) {
    $keyword_safe = mysqli_real_escape_string($koneksi, $keyword);
    $count_query .= " WHERE nama_pembimbing LIKE '%$keyword_safe%' OR nip LIKE '%$keyword_safe%'";
}
$count_result = mysqli_query($koneksi, $count_query);
$total_data = mysqli_fetch_assoc($count_result)['total_data'];
$total_pages = ceil($total_data / $limit);


// Query untuk mengambil data dengan LIMIT dan OFFSET
// PERUBAHAN: Menambahkan kolom 'jenis_kelamin' di SELECT
$sql = "SELECT id_pembimbing, nama_pembimbing, nip, jenis_kelamin FROM guru_pembimbing";
if (!empty($keyword)) {
    $keyword_safe = mysqli_real_escape_string($koneksi, $keyword);
    $sql .= " WHERE nama_pembimbing LIKE '%$keyword_safe%' OR nip LIKE '%$keyword_safe%'";
}
$sql .= " ORDER BY nama_pembimbing ASC LIMIT $limit OFFSET $offset";

$result = mysqli_query($koneksi, $sql);

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

                        <?php
                        // Tampilkan SweetAlert jika ada pesan dari sesi (dari generate_guru_excel.php atau lainnya)
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

                        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                            <h4 class="fw-bold mb-0 text-primary">
                                <span class="text-muted fw-light">Master /</span> Data Guru Pendamping
                            </h4>
                            <i class="fas fa-chalkboard-teacher fa-2x text-info" style="opacity: 0.6;"></i>
                        </div>

                        <div class="card mb-4 shadow-lg">
                            <div
                                class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                                <div class="d-flex flex-column flex-md-row gap-2 w-100 w-md-auto order-2 order-md-1">
                                    <a href="master_guru_pendamping_add.php" class="btn btn-primary w-100">
                                        <i class="bx bx-plus me-1"></i> Tambah Guru
                                    </a>
                                    <a href="generate_guru_pendamping.php<?= !empty($keyword) ? '?keyword=' . htmlspecialchars($keyword) : '' ?>"
                                        class="btn btn-outline-danger w-100" target="_blank">
                                        <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
                                    </a>
                                </div>

                                <div class="d-flex flex-column flex-md-row gap-2 w-100 w-md-auto order-3 order-md-2">

                                    <a href="generate_guru_excel.php<?= !empty($keyword) ? '?keyword=' . htmlspecialchars($keyword) : '' ?>"
                                        class="btn btn-outline-success w-100" target="_blank">
                                        <i class="bx bxs-file-excel me-1"></i> Ekspor Excel
                                    </a>
                                    <a href="index.php" class="btn btn-outline-secondary w-100">
                                        <i class="bx bx-arrow-back me-1"></i> Kembali
                                    </a>
                                </div>

                                <div class="col-md order-1 order-md-3">
                                    <form method="GET" action="" class="d-flex">
                                        <input type="text" name="keyword" class="form-control"
                                            placeholder="Cari guru berdasarkan nama atau NIP..."
                                            value="<?= htmlspecialchars($keyword) ?>">
                                        <button type="submit" class="btn btn-primary ms-2">
                                            <i class="bx bx-search"></i>
                                        </button>
                                        <?php if (!empty($keyword)): ?>
                                            <a href="master_guru_pendamping.php" class="btn btn-outline-secondary ms-2">
                                                <i class="bx bx-x"></i>
                                            </a>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Daftar Lengkap Guru Pendamping</h5>
                                <small class="text-muted">Total: <?= $total_data ?> guru</small>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive text-nowrap d-none d-md-block"
                                    style="min-height: calc(100vh - 450px); overflow-y: auto;">
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <table class="table table-hover" style="min-width: 800px;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Nama Guru</th>
                                                    <th>NIP</th>
                                                    <th>Jenis Kelamin</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $no = $offset + 1; // Start numbering from the correct offset
                                                // result_guru_data sudah diganti menjadi $result
                                                // Menggunakan data_seek(0) untuk memastikan loop dimulai dari awal
                                                // jika $result sudah digunakan di tempat lain untuk count.
                                                // Karena query di atas sudah pakai LIMIT/OFFSET, $result sudah pas untuk page ini.
                                                while ($row = $result->fetch_assoc()) {
                                                ?>
                                                    <tr>
                                                        <td><?= $no++ ?></td>
                                                        <td><?= htmlspecialchars($row['nama_pembimbing']) ?></td>
                                                        <td><?= htmlspecialchars($row['nip']) ?></td>
                                                        <td><?= htmlspecialchars($row['jenis_kelamin'] ?? '-') ?></td>
                                                        <td>
                                                            <div class='dropdown'>
                                                                <button class='btn p-0 dropdown-toggle hide-arrow'
                                                                    data-bs-toggle='dropdown'>
                                                                    <i class='bx bx-dots-vertical-rounded'></i>
                                                                </button>
                                                                <div class='dropdown-menu'>
                                                                    <a class='dropdown-item'
                                                                        href='master_guru_pendamping_edit.php?id=<?= htmlspecialchars($row['id_pembimbing']) ?>'>
                                                                        <i class='bx bx-edit-alt me-1'></i> Edit
                                                                    </a>
                                                                    <a class='dropdown-item text-danger'
                                                                        href='javascript:void(0);'
                                                                        onclick="confirmDeleteGuru('<?= htmlspecialchars($row['id_pembimbing']) ?>', '<?= htmlspecialchars($row['nama_pembimbing']) ?>')">
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
                                            <p class="mb-3">Tidak ada data guru ditemukan dengan kriteria tersebut.</p>
                                            <p class="mb-0">
                                                <a href="master_guru_pendamping_add.php"
                                                    class="alert-link fw-bold">Tambahkan
                                                    guru baru</a> atau coba filter lainnya!
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="d-md-none p-3">
                                    <?php
                                    // Reset result pointer for mobile display
                                    if ($result) {
                                        $result->data_seek(0);
                                    }

                                    if ($result && $result->num_rows > 0) {
                                        $colors = ['primary', 'warning', 'info', 'success', 'danger'];
                                        $color_index = 0;
                                        while ($row_mobile = $result->fetch_assoc()) {
                                            $current_color = $colors[$color_index % count($colors)];
                                            $color_index++;
                                    ?>
                                            <div
                                                class="card mb-3 shadow-sm border-start border-4 border-<?= $current_color ?> rounded-3 animate__animated animate__fadeInUp">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                                        <div>
                                                            <h6 class="mb-1 text-primary"><i class="bx bx-user-voice me-1"></i>
                                                                <strong><?= htmlspecialchars($row_mobile['nama_pembimbing']) ?></strong>
                                                            </h6>
                                                            <span class="badge bg-label-secondary"><i
                                                                    class="bx bx-id-card me-1"></i>
                                                                NIP: <?= htmlspecialchars($row_mobile['nip']) ?></span>
                                                        </div>
                                                        <div class="dropdown">
                                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                                data-bs-toggle="dropdown">
                                                                <i class="bx bx-dots-vertical-rounded"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <a class="dropdown-item"
                                                                    href="master_guru_pendamping_edit.php?id=<?= htmlspecialchars($row_mobile['id_pembimbing']) ?>"><i
                                                                        class="bx bx-edit-alt me-1"></i> Edit Data</a>
                                                                <div class="dropdown-divider"></div>
                                                                <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                                    onclick="confirmDeleteGuru('<?= htmlspecialchars($row_mobile['id_pembimbing']) ?>', '<?= htmlspecialchars($row_mobile['nama_pembimbing']) ?>')"><i
                                                                        class="bx bx-trash me-1"></i> Hapus</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <strong class="text-dark"><i class="bx bx-male-female me-1"></i> Jenis
                                                            Kelamin:</strong>
                                                        <?= htmlspecialchars($row_mobile['jenis_kelamin'] ?? '-') ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                    } else {
                                        // Jika tidak ada data sama sekali, tampilkan pesan di sini juga
                                        if ($total_data == 0) {
                                        ?>
                                            <div class="alert alert-info text-center mt-5 py-4 animate__animated animate__fadeInUp"
                                                role="alert"
                                                style="border-radius: 8px; min-height: 200px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                                <h5 class="alert-heading mb-3"><i class="bx bx-info-circle bx-lg text-info"></i>
                                                </h5>
                                                <p class="mb-3">Tidak ada data guru ditemukan dengan kriteria tersebut.</p>
                                                <p class="mb-0">
                                                    <a href="master_guru_pendamping_add.php"
                                                        class="alert-link fw-bold">Tambahkan
                                                        guru baru</a> atau coba filter lainnya!
                                                </p>
                                            </div>
                                    <?php
                                        }
                                    }
                                    ?>
                                </div>

                                <?php if ($total_data > 0 && $total_pages > 1) : ?>
                                    <div class="card-footer d-flex justify-content-center">
                                        <nav aria-label="Page navigation">
                                            <ul class="pagination">
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
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                    <?php include './partials/footer.php'; ?>
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDeleteGuru(id, nama) {
            Swal.fire({
                title: 'Konfirmasi Hapus Data Guru',
                html: `Apakah Anda yakin ingin menghapus <strong>${nama}</strong>?<br>Tindakan ini tidak dapat dibatalkan!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'master_guru_pendamping_delete.php?id=' + id;
                }
            });
        }
    </script>

    <?php include './partials/script.php'; ?>
</body>

</html>