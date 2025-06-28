<?php
session_start();

$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';

if (!$is_siswa && !$is_admin) {
    if (isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in') {
        header('Location: ../halaman_guru.php');
        exit();
    } else {
        header('Location: ../login.php');
        exit();
    }
}

include 'partials/db.php';

$id_siswa_filter = null;
$siswa_nama_display = "";

if ($is_siswa) {
    $id_siswa_filter = $_SESSION['id_siswa'] ?? null;
    $siswa_nama_display = $_SESSION['siswa_nama'] ?? "Saya";
} elseif ($is_admin) {
    if (isset($_GET['siswa_id']) && !empty($_GET['siswa_id'])) {
        $id_siswa_filter = $_GET['siswa_id'];

        $stmt_nama_siswa = $koneksi->prepare("SELECT nama_siswa FROM siswa WHERE id_siswa = ?");
        if ($stmt_nama_siswa) {
            $stmt_nama_siswa->bind_param("i", $id_siswa_filter);
            $stmt_nama_siswa->execute();
            $res_nama_siswa = $stmt_nama_siswa->get_result();
            if ($res_nama_siswa->num_rows > 0) {
                $siswa_nama_display = "Siswa: " . htmlspecialchars($res_nama_siswa->fetch_assoc()['nama_siswa']);
            } else {
                $siswa_nama_display = "Siswa (ID tidak ditemukan)";
            }
            $stmt_nama_siswa->close();
        } else {
            error_log("Failed to prepare statement for siswa name: " . $koneksi->error);
            $siswa_nama_display = "Siswa (Error)";
        }
    } else {
        $siswa_nama_display = "Seluruh Siswa";
    }
}


$keyword = $_GET['keyword'] ?? '';
$laporan_tugas = [];

$sql = "SELECT jk.id_jurnal_kegiatan, jk.nama_pekerjaan, jk.perencanaan_kegiatan, jk.pelaksanaan_kegiatan, jk.catatan_instruktur, jk.gambar, jk.tanggal_laporan, jk.siswa_id, s.nama_siswa
        FROM jurnal_kegiatan jk
        LEFT JOIN siswa s ON jk.siswa_id = s.id_siswa";

$params = [];
$types = "";
$where_clauses = [];

if ($id_siswa_filter !== null && $id_siswa_filter !== "") {
    $where_clauses[] = "jk.siswa_id = ?";
    $params[] = $id_siswa_filter;
    $types .= "i";
}

if (!empty($keyword)) {
    $where_clauses[] = "(jk.nama_pekerjaan LIKE ? OR jk.perencanaan_kegiatan LIKE ? OR jk.pelaksanaan_kegiatan LIKE ? OR jk.catatan_instruktur LIKE ?)";
    $params[] = "%" . $keyword . "%";
    $params[] = "%" . $keyword . "%";
    $params[] = "%" . $keyword . "%";
    $params[] = "%" . $keyword . "%";
    $types .= "ssss";
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY jk.tanggal_laporan DESC";

$stmt = $koneksi->prepare($sql);

if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $laporan_tugas = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    error_log("Failed to prepare statement: " . $koneksi->error);
}

$koneksi->close();
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/"
    data-template="vertical-menu-template-free">

