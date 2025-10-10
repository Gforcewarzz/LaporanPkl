<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

include 'partials/db.php';

// --- FUNGSI & KEAMANAN ---
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';

// Keamanan: Hanya siswa dan admin yang boleh mengakses halaman ini
if (!$is_siswa && !$is_admin) {
    header('Location: ../login.php');
    exit();
}

// Menyiapkan ID siswa untuk form
$siswa_id_for_form = $is_siswa ? ($_SESSION['id_siswa'] ?? '') : '';
$selected_siswa_id_from_url = '';

if ($is_admin && isset($_GET['siswa_id']) && !empty($_GET['siswa_id'])) {
    $selected_siswa_id_from_url = htmlspecialchars($_GET['siswa_id']);
}

// Ambil daftar siswa untuk dropdown jika admin yang login
$siswa_list = [];
if ($is_admin) {
    $result = $koneksi->query("SELECT id_siswa, nama_siswa, kelas FROM siswa ORDER BY nama_siswa ASC");
    if ($result) {
        $siswa_list = $result->fetch_all(MYSQLI_ASSOC);
    }
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
                                <span class="text-muted fw-light">Jurnal Harian /</span> Tambah Laporan
                            </h4>
                        </div>

                        <div class="card bg-gradient-primary-to-secondary text-white mb-4 shadow-lg animate__animated animate__fadeInDown"
                            style="border-radius: 12px; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title text-white mb-1">Formulir Laporan Harian</h5>
                                    <p class="card-text text-white-75 small">Catat setiap progresmu dengan detail di
                                        sini.</p>
                                </div>
                                <i class='bx bxs-file-plus display-4 text-white' style="opacity: 0.5;"></i>
                            </div>
                        </div>

                        <div class="card shadow-lg animate__animated animate__fadeInUp">
                            <div class="card-body p-4">
                                <form action="master_kegiatan_harian_add_act.php" method="POST">

                                    <?php if ($is_admin): ?>
                                        <div class="mb-4">
                                            <label for="selected_siswa_id" class="form-label fw-semibold"><i
                                                    class="bx bx-user-check me-1"></i>Pilih Siswa</label>
                                            <select class="form-select" id="selected_siswa_id" name="selected_siswa_id"
                                                required>
                                                <option value="" disabled
                                                    <?= empty($selected_siswa_id_from_url) ? 'selected' : '' ?>>-- Pilih
                                                    salah satu siswa --</option>
                                                <?php foreach ($siswa_list as $siswa): ?>
                                                    <option value="<?= htmlspecialchars($siswa['id_siswa']) ?>"
                                                        <?= ($selected_siswa_id_from_url == $siswa['id_siswa']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($siswa['nama_siswa']) ?>
                                                        (<?= htmlspecialchars($siswa['kelas']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mb-4">
                                        <label for="tanggal_kegiatan" class="form-label fw-semibold"><i
                                                class="bx bx-calendar me-1"></i>Tanggal Kegiatan</label>
                                        <input type="date" class="form-control" id="tanggal_kegiatan" name="tanggal"
                                            required value="<?php echo date('Y-m-d'); ?>">
                                    </div>

                                    <div class="mb-4">
                                        <label for="pekerjaan" class="form-label fw-semibold"><i
                                                class="bx bx-briefcase-alt-2 me-1"></i>Uraian Pekerjaan</label>
                                        <textarea class="form-control" id="pekerjaan" name="pekerjaan" rows="6"
                                            placeholder="Jelaskan secara rinci pekerjaan yang Anda lakukan..."
                                            required></textarea>
                                    </div>

                                    <div class="mb-4">
                                        <label for="catatan" class="form-label fw-semibold"><i
                                                class="bx bx-notepad me-1"></i>Catatan Tambahan (Opsional)</label>
                                        <textarea class="form-control" id="catatan" name="catatan" rows="3"
                                            placeholder="Tulis catatan jika ada, misalnya kendala atau hal baru yang dipelajari..."></textarea>
                                    </div>

                                    <?php if ($is_siswa): ?>
                                        <input type="hidden" name="selected_siswa_id"
                                            value="<?= htmlspecialchars($siswa_id_for_form); ?>">
                                    <?php endif; ?>

                                    <div class="pt-2 d-flex justify-content-end gap-2">
                                        <a href="master_kegiatan_harian.php" class="btn btn-outline-secondary">
                                            <i class="bx bx-x me-1"></i>Batal
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-save me-1"></i> Simpan Laporan
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