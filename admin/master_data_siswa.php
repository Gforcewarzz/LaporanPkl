<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/" data-template="vertical-menu-template-free">

<?php include 'partials/head.php'; include "partials/db.php"; ?>

<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include './partials/sidebar.php'; ?>
        <div class="layout-page">
            <?php include './partials/navbar.php'; ?>
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">

                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom position-relative">
                        <h4 class="fw-bold mb-0 text-primary animate__animated animate__fadeInLeft">
                            <span class="text-muted fw-light">Master /</span> Data Siswa
                        </h4>
                        <i class="fas fa-graduation-cap fa-2x text-info animate__animated animate__fadeInRight" style="opacity: 0.6;"></i>
                    </div>

                    <!-- Banner -->
                    <div class="card bg-gradient-primary-to-secondary text-white mb-4 shadow-lg animate__animated animate__fadeInDown"
                         style="border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                        <div class="card-body p-4 d-flex flex-column flex-sm-row justify-content-between align-items-center">
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

                    <!-- Action Buttons -->
                    <div class="card mb-4 shadow-lg position-relative" style="border-radius: 10px;">
                        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 p-4">
                            <div class="d-flex gap-2 w-100 w-md-auto order-2 order-md-1">
                                <a href="index.php" class="btn btn-outline-secondary w-100"><i class="bx bx-arrow-back me-1"></i> Kembali</a>
                                <a href="master_data_siswa_add.php" class="btn btn-primary w-100"><i class="bx bx-plus me-1"></i> Tambah Siswa</a>
                            </div>
                            <div class="d-flex gap-2 w-100 w-md-auto order-1 order-md-2">
                                <button class="btn btn-outline-danger w-100"><i class="bx bxs-file-pdf me-1"></i> Cetak PDF</button>
                                <button class="btn btn-outline-success w-100"><i class="bx bxs-file-excel me-1"></i> Ekspor Excel</button>
                            </div>
                        </div>
                        <div class="card-footer bg-light border-top p-3">
                            <div class="row align-items-center">
                                <div class="col-md-8 mb-2 mb-md-0">
                                    <input type="text" class="form-control" placeholder="Cari siswa berdasarkan nama, NISN, jenis kelamin atau kelas...">
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <button class="btn btn-outline-dark w-100 w-md-auto"><i class="bx bx-filter-alt me-1"></i> Filter Siswa</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Siswa -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Daftar Lengkap Siswa PKL</h5>
                            <small class="text-muted">Informasi detail seluruh siswa</small>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive text-nowrap">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama</th>
                                            <th>Jenis Kelamin</th>
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
                                        $query = "SELECT 
                                                    siswa.id_siswa,
                                                    siswa.nama_siswa,
                                                    siswa.jenis_kelamin,
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
                                                ORDER BY siswa.id_siswa ASC";

                                        $result = mysqli_query($koneksi, $query);
                                        $no = 1;

                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $badgeColor = match($row['status']) {
                                                'Tidak Aktif' => 'bg-label-warning',
                                                'Selesai' => 'bg-label-info',
                                                default => 'bg-label-success',
                                            };

                                            echo "<tr>
                                                    <td>{$no}</td>
                                                    <td><strong>{$row['nama_siswa']}</strong></td>
                                                    <td>{$row['jenis_kelamin']}</td>
                                                    <td>{$row['no_induk']}</td>
                                                    <td>{$row['kelas']}</td>
                                                    <td>{$row['nama_jurusan']}</td>
                                                    <td>{$row['nama_pembimbing']}</td>
                                                    <td>{$row['nama_tempat_pkl']}</td>
                                                    <td><span class='badge {$badgeColor}'>{$row['status']}</span></td>
                                                    <td>
                                                        <div class='dropdown'>
                                                            <button class='btn p-0 dropdown-toggle' data-bs-toggle='dropdown'>
                                                                <i class='bx bx-dots-vertical-rounded'></i>
                                                            </button>
                                                            <div class='dropdown-menu'>
                                                                <a class='dropdown-item' href='master_data_siswa_edit.php?id={$row['id_siswa']}'>
                                                                    <i class='bx bx-edit-alt me-1'></i> Edit
                                                                </a>
                                                                <a class='dropdown-item text-danger' href='javascript:void(0);' onclick=\"confirmDelete('{$row['id_siswa']}', '{$row['nama_siswa']}')\">
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
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="layout-overlay layout-menu-toggle"></div>
        </div>
    </div>
</div>

