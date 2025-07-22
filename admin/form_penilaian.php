<?php
session_start();
require_once 'partials/db.php';

// Keamanan: Admin dan Guru boleh akses
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_admin && !$is_guru) {
    header('Location: ../login.php');
    exit();
}

// ---------------------------------------------------------------------
// TAHAP 1: PERSIAPAN DATA DARI DATABASE
// ---------------------------------------------------------------------

// --- PERUBAHAN DI SINI: Query siswa dimodifikasi untuk mengecek status penilaian ---
$query_siswa_sql = "
    SELECT 
        s.id_siswa, 
        s.nama_siswa, 
        s.jurusan_id,
        -- Tambahkan subquery untuk mengecek apakah siswa punya nilai
        (SELECT COUNT(*) FROM nilai_siswa ns WHERE ns.siswa_id = s.id_siswa) as jumlah_nilai
    FROM siswa s
    WHERE s.status = 'aktif'
";
$params = [];
$types = '';

if ($is_guru) {
    $query_siswa_sql .= " AND s.pembimbing_id = ?";
    $params[] = $_SESSION['id_guru_pendamping'];
    $types .= 'i';
}
$query_siswa_sql .= " ORDER BY s.nama_siswa ASC";

$stmt = $koneksi->prepare($query_siswa_sql);
if ($stmt === false) {
    die("Error preparing student query: " . $koneksi->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$siswa_result = $stmt->get_result();
// --- AKHIR PERUBAHAN ---

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

                        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                            <h4 class="fw-bold mb-0 text-primary">
                                <span class="text-muted fw-light">Penilaian /</span> Form Penilaian Kompetensi
                            </h4>
                            <i class="fas fa-edit fa-2x text-info" style="opacity: 0.6;"></i>
                        </div>

                        <div class="card shadow-lg">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">Input Nilai Siswa</h5>
                                <small class="text-muted">Pilih siswa untuk memuat kompetensi sesuai jurusannya. Siswa dengan tanda (✓) sudah pernah dinilai.</small>
                            </div>
                            <div class="card-body p-4">
                                <form action="proses_nilai.php" method="POST">
                                    
                                    <div class="mb-4">
                                        <label for="pilih_siswa" class="form-label fw-bold"><i class="bx bx-user-check me-1"></i> Pilih Siswa:</label>
                                        <select class="form-select" id="pilih_siswa" name="id_siswa" required>
                                            <option value="" data-jurusan-id="0" selected disabled>
                                                <?= $is_guru ? 'Pilih siswa bimbingan Anda...' : 'Pilih seorang siswa...' ?>
                                            </option>
                                            
                                            <?php while ($siswa = $siswa_result->fetch_assoc()): ?>
                                                <?php 
                                                    // Cek apakah siswa punya nilai berdasarkan hasil query
                                                    $sudah_dinilai = $siswa['jumlah_nilai'] > 0;
                                                    $tanda = $sudah_dinilai ? '✓' : ''; // Tanda centang
                                                ?>
                                                <option value="<?= $siswa['id_siswa'] ?>" data-jurusan-id="<?= $siswa['jurusan_id'] ?>">
                                                    <?= htmlspecialchars($siswa['nama_siswa']) ?> <?= $tanda ? "($tanda Sudah Dinilai)" : "" ?>
                                                </option>
                                            <?php endwhile; ?>
                                            </select>
                                    </div>

                                    <hr class="my-4">

                                    <div id="container-kompetensi">
                                        <div class="text-center text-muted p-4 border rounded-3 bg-light">
                                            <i class="bx bx-list-ul fs-1 mb-3"></i>
                                            <p class="mb-0">Silakan pilih seorang siswa untuk menampilkan daftar kompetensi yang akan dinilai.</p>
                                        </div>
                                    </div>

                                    <div id="submit-wrapper" class="d-flex justify-content-end gap-2 mt-4" style="display: none !important;">
                                        <a href="laporan_penilaian_siswa.php" class="btn btn-outline-secondary">
                                            <i class="bx bx-arrow-back me-1"></i> Kembali ke Laporan
                                        </a>
                                        <button type="submit" name="submit" class="btn btn-primary">
                                            <i class="bx bx-save me-1"></i> Simpan Penilaian
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php include './partials/footer.php'; ?>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <span></span>
    <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <?php include 'partials/script.php' ?>
    <script src="penilaian.js"></script>
</body>
</html>