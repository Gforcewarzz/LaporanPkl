<?php
session_start();
include 'partials/db.php';

if (!isset($_SESSION['id_siswa'])) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$siswa_id = $_SESSION['id_siswa'];

// Ambil data kegiatan dari database berdasarkan ID dan siswa
$query = "SELECT * FROM jurnal_harian WHERE id_jurnal_harian = $id AND siswa_id = $siswa_id";
$result = mysqli_query($koneksi, $query);
$activityData = mysqli_fetch_assoc($result);

// Data untuk datalist (opsional)
$pekerjaan_options = [
    'Membantu konfigurasi server',
    'Melakukan instalasi software',
    'Riset teknologi baru',
    'Membuat dokumentasi proyek',
    'Debugging kode program',
    'Mendesain UI/UX'
];
$catatan_options = [
    'Kendala teknis berhasil diatasi.',
    'Perlu follow up besok.',
    'Sangat memahami materi yang diberikan.',
    'Menemukan bug minor.',
    'Belajar fitur baru di aplikasi X.'
];
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
                                <span class="text-muted fw-light">Laporan Harian /</span> Edit Laporan
                            </h4>
                            <i class="fas fa-edit fa-2x text-info animate__animated animate__fadeInRight"
                                style="opacity: 0.6;"></i>
                        </div>

                        <?php if ($activityData): ?>
                            <div class="card shadow-lg animate__animated animate__fadeInUp" style="border-radius: 10px;">
                                <div class="card-header border-bottom">
                                    <h5 class="card-title mb-0">Formulir Edit Laporan Kegiatan</h5>
                                    <small class="text-muted">Isi kolom yang ingin diubah, sisanya biarkan.</small>
                                </div>
                                <div class="card-body p-4">
                                    <form action="master_kegiatan_harian_edit_act.php" method="POST">
                                        <input type="hidden" name="id_jurnal_harian"
                                            value="<?php echo $activityData['id_jurnal_harian']; ?>">

                                        <div class="mb-3">
                                            <label for="tanggal" class="form-label fw-bold">
                                                <i class="bx bx-calendar me-1"></i> Hari/Tanggal Kegiatan:
                                            </label>
                                            <input type="date" class="form-control" id="tanggal" name="tanggal"
                                                value="<?php echo $activityData['tanggal']; ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="pekerjaan" class="form-label fw-bold">
                                                <i class="bx bx-briefcase-alt me-1"></i> Deskripsi Pekerjaan:
                                            </label>
                                            <textarea class="form-control" id="pekerjaan" name="pekerjaan" rows="5"
                                                required><?php echo htmlspecialchars($activityData['pekerjaan']); ?></textarea>
                                            <datalist id="datalistPekerjaan">
                                                <?php foreach ($pekerjaan_options as $p): ?>
                                                    <option value="<?php echo htmlspecialchars($p); ?>">
                                                    <?php endforeach; ?>
                                            </datalist>
                                        </div>

                                        <div class="mb-3">
                                            <label for="catatan" class="form-label fw-bold">
                                                <i class="bx bx-notepad me-1"></i> Catatan Tambahan (Opsional):
                                            </label>
                                            <textarea class="form-control" id="catatan" name="catatan"
                                                rows="3"><?php echo htmlspecialchars($activityData['catatan']); ?></textarea>
                                            <datalist id="datalistCatatan">
                                                <?php foreach ($catatan_options as $c): ?>
                                                    <option value="<?php echo htmlspecialchars($c); ?>">
                                                    <?php endforeach; ?>
                                            </datalist>
                                        </div>

                                        <hr class="my-4">

                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="master_kegiatan_harian.php" class="btn btn-outline-secondary">
                                                <i class="bx bx-arrow-back me-1"></i> Batal
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-save me-1"></i> Simpan Perubahan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">Data tidak ditemukan atau tidak valid.</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="layout-overlay layout-menu-toggle"></div>
            </div>
        </div>
    </div>

    <!-- Animate & Script -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <?php include 'partials/script.php'; ?>
</body>

</html>