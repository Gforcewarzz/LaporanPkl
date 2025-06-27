<?php
// 1. Mulai sesi untuk mendapatkan ID siswa yang login
session_start();

// --- LOGIKA KEAMANAN HALAMAN SISWA ---

// 1. Definisikan dulu role yang sedang login untuk mempermudah pembacaan kode.
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';

// 2. Aturan utama: Cek jika pengguna BUKAN Siswa DAN BUKAN Admin.
// Jika salah satu dari mereka (siswa atau admin) login, kondisi ini akan false dan halaman akan lanjut dimuat.
if (!$is_siswa && !$is_admin) {

    // 3. Jika tidak diizinkan, baru kita cek siapa pengguna ini.
    // Apakah dia seorang Guru yang mencoba masuk?
    if (isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in') {
        // Jika benar guru, kembalikan ke halaman dasbor guru.
        header('Location: ../halaman_guru.php'); // Sesuaikan path jika perlu
        exit();
    }
    // 4. Jika bukan siapa-siapa dari role di atas, artinya pengguna belum login.
    else {
        // Arahkan paksa ke halaman login.
        header('Location: ../login.php'); // Sesuaikan path jika perlu
        exit();
    }
}
// Sertakan file koneksi database
include 'partials/db.php';

// 3. Ambil ID siswa dari sesi dan ID laporan dari URL
$id_siswa_login = $_SESSION['id_siswa'];
$id_jurnal_kegiatan = isset($_GET['id']) ? intval($_GET['id']) : 0;

$laporan_data = null;

// 4. Ambil data laporan HANYA JIKA ID laporan ada DAN milik siswa yang login
if ($id_jurnal_kegiatan > 0) {
    // Query yang aman: Cek id_jurnal_kegiatan DAN siswa_id
    $sql = "SELECT * FROM jurnal_kegiatan WHERE id_jurnal_kegiatan = ? AND siswa_id = ?";
    
    $stmt = $koneksi->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ii", $id_jurnal_kegiatan, $id_siswa_login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $laporan_data = $result->fetch_assoc();
        }
        $stmt->close();
    }
}

$koneksi->close();
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/" data-template="vertical-menu-template-free">

<?php include 'partials/head.php'; ?>

<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include './partials/sidebar.php'; ?>
        <div class="layout-page">
            <?php include './partials/navbar.php'; ?>
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">

                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom position-relative">
                        <h4 class="fw-bold mb-0 text-primary animate__animated animate__fadeInLeft">
                            <span class="text-muted fw-light">Laporan /</span> Edit Tugas Proyek
                        </h4>
                        <i class="fas fa-pencil-alt fa-2x text-info animate__animated animate__fadeInRight" style="opacity: 0.6;"></i>
                    </div>

                    <?php if ($laporan_data): ?>
                    <form action="master_tugas_project_edit_act.php" method="POST" class="card p-4 shadow-lg">
                        
                        <input type="hidden" name="id_jurnal_kegiatan" value="<?php echo htmlspecialchars($laporan_data['id_jurnal_kegiatan']); ?>">

                        <div class="mb-3">
                            <label for="nama_pekerjaan" class="form-label fw-bold">Nama Pekerjaan / Proyek</label>
                            <input type="text" class="form-control" id="nama_pekerjaan" name="nama_pekerjaan"
                                   value="<?php echo htmlspecialchars($laporan_data['nama_pekerjaan']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="perencanaan_kegiatan" class="form-label fw-bold">Perencanaan Kegiatan</label>
                            <textarea class="form-control" id="perencanaan_kegiatan" name="perencanaan_kegiatan" rows="5" required><?php echo htmlspecialchars($laporan_data['perencanaan_kegiatan']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="pelaksanaan_kegiatan" class="form-label fw-bold">Pelaksanaan Kegiatan / Hasil</label>
                            <textarea class="form-control" id="pelaksanaan_kegiatan" name="pelaksanaan_kegiatan" rows="7" required><?php echo htmlspecialchars($laporan_data['pelaksanaan_kegiatan']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="catatan_instruktur" class="form-label fw-bold">Catatan Instruktur</label>
                            <textarea class="form-control" id="catatan_instruktur" name="catatan_instruktur" rows="4"><?php echo htmlspecialchars($laporan_data['catatan_instruktur']); ?></textarea>
                             <div class="form-text text-muted">Catatan atau umpan balik dari instruktur Anda (opsional).</div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="master_tugas_project.php" class="btn btn-secondary me-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                    <?php else: ?>
                        <div class="alert alert-danger mt-4">
                            <h5 class="alert-heading">Akses Ditolak!</h5>
                            <p class="mb-0">Laporan tugas tidak ditemukan atau Anda tidak memiliki izin untuk mengeditnya.</p>
                        </div>
                    <?php endif; ?>

                </div>
                 <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</div>

<?php include 'partials/script.php'; ?>
</body>
</html>