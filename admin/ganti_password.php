<?php
// Pastikan path ke db.php benar relatif dari lokasi file ini
// Jika db.php ada di partials/
include 'partials/db.php';
session_start(); // Pastikan session sudah dimulai

// Ambil data sesi universal (user_id, user_role, user_name)
$loggedInUserId = $_SESSION['user_id'] ?? null;
$loggedInUserRole = $_SESSION['user_role'] ?? null;
$loggedInUserName = $_SESSION['user_name'] ?? 'Pengguna'; // Default nama jika tidak ada

// Verifikasi apakah ada user yang login dengan sesi universal
if (!$loggedInUserId || !$loggedInUserRole) {
    header('Location: login.php'); // Redirect ke halaman login jika belum login
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
        $formTitle .= " Pengguna"; // Fallback jika peran tidak dikenali
        break;
}

// Ambil pesan notifikasi (misal dari ganti_password_act.php) dari session
$message = $_SESSION['ganti_password_message'] ?? '';
$message_type = $_SESSION['ganti_password_message_type'] ?? '';
$message_title_swal = $_SESSION['ganti_password_message_title'] ?? ''; // Ambil juga judul SweetAlert

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