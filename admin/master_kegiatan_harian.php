<?php
include 'partials/head.php';
include 'partials/db.php';

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

if (!isset($_SESSION['id_siswa'])) {
    header("Location: ../login.php");
    exit;
}

$siswa_id = $_SESSION['id_siswa'];

$sql_laporan = "SELECT id_jurnal_harian, tanggal, pekerjaan, catatan FROM jurnal_harian WHERE siswa_id = ?";
if (!empty($keyword)) {
    $sql_laporan .= " AND (pekerjaan LIKE '%" . mysqli_real_escape_string($koneksi, $keyword) . "%' OR catatan LIKE '%" . mysqli_real_escape_string($koneksi, $keyword) . "%')";
}
$sql_laporan .= " ORDER BY tanggal DESC";

$stmt_laporan = $koneksi->prepare($sql_laporan);
$stmt_laporan->bind_param("i", $siswa_id);
$stmt_laporan->execute();
$result_laporan = $stmt_laporan->get_result();

$laporan_data = $result_laporan->fetch_all(MYSQLI_ASSOC);
$stmt_laporan->close();
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
                                <span class="text-muted fw-light">Siswa /</span> Laporan Harian
                            </h4>
                            <i class="fas fa-edit fa-2x text-info animate__animated animate__fadeInRight"
                                style="opacity: 0.6;"></i>
                        </div>

                        <div class="card bg-gradient-primary-to-secondary text-white mb-4 shadow-lg animate__animated animate__fadeInDown"
                            style="border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                            <div
                                class="card-body p-4 d-flex flex-column flex-sm-row justify-content-between align-items-center">
                                <div class="text-center text-sm-start mb-3 mb-sm-0">
                                    <h5 class="card-title text-white mb-1">Catat Progres PKLmu di Sini!</h5>
                                    <p class="card-text text-white-75 small">Setiap laporan adalah langkah menuju
                                        kesuksesan.</p>
                                </div>
                                <div class="text-center text-sm-end position-relative">
                                    <div class="rounded-circle bg-white d-flex justify-content-center align-items-center animate__animated animate__zoomIn animate__delay-0-5s"
                                        style="width: 80px; height: 80px; opacity: 0.2; position: relative; overflow: hidden; z-index: 1;">
                                        <i class="bx bx-check-circle bx-lg text-primary"
                                            style="font-size: 3rem; opacity: 1;"></i>
                                    </div>
                                    <div class="position-absolute rounded-circle bg-white"
                                        style="width: 50px; height: 50px; opacity: 0.1; top: -10px; left: -10px; transform: scale(0.6); z-index: 0;">
                                    </div>
                                    <div class="position-absolute rounded-circle bg-white"
                                        style="width: 60px; height: 60px; opacity: 0.15; bottom: -10px; right: -10px; transform: scale(0.8); z-index: 0;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4 shadow-lg position-relative" style="border-radius: 10px;">
                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
                                style="pointer-events: none; z-index: 0; opacity: 0.05;">
                                <svg width="100%" height="100%" viewBox="0 0 200 100" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 20 C 50 0, 150 0, 200 20 L 200 80 C 150 100, 50 100, 0 80 Z"
                                        fill="currentColor" opacity="0.1"
                                        class="text-primary animate__animated animate__fadeIn animate__delay-0-1s" />
                                    <path d="M0 30 C 50 10, 150 10, 200 30 L 200 70 C 150 90, 50 90, 0 70 Z"
                                        fill="currentColor" opacity="0.15"
                                        class="text-info animate__animated animate__fadeIn animate__delay-0-2s" />
                                </svg>
                            </div>
                            <div
                                class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 position-relative z-1 p-4">
                                <div class="d-flex flex-column flex-md-row gap-2 w-100 w-md-auto order-2 order-md-1">
                                    <a href="dashboard_siswa.php"
                                        class="btn btn-outline-secondary w-100 animate__animated animate__fadeInUp animate__delay-0-2s">
                                        <i class="bx bx-arrow-back me-1"></i> Kembali
                                    </a>
                                    <a href="master_kegiatan_harian_add.php"
                                        class="btn btn-primary w-100 animate__animated animate__fadeInUp animate__delay-0-3s">
                                        <i class="bx bx-plus me-1"></i> Tambah Laporan
                                    </a>
                                </div>
                                <div class="d-flex flex-column flex-md-row gap-2 w-100 w-md-auto order-1 order-md-2">
                                    <a href="generate_laporan_harian_pdf.php<?= !empty($keyword) ? '?keyword=' . htmlspecialchars($keyword) : '' ?>"
                                        class="btn btn-outline-danger w-100 animate__animated animate__fadeInDown animate__delay-0-3s"
                                        target="_blank">
                                        <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
                                    </a>
                                    <button type="button"
                                        class="btn btn-outline-success w-100 animate__animated animate__fadeInDown animate__delay-0-2s">
                                        <i class="bx bxs-file-excel me-1"></i> Ekspor Excel
                                    </button>
                                </div>
                            </div>
                            <div class="card-footer bg-light border-top p-3 pt-md-2 pb-md-2 position-relative z-1">
                                <div
                                    class="row align-items-center animate__animated animate__fadeInUp animate__delay-0-4s">
                                    <div class="col-12 col-md-8 mb-2 mb-md-0">
                                        <form method="GET" action="" id="filterForm">
                                            <input type="text" name="keyword" class="form-control"
                                                placeholder="Cari laporan berdasarkan kata kunci..." aria-label="Search"
                                                value="<?= htmlspecialchars($keyword) ?>" />
                                    </div>
                                    <div class="col-12 col-md-4 text-md-end">
                                        <button type="submit" class="btn btn-outline-dark w-100 w-md-auto"><i
                                                class="bx bx-filter-alt me-1"></i> Filter Laporan</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Daftar Laporan Harian Anda</h5>
                                <small class="text-muted">Riwayat lengkap aktivitas PKL</small>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive text-nowrap d-none d-md-block">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Hari/Tanggal</th>
                                                <th>Pekerjaan</th>
                                                <th>Catatan</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            <?php
                                            if (count($laporan_data) > 0) {
                                                $no = 1;
                                                foreach ($laporan_data as $row) {
                                                    $hari_indonesia = [
                                                        'Sunday' => 'Minggu',
                                                        'Monday' => 'Senin',
                                                        'Tuesday' => 'Selasa',
                                                        'Wednesday' => 'Rabu',
                                                        'Thursday' => 'Kamis',
                                                        'Friday' => 'Jumat',
                                                        'Saturday' => 'Sabtu'
                                                    ];
                                                    $nama_hari_inggris = date('l', strtotime($row['tanggal']));
                                                    $formatted_date_display = $hari_indonesia[$nama_hari_inggris] . ', ' . date('d F Y', strtotime($row['tanggal']));
                                            ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><strong><?= htmlspecialchars($formatted_date_display) ?></strong>
                                                </td>
                                                <td><?= nl2br(htmlspecialchars($row['pekerjaan'])) ?></td>
                                                <td><?= nl2br(htmlspecialchars($row['catatan'])) ?></td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="bx bx-dots-vertical-rounded"></i>
                                                        </button>
                                                        <div class="dropdown-menu" style='z-index: 1050;'> <a
                                                                class="dropdown-item"
                                                                href="master_kegiatan_harian_edit.php?id=<?= htmlspecialchars($row['id_jurnal_harian']) ?>">
                                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                                            </a>
                                                            <div class="dropdown-divider"></div>
                                                            <a class="dropdown-item text-danger"
                                                                href="javascript:void(0);"
                                                                onclick="confirmDeleteKegiatanHarian('<?= htmlspecialchars($row['id_jurnal_harian']) ?>', '<?= htmlspecialchars($formatted_date_display) ?>')">
                                                                <i class="bx bx-trash me-1"></i> Hapus
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                                }
                                            } else {
                                                ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">
                                                    <i class="bx bx-info-circle me-1"></i> Belum ada laporan kegiatan
                                                    yang tercatat.
                                                </td>
                                            </tr>
                                            <?php
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-md-none p-3">
                                    <div class="text-center text-muted mb-4 animate__animated animate__fadeInUp">
                                        <small><i class="bx bx-mobile me-1"></i> Geser ke bawah untuk melihat laporan
                                            Anda</small>
                                    </div>

                                    <?php
                                    if (count($laporan_data) > 0) {
                                        $colors = ['primary', 'warning', 'info', 'success', 'danger'];
                                        $color_index = 0;
                                        foreach ($laporan_data as $row_mobile) {
                                            $current_color = $colors[$color_index % count($colors)];
                                            $color_index++;
                                            $hari_indonesia = [
                                                'Sunday' => 'Minggu',
                                                'Monday' => 'Senin',
                                                'Tuesday' => 'Selasa',
                                                'Wednesday' => 'Rabu',
                                                'Thursday' => 'Kamis',
                                                'Friday' => 'Jumat',
                                                'Saturday' => 'Sabtu'
                                            ];
                                            $nama_hari_inggris_mobile = date('l', strtotime($row_mobile['tanggal']));
                                            $formatted_date_mobile = $hari_indonesia[$nama_hari_inggris_mobile] . ', ' . date('d F Y', strtotime($row_mobile['tanggal']));
                                    ?>
                                    <div
                                        class="card mb-4 shadow-lg border-start border-4 border-<?= $current_color ?> rounded-3 animate__animated animate__fadeInUp">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="mb-1 text-<?= $current_color ?>"><i
                                                            class="bx bx-calendar-event me-1"></i>
                                                        <strong><?= htmlspecialchars($formatted_date_mobile) ?></strong>
                                                    </h6>
                                                    <span class="badge bg-label-<?= $current_color ?>"><i
                                                            class="bx bx-file me-1"></i> Laporan
                                                        #<?= htmlspecialchars($row_mobile['id_jurnal_harian']) ?></span>
                                                </div>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item"
                                                            href="master_kegiatan_harian_edit.php?id=<?= htmlspecialchars($row_mobile['id_jurnal_harian']) ?>">
                                                            <i class="bx bx-edit-alt me-1"></i> Edit Laporan
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                            onclick="confirmDeleteKegiatanHarian('<?= htmlspecialchars($row_mobile['id_jurnal_harian']) ?>', '<?= htmlspecialchars($formatted_date_mobile) ?>')">
                                                            <i class="bx bx-trash me-1"></i> Hapus
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-2">
                                                <strong class="text-dark"><i class="bx bx-task me-1"></i>
                                                    Pekerjaan:</strong><br>
                                                <?= nl2br(htmlspecialchars($row_mobile['pekerjaan'])) ?>
                                            </div>
                                            <div class="mb-0 text-wrap">
                                                <strong class="text-dark"><i class="bx bx-info-circle me-1"></i>
                                                    Catatan:</strong><br>
                                                <?= nl2br(htmlspecialchars($row_mobile['catatan'])) ?>
                                            </div>
                                            <div class="d-flex justify-content-end mt-3">
                                                <small class="text-muted"><i class="bx bx-calendar-check me-1"></i>
                                                    Dilaporkan:
                                                    <?= date('d F Y, H:i', strtotime($row_mobile['tanggal'])) ?>
                                                    WIB</small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                        }
                                    } else {
                                        ?>
                                    <div class="alert alert-info text-center mt-5 py-4 animate__animated animate__fadeInUp animate__delay-0-3s"
                                        role="alert" style="border-radius: 8px;">
                                        <h5 class="alert-heading mb-3"><i class="bx bx-list-plus bx-lg text-info"></i>
                                        </h5>
                                        <p class="mb-3">Belum ada laporan kegiatan yang tercatat di sini.</p>
                                        <p class="mb-0">
                                            Ayo, <a href="master_kegiatan_harian_add.php"
                                                class="alert-link fw-bold">tambahkan laporan pertama Anda</a> sekarang!
                                        </p>
                                    </div>
                                    <?php
                                    }
                                    $koneksi->close();
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
    function confirmDeleteKegiatanHarian(id, tanggal) {
        Swal.fire({
            title: 'Konfirmasi Hapus Laporan Harian',
            html: "Apakah Anda yakin ingin menghapus laporan kegiatan pada tanggal <strong>" + tanggal +
                "</strong>?<br>Tindakan ini tidak dapat dibatalkan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus Sekarang!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'master_kegiatan_harian_delete.php?id=' + id;
            }
        });
    }
    </script>
    <?php include './partials/script.php'; ?>
</body>

</html>