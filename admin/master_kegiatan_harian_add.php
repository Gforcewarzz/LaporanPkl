<?php
session_start();

// Asumsi: db.php berisi koneksi $koneksi
include 'partials/db.php';

$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';

// Logika keamanan halaman
if (!$is_siswa && !$is_admin) {
    if (isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in') {
        header('Location: ../halaman_guru.php');
        exit();
    } else {
        header('Location: ../login.php');
        exit();
    }
}

// Inisialisasi $siswa_id_for_form
// Jika siswa yang login, ambil ID dari sesi mereka
$siswa_id_for_form = $is_siswa && isset($_SESSION['id_siswa']) ? $_SESSION['id_siswa'] : '';

// Jika admin yang login dan ada parameter siswa_id di URL (misal dari daftar siswa)
// Ini memungkinkan admin untuk langsung menginput laporan untuk siswa tertentu
$selected_siswa_id_from_url = '';
$selected_siswa_name_from_url = '';

if ($is_admin) {
    if (isset($_GET['siswa_id']) && !empty($_GET['siswa_id'])) {
        $selected_siswa_id_from_url = htmlspecialchars($_GET['siswa_id']);

        // Ambil nama siswa untuk ditampilkan
        $stmt_get_siswa_name = $koneksi->prepare("SELECT nama_siswa FROM siswa WHERE id_siswa = ?");
        if ($stmt_get_siswa_name) {
            $stmt_get_siswa_name->bind_param("i", $selected_siswa_id_from_url);
            $stmt_get_siswa_name->execute();
            $result_siswa_name = $stmt_get_siswa_name->get_result();
            if ($result_siswa_name->num_rows > 0) {
                $selected_siswa_name_from_url = $result_siswa_name->fetch_assoc()['nama_siswa'];
            }
            $stmt_get_siswa_name->close();
        }
    }
}

// Ambil daftar siswa untuk dropdown jika admin yang login
$siswa_list = [];
if ($is_admin) {
    $query_siswa_list = "SELECT id_siswa, nama_siswa FROM siswa ORDER BY nama_siswa ASC";
    $result_siswa_list = mysqli_query($koneksi, $query_siswa_list);
    if ($result_siswa_list) {
        while ($row_siswa = mysqli_fetch_assoc($result_siswa_list)) {
            $siswa_list[] = $row_siswa;
        }
        mysqli_free_result($result_siswa_list);
    }
}

// Jangan lupa tutup koneksi jika tidak ada query lain setelah ini
// $koneksi->close(); 
// Catatan: Jika partials/script.php atau file lain di bawah membutuhkan $koneksi,
// biarkan terbuka dan tutup di akhir file utama atau di partials/footer.php.
// Jika $koneksi hanya untuk halaman ini, tutup di sini.
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
                                <span class="text-muted fw-light">Laporan Harian /</span> Tambah Kegiatan
                            </h4>
                            <i class="fas fa-plus-circle fa-2x text-info animate__animated animate__fadeInRight"
                                style="opacity: 0.6;"></i>
                        </div>

                        <div class="card bg-gradient-primary-to-secondary text-white mb-4 shadow-lg animate__animated animate__fadeInDown"
                            style="border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                            <div
                                class="card-body p-4 d-flex flex-column flex-sm-row justify-content-between align-items-center">
                                <div class="text-center text-sm-start mb-3 mb-sm-0">
                                    <h5 class="card-title text-white mb-1">Yuk, Tambahkan Laporan Harianmu!</h5>
                                    <p class="card-text text-white-75 small">Pastikan datanya akurat ya!</p>
                                </div>
                                <div class="text-center text-sm-end position-relative">
                                    <div class="rounded-circle bg-white d-flex justify-content-center align-items-center animate__animated animate__zoomIn animate__delay-0-5s"
                                        style="width: 80px; height: 80px; opacity: 0.2; position: relative; overflow: hidden; z-index: 1;">
                                        <i class="bx bx-receipt bx-lg text-primary"
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
                                <h5 class="card-title mb-0">Isi Detail Kegiatan</h5>
                                <small class="text-muted">Pastikan semua kolom terisi dengan benar.</small>
                            </div>
                            <div class="card-body p-4">
                                <form action="master_kegiatan_harian_add_act.php" method="POST">
                                    <?php if ($is_admin): // Tampilkan dropdown siswa jika admin yang login 
                                    ?>
                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-1s">
                                        <label for="selected_siswa_id" class="form-label fw-bold">
                                            <i class="bx bx-user me-1"></i> Pilih Siswa:
                                        </label>
                                        <select class="form-control" id="selected_siswa_id" name="selected_siswa_id"
                                            required>
                                            <option value="">-- Pilih Siswa --</option>
                                            <?php foreach ($siswa_list as $siswa_option): ?>
                                            <option value="<?= htmlspecialchars($siswa_option['id_siswa']) ?>"
                                                <?= ($selected_siswa_id_from_url == $siswa_option['id_siswa']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($siswa_option['nama_siswa']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <?php endif; ?>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-2s">
                                        <label for="tanggal_kegiatan" class="form-label fw-bold">
                                            <i class="bx bx-calendar me-1"></i> Hari/Tanggal Kegiatan:
                                        </label>
                                        <input type="date" class="form-control" id="tanggal_kegiatan" name="tanggal"
                                            required value="<?php echo date('Y-m-d'); ?>">
                                    </div>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-3s">
                                        <label for="pekerjaan" class="form-label fw-bold">
                                            <i class="bx bx-briefcase-alt me-1"></i> Deskripsi Pekerjaan:
                                        </label>
                                        <textarea class="form-control" id="pekerjaan" name="pekerjaan" rows="5"
                                            placeholder="Contoh: Membantu tim IT dalam konfigurasi jaringan baru di kantor pusat."
                                            required></textarea>
                                    </div>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-4s">
                                        <label for="catatan" class="form-label fw-bold">
                                            <i class="bx bx-notepad me-1"></i> Catatan Tambahan (Opsional):
                                        </label>
                                        <textarea class="form-control" id="catatan" name="catatan" rows="3"
                                            placeholder="Contoh: Menghadapi kendala teknis saat instalasi driver printer."></textarea>
                                    </div>

                                    <input type="hidden" name="siswa_id"
                                        value="<?php echo htmlspecialchars($siswa_id_for_form); ?>">

                                    <hr class="my-4">

                                    <div
                                        class="d-flex flex-column flex-sm-row justify-content-end gap-2 animate__animated animate__fadeInUp animate__delay-0-5s">
                                        <a href="master_kegiatan_harian.php"
                                            class="btn btn-outline-secondary w-100 w-sm-auto">
                                            <i class="bx bx-arrow-back me-1"></i> Kembali
                                        </a>
                                        <button type="reset" class="btn btn-outline-secondary w-100 w-sm-auto">
                                            <i class="bx bx-refresh me-1"></i> Reset Form
                                        </button>
                                        <button type="submit" class="btn btn-primary w-100 w-sm-auto">
                                            <i class="bx bx-save me-1"></i> Simpan Laporan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="layout-overlay layout-menu-toggle"></div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <?php include './partials/script.php'; ?>
</body>

</html>