<?php include 'partials/head.php' ?>

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
                                <span class="text-muted fw-light">
                                    <?php if ($is_siswa) echo "Siswa /";
                                    elseif ($is_admin) echo "Admin /"; ?>
                                </span> Tugas Proyek
                            </h4>
                            <i class="fas fa-tasks fa-2x text-info" style="opacity: 0.6;"></i>
                        </div>

                        <div class="card mb-4 shadow-lg">
                            <div
                                class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 p-4">
                                <div class="d-flex gap-2 w-100 w-md-auto">
                                    <a href="<?php echo $is_siswa ? 'dashboard_siswa.php' : 'index.php'; ?>"
                                        class="btn btn-outline-secondary w-100">
                                        <i class="bx bx-arrow-back me-1"></i> Kembali
                                    </a>
                                    <a href="master_tugas_project_add.php<?php echo ($is_admin && !empty($id_siswa_filter)) ? '?siswa_id=' . htmlspecialchars($id_siswa_filter) : ''; ?>"
                                        class="btn btn-primary w-100">
                                        <i class="bx bx-plus me-1"></i> Tambah Laporan
                                    </a>
                                </div>
                                <div class="d-flex gap-2 w-100 w-md-auto">
                                    <?php
                                    $current_query_params = [];
                                    if (!empty($keyword)) {
                                        $current_query_params['keyword'] = $keyword;
                                    }
                                    if ($id_siswa_filter !== null && $id_siswa_filter !== "") {
                                        $current_query_params['siswa_id'] = $id_siswa_filter;
                                    }
                                    $pdf_link_query_string = !empty($current_query_params) ? '?' . http_build_query($current_query_params) : '';
                                    ?>
                                    <a href="generate_tugas_pdf.php<?= $pdf_link_query_string ?>"
                                        class="btn btn-outline-danger w-100" target="_blank">
                                        <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
                                    </a>
                                    <button type="button" class="btn btn-outline-success w-100">
                                        <i class="bx bxs-file-excel me-1"></i> Ekspor Excel
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4 shadow-sm">
                            <div class="card-body">
                                <form method="GET" action="">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="keyword"
                                            placeholder="Cari laporan berdasarkan nama proyek..."
                                            value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
                                        <?php if ($is_admin && !empty($id_siswa_filter)): ?>
                                        <input type="hidden" name="siswa_id"
                                            value="<?= htmlspecialchars($id_siswa_filter) ?>">
                                        <?php endif; ?>
                                        <button class="btn btn-primary" type="submit">
                                            <i class="bx bx-search"></i> Cari
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Daftar Laporan Tugas Proyek
                                    <?= htmlspecialchars($siswa_nama_display) ?></h5>
                                <small class="text-muted">Total: <?php echo count($laporan_tugas); ?> Laporan</small>
                            </div>
                            <div class="card-body p-0">
                                <?php if (!empty($laporan_tugas)): ?>
                                <div class="table-responsive text-nowrap d-none d-md-block"
                                    style="min-height: calc(100vh - 450px); overflow-y: auto;">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Tanggal</th>
                                                <th>Nama Pekerjaan / Proyek</th>
                                                <th>Perencanaan</th>
                                                <th>Pelaksanaan</th>
                                                <th>Gambar</th>
                                                <th>Catatan Instruktur</th>
                                                <?php if ($is_admin && ($id_siswa_filter === null || $id_siswa_filter === "")): ?>
                                                <th>Siswa</th>
                                                <?php endif; ?>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            <?php $no = 1; ?>
                                            <?php foreach ($laporan_tugas as $laporan): ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo htmlspecialchars(date('d M Y', strtotime($laporan['tanggal_laporan']))); ?>
                                                </td>
                                                <td><strong><?php echo htmlspecialchars($laporan['nama_pekerjaan']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars(substr($laporan['perencanaan_kegiatan'], 0, 50)) . '...'; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars(substr($laporan['pelaksanaan_kegiatan'], 0, 50)) . '...'; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($laporan['gambar'])): ?>
                                                    <a href="images/<?php echo htmlspecialchars($laporan['gambar']); ?>"
                                                        target="_blank">
                                                        <img src="images/<?php echo htmlspecialchars($laporan['gambar']); ?>"
                                                            alt="Gambar Proyek"
                                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">
                                                    </a>
                                                    <?php else: ?>
                                                    -
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars(substr($laporan['catatan_instruktur'] ?? '', 0, 50)) . '...'; ?>
                                                </td>
                                                <?php if ($is_admin && ($id_siswa_filter === null || $id_siswa_filter === "")): ?>
                                                <td><?= htmlspecialchars($laporan['nama_siswa'] ?? '-') ?></td>
                                                <?php endif; ?>
                                                <td>
                                                    <div class="dropdown">
                                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                            data-bs-toggle="dropdown">
                                                            <i class="bx bx-dots-vertical-rounded"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item"
                                                                href="master_tugas_project_edit.php?id=<?php echo $laporan['id_jurnal_kegiatan']; ?>">
                                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                                            </a>
                                                            <a class="dropdown-item text-danger"
                                                                href="javascript:void(0);"
                                                                onclick="confirmDeleteLaporanTugas('<?php echo $laporan['id_jurnal_kegiatan']; ?>', '<?php echo htmlspecialchars(addslashes($laporan['nama_pekerjaan'])); ?>', '<?php echo htmlspecialchars($laporan['siswa_id']); ?>')">
                                                                <i class="bx bx-trash me-1"></i> Hapus
                                                            </a>
                                                            <div class="dropdown-divider"></div>
                                                            <a class="dropdown-item"
                                                                href="master_tugas_project_print.php?id=<?php echo $laporan['id_jurnal_kegiatan']; ?>"
                                                                target="_blank">
                                                                <i class="bx bx-printer me-1"></i> Lihat & Print
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-md-none p-3">
                                    <?php $no_mobile = 1; ?>
                                    <?php foreach ($laporan_tugas as $laporan): ?>
                                    <div class="card mb-3 shadow-sm border-start border-4 border-primary">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="text-primary mb-0">
                                                    <strong><?php echo htmlspecialchars($laporan['nama_pekerjaan']); ?></strong>
                                                </h6>
                                                <div class="dropdown">
                                                    <button type="button"
                                                        class="btn btn-sm btn-icon-only p-0 dropdown-toggle hide-arrow"
                                                        data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item"
                                                            href="master_tugas_project_edit.php?id=<?php echo $laporan['id_jurnal_kegiatan']; ?>"><i
                                                                class="bx bx-edit-alt me-1"></i> Edit</a>
                                                        <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                            onclick="confirmDeleteLaporanTugas('<?php echo $laporan['id_jurnal_kegiatan']; ?>', '<?php echo htmlspecialchars(addslashes($laporan['nama_pekerjaan'])); ?>', '<?php echo htmlspecialchars($laporan['siswa_id']); ?>')"><i
                                                                class="bx bx-trash me-1"></i> Hapus</a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item"
                                                            href="master_tugas_project_print.php?id=<?php echo $laporan['id_jurnal_kegiatan']; ?>"
                                                            target="_blank"><i class="bx bx-printer me-1"></i> Lihat &
                                                            Print</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <small class="text-muted mb-2 d-block"><i class="bx bx-calendar me-1"></i>
                                                <?php echo htmlspecialchars(date('d M Y', strtotime($laporan['tanggal_laporan']))); ?></small>
                                            <?php if (!empty($laporan['gambar'])): ?>
                                            <div class="mb-2 text-center">
                                                <a href="images/<?php echo htmlspecialchars($laporan['gambar']); ?>"
                                                    target="_blank">
                                                    <img src="images/<?php echo htmlspecialchars($laporan['gambar']); ?>"
                                                        alt="Bukti Kegiatan" class="img-fluid rounded shadow-sm"
                                                        style="max-height: 200px; object-fit: contain;">
                                                </a>
                                            </div>
                                            <?php endif; ?>

                                            <p class="mb-2">
                                                <strong>Perencanaan:</strong><br><?php echo nl2br(htmlspecialchars($laporan['perencanaan_kegiatan'])); ?>
                                            </p>
                                            <p class="mb-0">
                                                <strong>Pelaksanaan:</strong><br><?php echo nl2br(htmlspecialchars($laporan['pelaksanaan_kegiatan'])); ?>
                                            </p>
                                            <p class="mb-0 text-muted small mt-2"><strong>Catatan
                                                    Instruktur:</strong><br><?php echo nl2br(htmlspecialchars($laporan['catatan_instruktur'] ?? 'Belum ada catatan')); ?>
                                            </p>
                                            <?php if ($is_admin && ($id_siswa_filter === null || $id_siswa_filter === "")): ?>
                                            <p class="mb-0 text-muted small mt-2"><strong>Siswa:</strong>
                                                <?= htmlspecialchars($laporan['nama_siswa'] ?? '-') ?>
                                            </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <?php else: ?>
                                <div class="alert alert-warning text-center mt-4 mx-3" role="alert"
                                    style="min-height: 200px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                    <h5 class="alert-heading"><i class="bx bx-info-circle"></i> Data Tidak Ditemukan
                                    </h5>
                                    <p class="mb-0">
                                        <?php if (!empty($keyword)): ?>
                                        Tidak ada laporan yang cocok dengan kata kunci
                                        "<strong><?php echo htmlspecialchars($keyword); ?></strong>".
                                        <?php elseif ($is_siswa): ?>
                                        Anda belum memiliki laporan tugas proyek yang tercatat. Silakan tambahkan
                                        laporan pertama Anda.
                                        <?php elseif ($is_admin && !empty($id_siswa_filter)): ?>
                                        Siswa ini belum memiliki laporan tugas proyek.
                                        <?php elseif ($is_admin && ($id_siswa_filter === null || $id_siswa_filter === "")): ?>
                                        Tidak ada laporan tugas proyek yang ditemukan di sistem.
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function confirmDeleteLaporanTugas(id, namaProyek, siswaId) {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            html: "Yakin ingin menghapus laporan <strong>" + namaProyek +
                "</strong>?<br><small>Tindakan ini tidak dapat dibatalkan!</small>",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Tambahkan siswaId ke URL redirect untuk admin
                let deleteUrl = 'master_tugas_project_delete.php?id=' + id;
                // Hanya tambahkan siswa_id jika bukan siswa yang login atau jika siswa_id berbeda dari sesi
                // Ini untuk memastikan admin bisa redirect ke halaman siswa yang benar
                <?php if ($is_admin): ?>
                deleteUrl += '&redirect_siswa_id=' + siswaId;
                <?php endif; ?>
                window.location.href = deleteUrl;
            }
        });
    }
    </script>

    <?php include './partials/script.php'; ?>
</body>

</html>