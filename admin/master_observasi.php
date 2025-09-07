<?php
// master_observasi.php

// === Aktifkan error reporting penuh untuk debugging di development (NONAKTIFKAN DI PRODUKSI) ===
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// ==============================================================================================

include 'partials/head.php';
include 'partials/db.php'; // Asumsi file ini berisi koneksi ke database ($koneksi)

$keyword = ""; // Inisialisasi $keyword
$filter = "";  // Inisialisasi $filter

if (isset($_GET['keyword']) && $_GET['keyword'] != '') {
    $keyword = mysqli_real_escape_string($koneksi, $_GET['keyword']);
    // Pastikan nama kolom di WHERE clause sesuai dengan tabel siswa Anda (nisn, bukan no_induk, jika itu yang Anda pakai)
    $filter = "WHERE s.nama_siswa LIKE '%$keyword%'
                OR s.nisn LIKE '%$keyword%'
                OR s.kelas LIKE '%$keyword%'
                OR j.nama_jurusan LIKE '%$keyword%'
                OR gp.nama_pembimbing LIKE '%$keyword%'
                OR tp.nama_tempat_pkl LIKE '%$keyword%'";
}
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/"
    data-template="vertical-menu-template-free">

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
                                <span class="text-muted fw-light">Laporan /</span> Penilaian Siswa
                            </h4>
                            <i class="fas fa-graduation-cap fa-2x text-info animate__animated animate__fadeInRight"
                                style="opacity: 0.6;"></i>
                        </div>

                        <div class="card bg-gradient-primary-to-secondary text-white mb-4 shadow-lg animate__animated animate__fadeInDown"
                            style="border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                            <div
                                class="card-body p-4 d-flex flex-column flex-sm-row justify-content-between align-items-center">
                                <div class="text-center text-sm-start mb-3 mb-sm-0">
                                    <h5 class="card-title text-white mb-1">Daftar Siswa untuk Penilaian</h5>
                                    <p class="card-text text-white-75 small">Pilih siswa untuk melakukan penilaian atau
                                        melihat riwayat laporan.</p>
                                </div>
                                <div class="text-center text-sm-end position-relative">
                                    <div class="rounded-circle bg-white d-flex justify-content-center align-items-center animate__animated animate__zoomIn animate__delay-0-5s"
                                        style="width: 80px; height: 80px; opacity: 0.2; position: relative; z-index: 1;">
                                        <i class="bx bx-file-check bx-lg text-primary" style="font-size: 3rem;"></i>
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
                                </div>
                                <div class="d-flex gap-2 w-100 w-md-auto">
                                    <a href="generate_siswa_pdf.php<?= !empty($keyword) ? '?keyword=' . htmlspecialchars($keyword) : '' ?>"
                                        class="btn btn-outline-danger w-100" target="_blank"> <i
                                            class="bx bxs-file-pdf me-1"></i> Cetak Daftar Siswa
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
                                                placeholder="Cari Siswa berdasarkan nama, NISN, kelas, jurusan, guru, atau tempat PKL..."
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
                                <h5 class="mb-0">Daftar Siswa Peserta PKL</h5>
                                <small class="text-muted">Pilih siswa untuk aksi penilaian</small>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive text-nowrap">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Siswa</th>
                                                <th>NISN</th>
                                                <th>Kelas</th>
                                                <th>Jurusan</th>
                                                <th>Tempat PKL</th>
                                                <th>Guru Pendamping</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            <?php
                                            $query = "SELECT
                                                            s.id_siswa,
                                                            s.nama_siswa,
                                                            s.nisn,
                                                            s.kelas,
                                                            j.nama_jurusan,
                                                            gp.nama_pembimbing,
                                                            tp.nama_tempat_pkl
                                                        FROM
                                                            siswa s
                                                        LEFT JOIN jurusan j ON s.jurusan_id = j.id_jurusan
                                                        LEFT JOIN guru_pembimbing gp ON s.pembimbing_id = gp.id_pembimbing
                                                        LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl
                                                        $filter
            ORDER BY s.nama_siswa ASC";
                                            
