<?php
session_start();
include 'partials/db.php';

// --- FUNGSI & KEAMANAN ---
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';

// Akses: hanya siswa & admin
if (!$is_siswa && !$is_admin) {
    header('Location: ../login.php');
    exit();
}

// Ambil ID laporan dari URL
$id_jurnal_harian = $_GET['id'] ?? null;
if (empty($id_jurnal_harian)) {
    header('Location: master_kegiatan_harian.php');
    exit();
}

// Ambil data laporan dari database
$stmt_laporan = $koneksi->prepare("SELECT id_jurnal_harian, tanggal, pekerjaan, catatan, siswa_id FROM jurnal_harian WHERE id_jurnal_harian = ?");
$stmt_laporan->bind_param("i", $id_jurnal_harian);
$stmt_laporan->execute();
$laporan_data = $stmt_laporan->get_result()->fetch_assoc();
$stmt_laporan->close();

if (!$laporan_data) {
    // Jika data tidak ditemukan, kembali ke halaman utama
    header('Location: master_kegiatan_harian.php');
    exit();
}

// Otorisasi: Siswa hanya boleh edit laporannya sendiri
if ($is_siswa && $laporan_data['siswa_id'] != ($_SESSION['id_siswa'] ?? null)) {
    $_SESSION['alert_message'] = 'Anda tidak memiliki izin untuk mengedit laporan ini.';
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_title'] = 'Akses Ditolak!';
    header('Location: master_kegiatan_harian.php');
    exit();
}

// Ambil nama siswa untuk header jika yang login adalah admin
$siswa_nama = "";
if ($is_admin) {
    $stmt_nama = $koneksi->prepare("SELECT nama_siswa FROM siswa WHERE id_siswa = ?");
    $stmt_nama->bind_param("i", $laporan_data['siswa_id']);
    $stmt_nama->execute();
    $siswa_nama = $stmt_nama->get_result()->fetch_assoc()['nama_siswa'] ?? '';
    $stmt_nama->close();
}

$koneksi->close();
?>
<!DOCTYPE html>
<html lang="id" class="light-style layout-menu-fixed">
<?php include 'partials/head.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

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
                                <span class="text-muted fw-light">Jurnal Harian /</span> Edit Laporan
                            </h4>
                        </div>

                        <div class="card bg-gradient-primary-to-secondary text-white mb-4 shadow-lg animate__animated animate__fadeInDown"
                            style="border-radius: 12px; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title text-white mb-1">
                                        Edit Laporan Harian
                                        <?= ($is_admin && !empty($siswa_nama)) ? "Siswa: " . htmlspecialchars($siswa_nama) : ""; ?>
                                    </h5>
                                    <p class="card-text text-white-75 small">Perbarui detail laporan di bawah ini.</p>
                                </div>
                                <i class='bx bxs-edit-alt display-4 text-white' style="opacity: 0.5;"></i>
                            </div>
                        </div>

                        <div class="card shadow-lg animate__animated animate__fadeInUp">
                            <div class="card-body p-4">
                                <form action="master_kegiatan_harian_edit_act.php" method="POST">
                                    <input type="hidden" name="id_jurnal_harian"
                                        value="<?= htmlspecialchars($laporan_data['id_jurnal_harian']) ?>">
                                    <input type="hidden" name="siswa_id_original"
                                        value="<?= htmlspecialchars($laporan_data['siswa_id']) ?>">

                                    <div class="mb-4">
                                        <label for="tanggal_kegiatan" class="form-label fw-semibold"><i
                                                class="bx bx-calendar me-1"></i>Tanggal Kegiatan</label>
                                        <input type="date" class="form-control" id="tanggal_kegiatan" name="tanggal"
                                            required value="<?= htmlspecialchars($laporan_data['tanggal']) ?>">
                                    </div>

                                    <div class="mb-4">
                                        <label for="pekerjaan" class="form-label fw-semibold"><i
                                                class="bx bx-briefcase-alt-2 me-1"></i>Uraian Pekerjaan</label>
                                        <textarea class="form-control" id="pekerjaan" name="pekerjaan" rows="6"
                                            required><?= htmlspecialchars($laporan_data['pekerjaan'] ?? '') ?></textarea>
                                    </div>

                                    <div class="mb-4">
                                        <label for="catatan" class="form-label fw-semibold"><i
                                                class="bx bx-notepad me-1"></i>Catatan Tambahan (Opsional)</label>
                                        <textarea class="form-control" id="catatan" name="catatan"
                                            rows="3"><?= htmlspecialchars($laporan_data['catatan'] ?? '') ?></textarea>
                                    </div>

                                    <div class="pt-2 d-flex justify-content-end gap-2">
                                        <a href="master_kegiatan_harian.php" class="btn btn-outline-secondary">
                                            <i class="bx bx-x me-1"></i>Batal
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-save me-1"></i> Simpan Perubahan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php include './partials/footer.php'; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include './partials/script.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi Flatpickr pada input tanggal
            flatpickr("#tanggal_kegiatan", {
                dateFormat: "Y-m-d",
                maxDate: "today",
                altInput: true,
                altFormat: "l, j F Y",
            });
        });
    </script>
</body>

</html>