<?php
session_start();

// 1. Aturan utama: Cek apakah pengguna yang mengakses BUKAN seorang ADMIN.
if (!isset($_SESSION['admin_status_login']) || $_SESSION['admin_status_login'] !== 'logged_in') {

    // 2. Jika bukan admin, cek apakah dia adalah SISWA.
    if (isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in') {
        // Jika benar siswa, kembalikan ke halaman siswa.
        header('Location: master_kegiatan_harian.php');
        exit();
    }
    // 3. TAMBAHAN: Jika bukan siswa, cek apakah dia adalah GURU.
    elseif (isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in') {
        // Jika benar guru, kembalikan ke halaman guru.
        header('Location: ../../halaman_guru.php'); //belum di atur
        exit();
    }
    // 4. Jika bukan salah satu dari role di atas (admin, siswa, guru),
    // artinya pengguna belum login sama sekali. Arahkan ke halaman login.
    else {
        header('Location: ../login.php');
        exit();
    }
}

// 5. Jika lolos semua pemeriksaan di atas, maka dia adalah ADMIN yang sah.
// Tampilkan semua konten halaman ini.
include 'partials/db.php';
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default">
<?php include 'partials/head.php'; ?>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include './partials/sidebar.php'; ?>
            <div class="layout-page">
                <?php include './partials/navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <?php
                        $id = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : null;
                        $query = "SELECT * FROM guru_pembimbing WHERE id_pembimbing = '$id'";
                        $result = mysqli_query($koneksi, $query);
                        $data = mysqli_fetch_assoc($result);
                        ?>

                        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                            <h4 class="fw-bold mb-0 text-primary">
                                <span class="text-muted fw-light">Master /</span> Edit Guru
                            </h4>
                            <i class="fas fa-user-edit fa-2x text-info" style="opacity: 0.6;"></i>
                        </div>

                        <?php if ($data): ?>
                            <div class="card shadow-lg">
                                <div class="card-header border-bottom">
                                    <h5 class="mb-0">Formulir Edit Data Guru</h5>
                                    <small class="text-muted">Perbarui informasi guru jika ada perubahan.</small>
                                </div>
                                <div class="card-body p-4">
                                    <form action="master_guru_pendamping_edit_act.php" method="POST">
                                        <input type="hidden" name="id_pembimbing"
                                            value="<?= htmlspecialchars($data['id_pembimbing']) ?>">

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Nama Guru</label>
                                            <input type="text" name="nama_pembimbing" class="form-control" required
                                                value="<?= htmlspecialchars($data['nama_pembimbing']) ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">NIP</label>
                                            <input type="text" name="nip" class="form-control" required
                                                value="<?= htmlspecialchars($data['nip']) ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Password Baru (Opsional)</label>
                                            <input type="password" name="password" class="form-control"
                                                placeholder="Isi jika ingin mengganti password">
                                            <div class="form-text">Biarkan kosong jika tidak ingin mengubah password.</div>
                                        </div>

                                        <div class="d-flex justify-content-between mt-4">
                                            <a href="master_guru_pendamping.php" class="btn btn-outline-secondary">
                                                <i class="bx bx-arrow-back me-1"></i> Kembali
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-save me-1"></i> Simpan Perubahan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">Data guru tidak ditemukan atau ID tidak valid.</div>
                            <a href="master_guru_pendamping.php" class="btn btn-primary mt-3"><i
                                    class="bx bx-left-arrow-alt"></i> Kembali ke daftar</a>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include './partials/script.php'; ?>
</body>

</html>