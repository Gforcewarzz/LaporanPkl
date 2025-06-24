<?php include 'partials/head.php'; ?>
<?php include 'partials/db.php';

// Ambil keyword pencarian (jika ada)
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default">
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
                            <span class="text-muted fw-light">Master /</span> Data Tempat PKL
                        </h4>
                        <i class="fas fa-building fa-2x text-info animate__animated animate__fadeInRight" style="opacity: 0.6;"></i>
                    </div>

                    <!-- Tombol Aksi dan Filter -->
                    <div class="card mb-4 shadow-lg">
                        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 p-4">
                            <div class="d-flex gap-2 w-100 w-md-auto">
                                <a href="index.php" class="btn btn-outline-secondary w-100">
                                    <i class="bx bx-arrow-back me-1"></i> Kembali
                                </a>
                                <a href="master_tempat_pkl_add.php" class="btn btn-primary w-100">
                                    <i class="bx bx-plus me-1"></i> Tambah Tempat Pkl
                                </a>
                            </div>
                            <div class="d-flex gap-2 w-100 w-md-auto">
                                <button type="button" class="btn btn-outline-danger w-100">
                                    <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
                                </button>
                                <button type="button" class="btn btn-outline-success w-100">
                                    <i class="bx bxs-file-excel me-1"></i> Ekspor Excel
                                </button>
                            </div>
                        </div>
                        <div class="card-footer bg-light border-top p-3">
                            <form method="GET" action="">
                                <div class="row align-items-center">
                                    <div class="col-md-8 mb-2 mb-md-0">
                                        <input type="text" name="keyword" class="form-control" value="<?= htmlspecialchars($keyword) ?>"
                                               placeholder="Cari Tempat PKL berdasarkan nama tempat atau instruktur...">
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <button class="btn btn-outline-dark w-100 w-md-auto">
                                            <i class="bx bx-filter-alt me-1"></i> Filter Tempat PKL
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabel Data -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Daftar Lengkap Tempat PKL</h5>
                            <small class="text-muted">Informasi detail seluruh mitra perusahaan/instansi</small>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive text-nowrap">
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
                                        $no = 1;
                                        $whereClause = "";
                                        if (!empty($keyword)) {
                                            $keywordSafe = mysqli_real_escape_string($koneksi, $keyword);
                                            $whereClause = "WHERE tp.nama_tempat_pkl LIKE '%$keywordSafe%' 
                                                            OR tp.nama_instruktur LIKE '%$keywordSafe%'";
                                        }

                                        $query = mysqli_query($koneksi, "
                                            SELECT tp.*, j.nama_jurusan 
                                            FROM tempat_pkl tp
                                            LEFT JOIN jurusan j ON tp.jurusan_id = j.id_jurusan
                                            $whereClause
                                            ORDER BY tp.id_tempat_pkl ASC
                                        ");

                                        if (mysqli_num_rows($query) > 0) {
                                            while ($data = mysqli_fetch_assoc($query)) {
                                                echo "<tr>
                                                    <td>{$no}</td>
                                                    <td><strong>" . htmlspecialchars($data['nama_tempat_pkl']) . "</strong></td>
                                                    <td>" . htmlspecialchars($data['alamat']) . "</td>
                                                    <td>" . htmlspecialchars($data['alamat_kontak']) . "</td>
                                                    <td>" . htmlspecialchars($data['nama_instruktur']) . "</td>
                                                    <td><span class='badge bg-label-info me-1'>{$data['kuota_siswa']} Siswa</span></td>
                                                    <td>" . htmlspecialchars($data['nama_jurusan'] ?: '-') . "</td>
                                                    <td>
                                                        <div class='dropdown'>
                                                            <button type='button' class='btn p-0 dropdown-toggle hide-arrow' data-bs-toggle='dropdown'>
                                                                <i class='bx bx-dots-vertical-rounded'></i>
                                                            </button>
                                                            <div class='dropdown-menu'>
                                                                <a class='dropdown-item' href='master_tempat_pkl_edit.php?id={$data['id_tempat_pkl']}'>
                                                                    <i class='bx bx-edit-alt me-1'></i> Edit
                                                                </a>
                                                                <a class='dropdown-item text-danger' href='javascript:void(0);' onclick=\"confirmDeleteTempatPKL('{$data['id_tempat_pkl']}', '" . htmlspecialchars($data['nama_tempat_pkl']) . "')\">
                                                                    <i class='bx bx-trash me-1'></i> Hapus
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>";
                                                $no++;
                                            }
                                        } else {
                                            echo "<tr><td colspan='8' class='text-center text-muted py-4'>Tidak ada data ditemukan.</td></tr>";
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

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDeleteTempatPKL(id, namaPerusahaan) {
    Swal.fire({
        title: 'Konfirmasi Hapus Data Tempat PKL',
        html: "Apakah Anda yakin ingin menghapus <strong>" + namaPerusahaan + "</strong>?",
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
