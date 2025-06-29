<?php

session_start();

// Keamanan: Hanya admin yang boleh mengakses dashboard ini
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_admin) {
    if ($is_siswa) {
        header('Location: dashboard_siswa.php'); // Redirect siswa ke dashboard siswa
        exit();
    } elseif ($is_guru) {
        header('Location: ../halaman_guru.php'); // Redirect guru ke halaman guru
        exit();
    } else {
        header('Location: ../login.php'); // Jika tidak login sama sekali, redirect ke halaman login
        exit();
    }
}
// 5. Jika lolos semua pemeriksaan di atas, maka dia adalah ADMIN yang sah.
// Tampilkan semua konten halaman ini.
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

                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                            <h4 class="fw-bold mb-0 text-primary">
                                <span class="text-muted fw-light">Master /</span> Tambah Guru Pendamping
                            </h4>
                            <i class="fas fa-user-plus fa-2x text-info" style="opacity: 0.6;"></i>
                        </div>

                        <!-- Form Card -->
                        <div class="card shadow-lg" style="border-radius: 10px;">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Form Tambah Data Guru</h5>
                                <small class="text-muted">Masukkan data lengkap guru pembimbing.</small>
                            </div>
                            <div class="card-body p-4">
                                <form action="master_guru_pendamping_add_act.php" method="POST">

                                    <div class="mb-3">
                                        <label for="nama_pembimbing" class="form-label fw-bold"><i
                                                class="bx bx-user me-1"></i> Nama Guru</label>
                                        <input type="text" class="form-control" id="nama_pembimbing"
                                            name="nama_pembimbing" placeholder="Contoh: Ibu Siti Aminah, S.T." required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="nip" class="form-label fw-bold"><i class="bx bx-id-card me-1"></i>
                                            NIP</label>
                                        <input type="text" class="form-control" id="nip" name="nip"
                                            placeholder="Contoh: 198811202015032001" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label fw-bold"><i class="bx bx-lock me-1"></i>
                                            Password</label>
                                        <input type="password" class="form-control" id="password" name="password"
                                            placeholder="Masukkan password login guru" required>
                                        <div class="form-text">Password akan dienkripsi otomatis.</div>
                                    </div>

                                    <hr class="my-4">

                                    <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                                        <a href="master_guru_pendamping.php"
                                            class="btn btn-outline-secondary w-100 w-sm-auto">
                                            <i class="bx bx-arrow-back me-1"></i> Kembali
                                        </a>
                                        <button type="reset" class="btn btn-outline-secondary w-100 w-sm-auto">
                                            <i class="bx bx-refresh me-1"></i> Reset
                                        </button>
                                        <button type="submit" class="btn btn-primary w-100 w-sm-auto">
                                            <i class="bx bx-save me-1"></i> Simpan
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

    <?php include './partials/script.php'; ?>
</body>

</html>