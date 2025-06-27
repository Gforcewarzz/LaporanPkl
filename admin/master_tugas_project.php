<?php
// 1. Mulai sesi di baris paling atas untuk mengakses ID siswa yang login
session_start();

// 2. Keamanan: Periksa apakah siswa sudah login.
// Jika tidak ada sesi 'id_siswa', redirect (arahkan) ke halaman login.
if (!isset($_SESSION['id_siswa'])) {
    header("Location: login.php");
    exit(); // Hentikan eksekusi skrip lebih lanjut
}

// 3. Ambil ID siswa yang sedang login dari sesi
$id_siswa_login = $_SESSION['id_siswa'];

// Sertakan file koneksi database
include 'partials/db.php'; // Sesuaikan path ini jika db.php ada di lokasi lain

// Logika Pencarian
$keyword = $_GET['keyword'] ?? ''; // Ambil kata kunci dengan aman
$laporan_tugas = []; // Buat array kosong sebagai default

// Siapkan query dasar, tambahkan kolom 'gambar' dan 'tanggal_laporan'
$sql = "SELECT id_jurnal_kegiatan, nama_pekerjaan, perencanaan_kegiatan, pelaksanaan_kegiatan, catatan_instruktur, gambar, tanggal_laporan 
        FROM jurnal_kegiatan WHERE siswa_id = ?";
$params = [$id_siswa_login];
$types = "i"; // 'i' untuk integer

// Jika ada kata kunci pencarian, tambahkan kondisi LIKE
if (!empty($keyword)) {
    $sql .= " AND nama_pekerjaan LIKE ?";
    $params[] = "%" . $keyword . "%"; // Tambahkan parameter keyword
    $types .= "s"; // 's' untuk string
}

$sql .= " ORDER BY tanggal_laporan DESC"; // Urutkan berdasarkan tanggal terbaru

// Menggunakan prepared statement untuk keamanan
$stmt = $koneksi->prepare($sql);

if ($stmt) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $laporan_tugas = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    // Handle error prepared statement
    error_log("Failed to prepare statement: " . $koneksi->error);
    // Mungkin tampilkan pesan error generik ke user atau log
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
                                <span class="text-muted fw-light">Laporan /</span> Tugas Proyek
                            </h4>
                            <i class="fas fa-tasks fa-2x text-info" style="opacity: 0.6;"></i>
                        </div>

                        <div class="card mb-4 shadow-lg">
                            <div
                                class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 p-4">
                                <div class="d-flex gap-2 w-100 w-md-auto">
                                    <a href="index.php" class="btn btn-outline-secondary w-100">
                                        <i class="bx bx-arrow-back me-1"></i> Kembali
                                    </a>
                                    <a href="master_tugas_project_add.php" class="btn btn-primary w-100">
                                        <i class="bx bx-plus me-1"></i> Tambah Laporan
                                    </a>
                                </div>
                                <div class="d-flex gap-2 w-100 w-md-auto">
                                    <?php
                                    // Ambil keyword dari URL untuk link PDF
                                    $keyword_for_pdf = $_GET['keyword'] ?? '';
                                    ?>
                                    <a href="generate_tugas_pdf.php?siswa_id=<?php echo $id_siswa_login; ?><?= !empty($keyword_for_pdf) ? '&keyword=' . urlencode($keyword_for_pdf) : '' ?>"
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
                                        <button class="btn btn-primary" type="submit">
                                            <i class="bx bx-search"></i> Cari
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Daftar Laporan Tugas Proyek Saya</h5>
                                <small class="text-muted">Total: <?php echo count($laporan_tugas); ?> Laporan</small>
                            </div>
                            <div class="card-body p-0">
                                <?php if (!empty($laporan_tugas)): ?>
                                <div class="table-responsive text-nowrap d-none d-md-block">
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
                                                                onclick="confirmDeleteLaporanTugas('<?php echo $laporan['id_jurnal_kegiatan']; ?>', '<?php echo htmlspecialchars(addslashes($laporan['nama_pekerjaan'])); ?>')">
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
                                                            onclick="confirmDeleteLaporanTugas('<?php echo $laporan['id_jurnal_kegiatan']; ?>', '<?php echo htmlspecialchars(addslashes($laporan['nama_pekerjaan'])); ?>')"><i
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
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <?php else: ?>
                                <div class="alert alert-warning text-center mt-4 mx-3" role="alert">
                                    <h5 class="alert-heading"><i class="bx bx-info-circle"></i> Data Tidak Ditemukan
                                    </h5>
                                    <p class="mb-0">
                                        <?php if (!empty($keyword)): ?>
                                        Tidak ada laporan yang cocok dengan kata kunci
                                        "<strong><?php echo htmlspecialchars($keyword); ?></strong>".
                                        <?php else: ?>
                                        Anda belum memiliki laporan tugas proyek yang tercatat. Silakan tambahkan
                                        laporan pertama Anda.
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
    function confirmDeleteLaporanTugas(id, namaProyek) {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            html: "Yakin ingin menghapus laporan <strong>" + namaProyek +
                "</strong>?<br><small>Tindakan ini tidak dapat dibatalkan!</small>",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'master_tugas_project_delete.php?id=' + id;
            }
        });
    }
    </script>

    <?php include './partials/script.php'; ?>
</body>

</html>