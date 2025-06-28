<?php

session_start();

if (!isset($_SESSION['admin_status_login']) || $_SESSION['admin_status_login'] !== 'logged_in') {
    if (isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in') {
        header('Location: master_kegiatan_harian.php');
        exit();
    } elseif (isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in') {
        header('Location: ../../halaman_guru.php');
        exit();
    } else {
        header('Location: ../login.php');
        exit();
    }
}

include 'partials/db.php';
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
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

                        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                            <h4 class="fw-bold mb-0 text-primary">
                                <span class="text-muted fw-light">Master /</span> Data Guru Pendamping
                            </h4>
                            <i class="fas fa-chalkboard-teacher fa-2x text-info" style="opacity: 0.6;"></i>
                        </div>

                        <div class="card bg-gradient-primary-to-secondary text-white mb-4 shadow-lg"
                            style="border-radius: 12px; overflow: hidden;">
                            <div
                                class="card-body p-4 d-flex flex-column flex-sm-row justify-content-between align-items-center">
                                <div class="text-center text-sm-start mb-3 mb-sm-0">
                                    <h5 class="card-title text-white mb-1">Manajemen Data Guru Pendamping</h5>
                                    <p class="card-text text-white-75 small">Kelola informasi pembimbing PKL siswa.</p>
                                </div>
                                <div class="text-center text-sm-end">
                                    <div class="rounded-circle bg-white d-flex justify-content-center align-items-center"
                                        style="width: 80px; height: 80px; opacity: 0.2;">
                                        <i class="bx bx-user-voice bx-lg text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4 shadow-lg">
                            <div
                                class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 p-4">
                                <div class="d-flex gap-2 w-100 w-md-auto">
                                    <a href="index.php" class="btn btn-outline-secondary w-100">
                                        <i class="bx bx-arrow-back me-1"></i> Kembali
                                    </a>
                                    <a href="master_guru_pendamping_add.php" class="btn btn-primary w-100">
                                        <i class="bx bx-plus me-1"></i> Tambah Guru
                                    </a>
                                </div>
                                <div class="d-flex gap-2 w-100 w-md-auto">
                                    <a href="generate_guru_pendamping.php<?= !empty($keyword) ? '?keyword=' . htmlspecialchars($keyword) : '' ?>"
                                        class="btn btn-outline-danger w-100" target="_blank"> <i
                                            class="bx bxs-file-pdf me-1"></i> Cetak PDF
                                    </a>
                                    <a href="generate_guru_excel.php<?= !empty($keyword) ? '?keyword=' . htmlspecialchars($keyword) : '' ?>"
                                        class="btn btn-outline-success w-100" target="_blank">
                                        <i class="bx bxs-file-excel me-1"></i> Ekspor Excel
                                    </a>
                                </div>
                            </div>

                            <div class="card-footer bg-light border-top p-3">
                                <form method="GET" action="">
                                    <div class="row align-items-center">
                                        <div class="col-md-8 mb-2 mb-md-0">
                                            <input type="text" name="keyword" class="form-control"
                                                placeholder="Cari guru berdasarkan nama atau NIP..."
                                                value="<?= htmlspecialchars($keyword) ?>">
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <button type="submit" class="btn btn-outline-dark w-100 w-md-auto">
                                                <i class="bx bx-filter-alt me-1"></i> Filter Guru
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Daftar Lengkap Guru Pendamping</h5>
                                <small class="text-muted">Informasi dari database</small>
                            </div>
                            <div class="card-body p-0">
                                <?php
                                $no = 1;
                                $sql = "SELECT * FROM guru_pembimbing";
                                if (!empty($keyword)) {
                                    $keyword_safe = mysqli_real_escape_string($koneksi, $keyword);
                                    $sql .= " WHERE nama_pembimbing LIKE '%$keyword_safe%' OR nip LIKE '%$keyword_safe%'";
                                }
                                // Added this line for alphabetical order by nama_pembimbing
                                $sql .= " ORDER BY nama_pembimbing ASC";

                                $result = mysqli_query($koneksi, $sql);
                                $total_rows = mysqli_num_rows($result);
                                ?>

                                <div class="table-responsive d-none d-md-block"
                                    style="overflow-x: auto; min-height: calc(100vh - 450px); overflow-y: auto;">
                                    <?php if ($total_rows > 0): ?>
                                        <table class="table table-hover" style="min-width: 800px;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Nama Guru</th>
                                                    <th>NIP</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Reset pointer if already fetched for count
                                                mysqli_data_seek($result, 0);
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    echo "<tr>
                                                    <td>{$no}</td>
                                                    <td>{$row['nama_pembimbing']}</td>
                                                    <td>{$row['nip']}</td>
                                                    <td>
                                                        <div class='dropdown'>
                                                            <button class='btn p-0 dropdown-toggle' data-bs-toggle='dropdown'>
                                                                <i class='bx bx-dots-vertical-rounded'></i>
                                                            </button>
                                                            <div class='dropdown-menu' style='z-index: 1050;'> 
                                                                <a class='dropdown-item' href='master_guru_pendamping_edit.php?id={$row['id_pembimbing']}'>
                                                                    <i class='bx bx-edit-alt me-1'></i> Edit
                                                                </a>
                                                                <a class='dropdown-item text-danger' href='javascript:void(0);' onclick=\"confirmDeleteGuru('{$row['id_pembimbing']}', '{$row['nama_pembimbing']}')\">
                                                                    <i class='bx bx-trash me-1'></i> Hapus
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    </tr>";
                                                    $no++;
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
                                    mysqli_data_seek($result, 0);
                                    if ($total_rows > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                    ?>
                                            <div
                                                class="card mb-3 shadow-sm border-start border-4 border-primary rounded-3 animate__animated animate__fadeInUp">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                                        <div>
                                                            <h6 class="mb-1 text-primary"><i class="bx bx-user-voice me-1"></i>
                                                                <strong><?= htmlspecialchars($row['nama_pembimbing']) ?></strong>
                                                            </h6>
                                                            <span class="badge bg-label-secondary"><i
                                                                    class="bx bx-id-card me-1"></i>
                                                                NIP: <?= htmlspecialchars($row['nip']) ?></span>
                                                        </div>
                                                        <div class="dropdown">
                                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                                data-bs-toggle="dropdown">
                                                                <i class="bx bx-dots-vertical-rounded"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <a class="dropdown-item"
                                                                    href="master_guru_pendamping_edit.php?id=<?= htmlspecialchars($row['id_pembimbing']) ?>">
                                                                    <i class="bx bx-edit-alt me-1"></i> Edit Data
                                                                </a>
                                                                <div class="dropdown-divider"></div>
                                                                <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                                    onclick="confirmDeleteGuru('<?= htmlspecialchars($row['id_pembimbing']) ?>', '<?= htmlspecialchars($row['nama_pembimbing']) ?>')">
                                                                    <i class="bx bx-trash me-1"></i> Hapus
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                    } else {
                                        // This alert will also be displayed if no data is found for mobile
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
                                    ?>
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
        function confirmDeleteGuru(id, nama) {
            Swal.fire({
                title: 'Konfirmasi Hapus Data Guru',
                html: `Apakah Anda yakin ingin menghapus <strong>${nama}</strong>?`,
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