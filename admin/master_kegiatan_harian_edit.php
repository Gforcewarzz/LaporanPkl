<?php
session_start();
include 'partials/db.php'; // pastikan $koneksi terdefinisi

$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru  = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

/* ===== Akses: hanya siswa & admin ===== */
if (!$is_siswa && !$is_admin) {
    if ($is_guru) {
        header('Location: ../halaman_guru.php');
        exit();
    }
    header('Location: ../login.php');
    exit();
}

/* ===== Ambil ID laporan ===== */
$id_jurnal_harian = $_GET['id'] ?? null;
if (empty($id_jurnal_harian)) {
    header('Location: master_kegiatan_harian.php');
    exit();
}

/* ===== Ambil data laporan ===== */
$query_laporan  = "SELECT id_jurnal_harian, tanggal, pekerjaan, catatan, siswa_id
                   FROM jurnal_harian
                   WHERE id_jurnal_harian = ?";
$stmt_laporan   = $koneksi->prepare($query_laporan);
if (!$stmt_laporan) {
    error_log("Failed to prepare statement for fetching data: " . $koneksi->error);
    header('Location: master_kegiatan_harian.php');
    exit();
}
$stmt_laporan->bind_param("i", $id_jurnal_harian);
$stmt_laporan->execute();
$result_laporan = $stmt_laporan->get_result();
$laporan_data   = $result_laporan->fetch_assoc();
$stmt_laporan->close();

if (!$laporan_data) {
    header('Location: master_kegiatan_harian.php');
    exit();
}

/* ===== Otorisasi edit ===== */
// siswa hanya boleh edit miliknya sendiri
if ($is_siswa && $laporan_data['siswa_id'] != ($_SESSION['id_siswa'] ?? null)) {
    $_SESSION['alert_message'] = 'Anda tidak memiliki izin untuk mengedit laporan siswa lain.';
    $_SESSION['alert_type']    = 'error';
    $_SESSION['alert_title']   = 'Akses Ditolak!';
    header('Location: master_kegiatan_harian.php');
    exit();
}

/* ===== (Opsional) Ambil nama siswa untuk header jika admin ===== */
$siswa_nama = "";
if ($is_admin) {
    $query_siswa_nama = "SELECT nama_siswa FROM siswa WHERE id_siswa = ?";
    $stmt_siswa_nama  = $koneksi->prepare($query_siswa_nama);
    if ($stmt_siswa_nama) {
        $stmt_siswa_nama->bind_param("i", $laporan_data['siswa_id']);
        $stmt_siswa_nama->execute();
        $res_siswa_nama = $stmt_siswa_nama->get_result();
        if ($res_siswa_nama->num_rows > 0) {
            $siswa_nama = htmlspecialchars($res_siswa_nama->fetch_assoc()['nama_siswa']);
        }
        $stmt_siswa_nama->close();
    }
}

$koneksi->close(); // selesai ambil data
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
                                <span class="text-muted fw-light">Jurnal PKL Harian /</span> Edit Kegiatan
                            </h4>
                            <i class="fas fa-edit fa-2x text-info animate__animated animate__fadeInRight"
                                style="opacity: 0.6;"></i>
                        </div>

                        <div class="card bg-gradient-primary-to-secondary text-white mb-4 shadow-lg animate__animated animate__fadeInDown"
                            style="border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                            <div
                                class="card-body p-4 d-flex flex-column flex-sm-row justify-content-between align-items-center">
                                <div class="text-center text-sm-start mb-3 mb-sm-0">
                                    <h5 class="card-title text-white mb-1">
                                        Edit Jurnal PKL Harian
                                        <?= ($is_admin && !empty($siswa_nama)) ? "Siswa: {$siswa_nama}" : "Anda"; ?>
                                    </h5>
                                    <p class="card-text text-white-75 small">Perbarui data Jurnal PKL Harian ini.</p>
                                </div>
                                <div class="text-center text-sm-end position-relative">
                                    <div class="rounded-circle bg-white d-flex justify-content-center align-items-center animate__animated animate__zoomIn animate__delay-0-5s"
                                        style="width: 80px; height: 80px; opacity: 0.2; position: relative; overflow: hidden; z-index: 1;">
                                        <i class="bx bx-pencil bx-lg text-primary"
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

                        <div class="card shadow-lg animate__animated animate__fadeInUp" style="border-radius: 10px;">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">Formulir Edit Jurnal PKL Harian</h5>
                                <small class="text-muted">Pastikan semua perubahan sudah benar.</small>
                            </div>
                            <div class="card-body p-4">
                                <form action="master_kegiatan_harian_edit_act.php" method="POST">
                                    <input type="hidden" name="id_jurnal_harian"
                                        value="<?= htmlspecialchars($laporan_data['id_jurnal_harian']) ?>">
                                    <input type="hidden" name="siswa_id_original"
                                        value="<?= htmlspecialchars($laporan_data['siswa_id']) ?>">
                                    <?php if ($is_admin): ?>
                                    <input type="hidden" name="redirect_siswa_id"
                                        value="<?= htmlspecialchars($laporan_data['siswa_id']) ?>">
                                    <?php endif; ?>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-2s">
                                        <label for="tanggal_kegiatan" class="form-label fw-bold">
                                            <i class="bx bx-calendar me-1"></i> Hari/Tanggal Kegiatan:
                                        </label>
                                        <input type="date" class="form-control" id="tanggal_kegiatan" name="tanggal"
                                            required value="<?= htmlspecialchars($laporan_data['tanggal']) ?>" readonly>
                                    </div>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-3s">
                                        <label for="pekerjaan" class="form-label fw-bold">
                                            <i class="bx bx-briefcase-alt me-1"></i> Deskripsi Pekerjaan:
                                        </label>
                                        <textarea class="form-control" id="pekerjaan" name="pekerjaan" rows="5"
                                            placeholder="Tulis deskripsi pekerjaan..."
                                            required><?= htmlspecialchars($laporan_data['pekerjaan'] ?? '') ?></textarea>
                                    </div>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-4s">
                                        <label for="catatan" class="form-label fw-bold">
                                            <i class="bx bx-notepad me-1"></i> Catatan Tambahan (Opsional):
                                        </label>
                                        <textarea class="form-control" id="catatan" name="catatan" rows="3"
                                            placeholder="Tulis catatan bila perlu..."><?= htmlspecialchars($laporan_data['catatan'] ?? '') ?></textarea>
                                    </div>

                                    <hr class="my-4">

                                    <div
                                        class="d-flex flex-column flex-sm-row justify-content-end gap-2 animate__animated animate__fadeInUp animate__delay-0-5s">
                                        <button type="submit" class="btn btn-primary w-100 w-sm-auto">
                                            <i class="bx bx-save me-1"></i> Simpan Perubahan
                                        </button>
                                        <button type="reset" class="btn btn-outline-secondary w-100 w-sm-auto">
                                            <i class="bx bx-refresh me-1"></i> Reset Form
                                        </button>
                                        <a href="master_kegiatan_harian.php"
                                            class="btn btn-outline-secondary w-100 w-sm-auto">
                                            <i class="bx bx-arrow-back me-1"></i> Kembali
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div><!-- /container -->
                </div><!-- /content-wrapper -->
                <div class="layout-overlay layout-menu-toggle"></div>
            </div><!-- /layout-page -->
        </div><!-- /layout-container -->
    </div><!-- /layout-wrapper -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include './partials/script.php'; ?>
</body>

</html>