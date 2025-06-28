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
$keyword = "";
$filter = "";
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
                                <span class="text-muted fw-light">Master /</span> Data Siswa
                            </h4>
                            <i class="fas fa-graduation-cap fa-2x text-info animate__animated animate__fadeInRight"
                                style="opacity: 0.6;"></i>
                        </div>

                        <div class="card bg-gradient-primary-to-secondary text-white mb-4 shadow-lg animate__animated animate__fadeInDown"
                            style="border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                            <div
                                class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-center">
                                <div class="text-center text-sm-start mb-3 mb-sm-0">
                                    <h5 class="card-title text-white mb-1">Kelola Data Siswa PKL</h5>
                                    <p class="card-text text-white-75 small">Informasi lengkap siswa peserta PKL.</p>
                                </div>
                                <div class="text-center text-sm-end position-relative">
                                    <div class="rounded-circle bg-white d-flex justify-content-center align-items-center animate__animated animate__zoomIn animate__delay-0-5s"
                                        style="width: 80px; height: 80px; opacity: 0.2; position: relative; z-index: 1;">
                                        <i class="bx bx-user-check bx-lg text-primary" style="font-size: 3rem;"></i>
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
                                    <a href="master_data_siswa_add.php" class="btn btn-primary w-100">
                                        <i class="bx bx-plus me-1"></i> Tambah Siswa
                                    </a>
                                </div>
                                <div class="d-flex gap-2 w-100 w-md-auto">
                                    <a href="generate_siswa_pdf.php<?= !empty($keyword) ? '?keyword=' . htmlspecialchars($keyword) : '' ?>"
                                        class="btn btn-outline-danger w-100" target="_blank"> <i
                                            class="bx bxs-file-pdf me-1"></i> Cetak PDF
                                    </a>
                                    <button type="button" class="btn btn-outline-success w-100">
                                        <i class="bx bxs-file-excel me-1"></i> Ekspor Excel
                                    </button>
                                </div>
                            </div>

                            <div class="card-footer bg-light border-top p-3">
                                <form method="GET" action="">
                                    <div class="row align-items-center">
                                        <div class="col-md-8 mb-2 mb-md-0">
                                            <input type="text" name="keyword" class="form-control"
                                                placeholder="Cari Siswa (Nama, No Induk, Kelas, Jurusan, Guru, Tempat PKL, Status)..."
                                                value="<?= htmlspecialchars($keyword) ?>">
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <button type="submit" class="btn btn-outline-dark w-100 w-md-auto">
                                                <i class="bx bx-filter-alt me-1"></i> Filter Siswa
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Daftar Lengkap Siswa PKL</h5>
                                <small class="text-muted">Informasi detail seluruh siswa</small>
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
                                                <th>Kelas</th>
                                                <th>Jurusan</th>
                                                <th>Guru Pendamping</th>
                                                <th>Tempat PKL</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            <?php
                                            $filter = "";
                                            if (isset($_GET['keyword']) && $_GET['keyword'] != '') {
                                                $keyword = mysqli_real_escape_string($koneksi, $_GET['keyword']);
                                                $filter = "WHERE siswa.nama_siswa LIKE '%$keyword%' 
                                                            OR siswa.no_induk LIKE '%$keyword%'
                                                            OR siswa.jenis_kelamin LIKE '%$keyword%'
                                                            OR siswa.kelas LIKE '%$keyword%'
                                                            OR jurusan.nama_jurusan LIKE '%$keyword%'
                                                            OR guru_pembimbing.nama_pembimbing LIKE '%$keyword%'
                                                            OR tempat_pkl.nama_tempat_pkl LIKE '%$keyword%'
                                                            OR siswa.status LIKE '%$keyword%'";
                                            }

                                            $query = "SELECT 
                                                            siswa.id_siswa,
                                                            siswa.nama_siswa,
                                                            siswa.no_induk,
                                                            siswa.kelas,
                                                            siswa.status,
                                                            jurusan.nama_jurusan,
                                                            guru_pembimbing.nama_pembimbing,
                                                            tempat_pkl.nama_tempat_pkl
                                                        FROM siswa
                                                        LEFT JOIN jurusan ON siswa.jurusan_id = jurusan.id_jurusan
                                                        LEFT JOIN guru_pembimbing ON siswa.pembimbing_id = guru_pembimbing.id_pembimbing
                                                        LEFT JOIN tempat_pkl ON siswa.tempat_pkl_id = tempat_pkl.id_tempat_pkl
                                                        $filter
                                                        ORDER BY siswa.nama_siswa ASC"; // Added this line for alphabetical order

                                            $result = mysqli_query($koneksi, $query);
                                            $no = 1;

                                            if (mysqli_num_rows($result) > 0) {
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    $badgeColor = match ($row['status']) {
                                                        'Tidak Aktif' => 'bg-label-warning',
                                                        'Selesai' => 'bg-label-info',
                                                        default => 'bg-label-success',
                                                    };
                                            ?>
                                            <tr>
                                                <td><?= $no ?></td>
                                                <td><strong><?= htmlspecialchars($row['nama_siswa']) ?></strong></td>
                                                <td><?= htmlspecialchars($row['no_induk']) ?></td>
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
                                                                onclick="confirmDelete('<?= htmlspecialchars($row['id_siswa']) ?>', '<?= htmlspecialchars($row['nama_siswa']) ?>')">
                                                                <i class='bx bx-trash me-1'></i> Hapus
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                                    $no++;
                                                }
                                            } else {
                                                echo "<tr><td colspan='9' class='text-center'>Tidak ada data siswa ditemukan.</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-md-none p-3">
                                    <?php
                                    mysqli_data_seek($result, 0);
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $badgeColor = match ($row['status']) {
                                                'Tidak Aktif' => 'bg-label-warning',
                                                'Selesai' => 'bg-label-info',
                                                default => 'bg-label-success',
                                            };
                                    ?>
                                    <div
                                        class="card mb-4 shadow-lg border-start border-4 border-primary rounded-3 animate__animated animate__fadeInUp">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="mb-1 text-primary"><i class="bx bx-user me-1"></i>
                                                        <strong><?= htmlspecialchars($row['nama_siswa']) ?></strong>
                                                    </h6>
                                                    <span class="badge bg-label-primary"><i class="bx bx-hash me-1"></i>
                                                        No Induk: <?= htmlspecialchars($row['no_induk']) ?></span>
                                                </div>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                        data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item"
                                                            href="master_data_siswa_edit.php?id=<?= htmlspecialchars($row['id_siswa']) ?>">
                                                            <i class="bx bx-edit-alt me-1"></i> Edit Data
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                            onclick="confirmDelete('<?= htmlspecialchars($row['id_siswa']) ?>', '<?= htmlspecialchars($row['nama_siswa']) ?>')">
                                                            <i class="bx bx-trash me-1"></i> Hapus
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-2">
                                                <strong class="text-dark"><i class="bx bx-award me-1"></i>
                                                    Kelas:</strong><br>
                                                <?= htmlspecialchars($row['kelas']) ?>
                                            </div>
                                            <div class="mb-2">
                                                <strong class="text-dark"><i class="bx bx-book-open me-1"></i>
                                                    Jurusan:</strong><br>
                                                <?= htmlspecialchars($row['nama_jurusan'] ?? '-') ?>
                                            </div>
                                            <div class="mb-2">
                                                <strong class="text-dark"><i class="bx bx-user-voice me-1"></i> Guru
                                                    Pendamping:</strong><br>
                                                <?= htmlspecialchars($row['nama_pembimbing'] ?? '-') ?>
                                            </div>
                                            <div class="mb-2">
                                                <strong class="text-dark"><i class="bx bx-building-house me-1"></i>
                                                    Tempat PKL:</strong><br>
                                                <?= htmlspecialchars($row['nama_tempat_pkl'] ?? '-') ?>
                                            </div>
                                            <div class="d-flex justify-content-end align-items-baseline mt-3">
                                                <small class="text-muted"><i class="bx bx-calendar-check me-1"></i>
                                                    Status: <?= htmlspecialchars($row['status']) ?></small>
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
    function confirmDelete(id, nama) {
        Swal.fire({
            title: 'Konfirmasi Hapus Data',
            html: `Apakah Anda yakin ingin menghapus data siswa bernama <strong>${nama}</strong>?<br>Tindakan ini tidak dapat dibatalkan!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus Sekarang!',
            cancelButtonText: 'Batal',
            reverseButtons: true
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