$result = mysqli_query($koneksi, $query);
                                            
                                            if (!$result) {
                                                error_log("Query data siswa gagal di master_observasi.php: " . mysqli_error($koneksi));
                                                echo "<tr><td colspan='8' class='text-center text-danger'>Gagal memuat data siswa. Silakan hubungi administrator.</td></tr>";
                                            } else {
                                                $no = 1;
                                                if (mysqli_num_rows($result) > 0) {
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                ?>
                                                <tr>
                                                    <td><?= $no ?></td>
                                                    <td><strong><?= htmlspecialchars($row['nama_siswa'] ?? '') ?></strong>
                                                    </td>
                                                    <td><?= htmlspecialchars($row['nisn'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($row['kelas'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($row['nama_jurusan'] ?? '-') ?></td>
                                                    <td><?= htmlspecialchars($row['nama_tempat_pkl'] ?? '-') ?></td>
                                                    <td><?= htmlspecialchars($row['nama_pembimbing'] ?? '-') ?></td>
                                                   <td> 
                                                        <div class='dropdown'>
                                                            <button class='btn p-0 dropdown-toggle hide-arrow'
                                                            data    -bs-toggle='dropdown'>
                                                                <i class='bx bx-dots-vertical-rounded'></i>
                                                        </button>    
                                                            <div class='dropdown-menu'>
                                                            <a class    ='dropdown-item'
                                                                    href='master_observasi_form_penilaian.php?id_siswa=<?= htmlspecialchars($row['id_siswa'] ?? '') ?>'>
                                                                <i c    lass='bx bx-file-plus me-1'></i> Nilai Siswa
                                                             </a>   
                                                             <a class   ='dropdown-item'
                                                                 href='la   poran_penilaian_histori.php?id_siswa=<?= htmlspecialchars($row['id_siswa'] ?? '') ?>'>
                                                                 <i c   lass='bx bx-history me-1'></i> Lihat Laporan
                                                             </a>   
                                                             <div class="   dropdown-divider"></div>
                                                             <a class='dr   opdown-item'
                                                                 href   ='master_data_siswa_edit.php?id=<?= htmlspecialchars($row['id_siswa'] ?? '') ?>'>
                                                                    <i class='bx bx-edit-alt me-1'></i> Edit Data Siswa
                                                                </a>
                                                                <a class='dropdown-item text-danger'
                                                                    href='javascript:void(0);'
                                                                onclick="confirmDelete('<?= htmlspecialchars($row['id_siswa'] ?? '') ?>', '<?= htmlspecialchars($row['nama_siswa'] ?? '') ?>')">
                                                                <i class='bx bx-trash me-1'></i> Hapus Data Siswa
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                                        $no++;
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='8' class='text-center'>Tidak ada data siswa ditemukan.</td></tr>";
                                                }
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-md-none p-3">
                                    <?php
                                    // Reset result pointer untuk loop kedua atau jalankan query lagi
                                    if ($result) { // Pastikan $result valid sebelum direset
                                        mysqli_data_seek($result, 0); 
                                    }
                                    if (isset($result) && mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                    ?>
                                    <div class="card mb-3 shadow-sm border-start border-4 border-primary rounded-3">
                                        <div class="card-body">
                                            <h6 class="mb-1 text-primary"><i class="bx bx-user me-1"></i>
                                                <strong><?= htmlspecialchars($row['nama_siswa'] ?? '') ?></strong></h6>
                                            <span class="badge bg-label-primary mb-2"><i class="bx bx-hash me-1"></i>
                                                NISN: <?= htmlspecialchars($row['nisn'] ?? '') ?></span>
                                            <p class="mb-1"><strong class="text-dark"><i class="bx bx-award me-1"></i>
                                                    Kelas:</strong> <?= htmlspecialchars($row['kelas'] ?? '') ?></p>
                                            <p class="mb-1"><strong class="text-dark"><i
                                                        class="bx bx-book-open me-1"></i> Jurusan:</strong>
                                                <?= htmlspecialchars($row['nama_jurusan'] ?? '-') ?></p>
                                            <p class="mb-1"><strong class="text-dark"><i
                                                        class="bx bx-building-house me-1"></i> Tempat PKL:</strong>
                                                <?= htmlspecialchars($row['nama_tempat_pkl'] ?? '-') ?></p>
                                            <p class="mb-1"><strong class="text-dark"><i
                                                        class="bx bx-user-voice me-1"></i> Guru Pendamping:</strong>
                                                <?= htmlspecialchars($row['nama_pembimbing'] ?? '-') ?></p>
                                            <div class="d-flex justify-content-end align-items-baseline mt-3 gap-2">
                                                <a href='master_observasi_form_penilaian.php?id_siswa=<?= htmlspecialchars($row['id_siswa'] ?? '') ?>'
                                                    class='btn btn-sm btn-primary'>
                                                    <i class='bx bx-file-plus me-1'></i> Nilai
                                                </a>
                                                <a href='laporan_penilaian_histori.php?id_siswa=<?= htmlspecialchars($row['id_siswa'] ?? '') ?>'
                                                    class='btn btn-sm btn-info'>
                                                    <i class='bx bx-history me-1'></i> Laporan
                                                </a>
                                                <div class="dropdown">
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-secondary p-0 dropdown-toggle hide-arrow"
                                                         data-bs-toggle="dropdown">
                                                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                                                </button>
                                                                                <div class="dropdown-menu dropdown-menu-end">
                                                                                    <a class="dropdown-item"
                                                                                        href="master_data_siswa_edit.php?id=<?= htmlspecialchars($row['id_siswa'] ?? '') ?>">
                                                                                        <i class="bx bx-edit-alt me-1"></i> Edit Data
                                                                                    </a>
                                                                                    <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                                                onclick="confirmDelete('<?= htmlspecialchars($row['id_siswa'] ?? '') ?>', '<?= htmlspecialchars($row['nama_siswa'] ?? '') ?>')">
                                                                                <i class="bx bx-trash me-1'></i> Hapus
                                                                                </a>
                                                                                </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                            </div>
                                                            <?php
                                                        }
                                                    } else {
                                                    ?>
                                                        <div class=" alert alert-info text-center mt-3 py-4 animate__animated
                                                                                animate__fadeInUp" role="alert"
                                                                style="border-radius: 8px;">
                                                                                <h5 class="alert-heading mb-3"><i
                                                                                        class="bx bx-user-plus bx-lg text-info"></i>
                                                                                </h5>
                                                                                <p class="mb-3">Tidak ada data siswa ditemukan dengan
                                                                                    kriteria tersebut.</p>
                                                                                <p class="mb-0">
                                                                                    <a href="master_data_siswa_add.php"
                                                                                        class="alert-link fw-bold">Tambahkan
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

                    <link rel="stylesheet"
                        href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
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