<!-- Scripts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(id, nama) {
    Swal.fire({
        title: 'Konfirmasi Hapus Data',
        html: `Apakah Anda yakin ingin menghapus data siswa <strong>${nama}</strong>?<br>Tindakan ini tidak dapat dibatalkan!`,
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
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/" data-template="vertical-menu-template-free">

<?php include 'partials/head.php'; include "partials/db.php"; ?>

<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include './partials/sidebar.php'; ?>
        <div class="layout-page">
            <?php include './partials/navbar.php'; ?>
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">

                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom position-relative">
                        <h4 class="fw-bold mb-0 text-primary animate__animated animate__fadeInLeft">
                            <span class="text-muted fw-light">Master /</span> Data Siswa
                        </h4>
                        <i class="fas fa-graduation-cap fa-2x text-info animate__animated animate__fadeInRight" style="opacity: 0.6;"></i>
                    </div>

                    <!-- Banner -->
                    <div class="card bg-gradient-primary-to-secondary text-white mb-4 shadow-lg animate__animated animate__fadeInDown"
                         style="border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                        <div class="card-body p-4 d-flex flex-column flex-sm-row justify-content-between align-items-center">
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

                    <!-- Action Buttons -->
                    <div class="card mb-4 shadow-lg position-relative" style="border-radius: 10px;">
                        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 p-4">
                            <div class="d-flex gap-2 w-100 w-md-auto order-2 order-md-1">
                                <a href="index.php" class="btn btn-outline-secondary w-100"><i class="bx bx-arrow-back me-1"></i> Kembali</a>
                                <a href="master_data_siswa_add.php" class="btn btn-primary w-100"><i class="bx bx-plus me-1"></i> Tambah Siswa</a>
                            </div>
                            <div class="d-flex gap-2 w-100 w-md-auto order-1 order-md-2">
                                <button class="btn btn-outline-danger w-100"><i class="bx bxs-file-pdf me-1"></i> Cetak PDF</button>
                                <button class="btn btn-outline-success w-100"><i class="bx bxs-file-excel me-1"></i> Ekspor Excel</button>
                            </div>
                        </div>
                        <div class="card-footer bg-light border-top p-3">
                            <div class="row align-items-center">
                                <div class="col-md-8 mb-2 mb-md-0">
                                    <input type="text" class="form-control" placeholder="Cari siswa berdasarkan nama, NISN, jenis kelamin atau kelas...">
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <button class="btn btn-outline-dark w-100 w-md-auto"><i class="bx bx-filter-alt me-1"></i> Filter Siswa</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Siswa -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Daftar Lengkap Siswa PKL</h5>
                            <small class="text-muted">Informasi detail seluruh siswa</small>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive text-nowrap">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama</th>
                                            <th>Jenis Kelamin</th>
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
                                        $query = "SELECT 
                                                    siswa.id_siswa,
                                                    siswa.nama_siswa,
                                                    siswa.jenis_kelamin,
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
                                                ORDER BY siswa.id_siswa ASC";

                                        $result = mysqli_query($koneksi, $query);
                                        $no = 1;

                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $badgeColor = match($row['status']) {
                                                'Tidak Aktif' => 'bg-label-warning',
                                                'Selesai' => 'bg-label-info',
                                                default => 'bg-label-success',
                                            };

                                            echo "<tr>
                                                    <td>{$no}</td>
                                                    <td><strong>{$row['nama_siswa']}</strong></td>
                                                    <td>{$row['jenis_kelamin']}</td>
                                                    <td>{$row['no_induk']}</td>
                                                    <td>{$row['kelas']}</td>
                                                    <td>{$row['nama_jurusan']}</td>
                                                    <td>{$row['nama_pembimbing']}</td>
                                                    <td>{$row['nama_tempat_pkl']}</td>
                                                    <td><span class='badge {$badgeColor}'>{$row['status']}</span></td>
                                                    <td>
                                                        <div class='dropdown'>
                                                            <button class='btn p-0 dropdown-toggle' data-bs-toggle='dropdown'>
                                                                <i class='bx bx-dots-vertical-rounded'></i>
                                                            </button>
                                                            <div class='dropdown-menu'>
                                                                <a class='dropdown-item' href='master_data_siswa_edit.php?id={$row['id_siswa']}'>
                                                                    <i class='bx bx-edit-alt me-1'></i> Edit
                                                                </a>
                                                                <a class='dropdown-item text-danger' href='javascript:void(0);' onclick=\"confirmDelete('{$row['id_siswa']}', '{$row['nama_siswa']}')\">
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
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="layout-overlay layout-menu-toggle"></div>
        </div>
    </div>
</div>

<!-- Scripts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(id, nama) {
    Swal.fire({
        title: 'Konfirmasi Hapus Data',
        html: `Apakah Anda yakin ingin menghapus data siswa <strong>${nama}</strong>?<br>Tindakan ini tidak dapat dibatalkan!`,
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
