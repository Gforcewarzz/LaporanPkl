<?php
session_start(); // Pastikan session sudah dimulai paling awal

// Pastikan path ke db.php benar relatif dari lokasi file ini
include 'partials/db.php';

// --- Inisialisasi Variabel Sesi Universal ---
// Ini adalah logika yang diperbaiki untuk memastikan user_id, user_role, dan user_name
// selalu terisi dengan benar dari berbagai kemungkinan setup sesi login.
$loggedInUserId = null;
$loggedInUserRole = null;
$loggedInUserName = 'Pengguna'; // Default nama jika tidak ada

// Prioritaskan sesi universal jika sudah ada (direkomendasikan setelah perbaikan di login_act)
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    $loggedInUserId = $_SESSION['user_id'];
    $loggedInUserRole = $_SESSION['user_role'];
    $loggedInUserName = $_SESSION['user_name'] ?? 'Pengguna';
} else {
    // Fallback/kompatibilitas dengan setup sesi lama jika sesi universal belum diterapkan konsisten
    // Admin
    if (isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in') {
        $loggedInUserId = $_SESSION['id_admin'] ?? null;
        $loggedInUserRole = 'admin';
        $loggedInUserName = $_SESSION['nama_admin'] ?? 'Admin';
    }
    // Guru Pendamping
    elseif (isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in') {
        $loggedInUserId = $_SESSION['id_guru_pendamping'] ?? null; // Cek ini! Pastikan ID guru ada di sesi ini
        $loggedInUserRole = 'guru_pendamping';
        $loggedInUserName = $_SESSION['nama_guru'] ?? 'Guru'; // Cek ini! Pastikan nama guru ada di sesi ini
    }
    // Siswa
    elseif (isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in') {
        $loggedInUserId = $_SESSION['id_siswa'] ?? null;
        $loggedInUserRole = 'siswa';
        $loggedInUserName = $_SESSION['siswa_nama'] ?? 'Siswa';
    }
}

// Verifikasi akhir: Jika masih belum ada ID atau peran, redirect ke login
if (!$loggedInUserId || !$loggedInUserRole) {
    // Set pesan alert untuk login agar lebih jelas
    $_SESSION['ganti_password_message'] = 'Sesi Anda tidak valid. Silakan login kembali.';
    $_SESSION['ganti_password_message_type'] = 'error';
    $_SESSION['ganti_password_message_title'] = 'Sesi Tidak Ditemukan!';
    header('Location: login.php'); // Sesuaikan ke halaman login utama Anda
    exit();
}

// Tentukan judul form berdasarkan role yang sedang login
$formTitle = "Formulir Ganti Password";
switch ($loggedInUserRole) {
    case 'siswa':
        $formTitle .= " Siswa";
        break;
    case 'guru_pendamping':
        $formTitle .= " Guru Pendamping";
        break;
    case 'admin':
        $formTitle .= " Admin";
        break;
    default:
        $formTitle .= " Pengguna";
        break;
}

// Ambil pesan notifikasi (misal dari ganti_password_act.php) dari session
$message = $_SESSION['ganti_password_message'] ?? '';
$message_type = $_SESSION['ganti_password_message_type'] ?? '';
$message_title_swal = $_SESSION['ganti_password_message_title'] ?? '';

// Hapus pesan dari session agar tidak muncul lagi setelah refresh halaman
unset($_SESSION['ganti_password_message']);
unset($_SESSION['ganti_password_message_type']);
unset($_SESSION['ganti_password_message_title']);

?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="assets/"
    data-template="vertical-menu-template-free">
<?php include 'partials/head.php'; // Sesuaikan path ke head.php 
?>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include 'partials/sidebar.php'; // Sesuaikan path ke sidebar.php 
            ?>
            <div class="layout-page">
                <?php include 'partials/navbar.php'; // Sesuaikan path ke navbar.php 
                ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <?php if ($message): // Tampilkan SweetAlert2 jika ada pesan 
                        ?>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    Swal.fire({
                                        icon: '<?= htmlspecialchars($message_type); ?>',
                                        title: '<?= htmlspecialchars($message_title_swal); ?>',
                                        text: '<?= htmlspecialchars($message); ?>',
                                        confirmButtonColor: '<?= ($message_type == "success") ? "#3085d6" : "#d33"; ?>',
                                        confirmButtonText: 'OK'
                                    });
                                });
                            </script>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                            <h4 class="fw-bold mb-0 text-primary">
                                <span class="text-muted fw-light">Profil <?= htmlspecialchars($loggedInUserName); ?>
                                    /</span> Ganti Password
                            </h4>
                            <i class="fas fa-key fa-2x text-info" style="opacity: 0.6;"></i>
                        </div>

                        <div class="card shadow-lg">
                            <div class="card-header border-bottom">
                                <h5 class="mb-0"><?= htmlspecialchars($formTitle); ?></h5>
                                <small class="text-muted">Pastikan password baru Anda kuat dan mudah diingat.</small>
                            </div>
                            <div class="card-body p-4">
                                <form action="ganti_password_act.php" method="POST">
                                    <input type="hidden" name="user_id"
                                        value="<?= htmlspecialchars($loggedInUserId); ?>">
                                    <input type="hidden" name="user_role"
                                        value="<?= htmlspecialchars($loggedInUserRole); ?>">

                                    <div class="mb-3">
                                        <label class="form-label fw-bold" for="current_password">Password Saat
                                            Ini</label>
                                        <input type="password" name="current_password" id="current_password"
                                            class="form-control" required autocomplete="current-password">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold" for="new_password">Password Baru</label>
                                        <input type="password" name="new_password" id="new_password"
                                            class="form-control" required autocomplete="new-password">
                                        <small class="form-text text-muted">Minimal 6 karakter, disarankan kombinasi
                                            huruf besar, kecil, angka, dan simbol.</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold" for="confirm_new_password">Konfirmasi Password
                                            Baru</label>
                                        <input type="password" name="confirm_new_password" id="confirm_new_password"
                                            class="form-control" required autocomplete="new-password">
                                    </div>

                                    <div class="d-flex justify-content-between mt-4">
                                        <a href="javascript:history.back()" class="btn btn-outline-secondary">
                                            <i class="bx bx-arrow-back me-1"></i> Kembali
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-save me-1"></i> Ganti Password
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'partials/script.php'; // Sesuaikan path ke script.php 
    ?>
</body>

</html>