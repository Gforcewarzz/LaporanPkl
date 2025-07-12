<?php
session_start();
include 'partials/db.php'; // Pastikan path ini benar

// Keamanan: Hanya admin yang boleh mengakses dashboard ini
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_admin) {
    if ($is_siswa) {
        header('Location: dashboard_siswa.php'); // Redirect siswa ke dashboard siswa
        exit();
    } elseif ($is_guru) {
        header('Location: dashboard_guru.php'); // Redirect guru ke halaman guru
        exit();
    } else {
        header('Location: ../login.php'); // Jika tidak login sama sekali, redirect ke halaman login
        exit();
    }
}
// 5. Jika lolos semua pemeriksaan di atas, maka dia adalah ADMIN yang sah.
// Tampilkan semua konten halaman ini.

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    $nama_admin = mysqli_real_escape_string($koneksi, $_POST['nama_admin']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);

    // Validasi sederhana
    if (empty($username) || empty($password) || empty($nama_admin)) {
        $message = 'Username, Password, dan Nama Admin harus diisi.';
        $message_type = 'error';
    } elseif (strlen($password) < 6) {
        $message = 'Password minimal 6 karakter.';
        $message_type = 'error';
    } else {
        // Cek apakah username sudah ada
        $check_stmt = mysqli_prepare($koneksi, "SELECT id_admin FROM admin WHERE username = ?");
        mysqli_stmt_bind_param($check_stmt, "s", $username);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $message = 'Username sudah ada, silakan gunakan username lain.';
            $message_type = 'error';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            $stmt = mysqli_prepare($koneksi, "INSERT INTO admin (username, password, nama_admin, email) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssss", $username, $hashed_password, $nama_admin, $email);
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['admin_message'] = 'Admin baru berhasil ditambahkan!';
                    $_SESSION['admin_message_type'] = 'success';
                    $_SESSION['admin_message_title'] = 'Berhasil!';
                    header('Location: master_data_admin.php');
                    exit();
                } else {
                    $message = 'Gagal menambahkan admin: ' . mysqli_error($koneksi);
                    $message_type = 'error';
                }
                mysqli_stmt_close($stmt);
            } else {
                $message = 'Terjadi kesalahan pada query insert: ' . mysqli_error($koneksi);
                $message_type = 'error';
            }
        }
        mysqli_stmt_close($check_stmt);
    }
}
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

                        <?php if ($message): ?>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: '<?= htmlspecialchars($message_type); ?>',
                                title: '<?= ($message_type == "success") ? "Berhasil!" : "Gagal!"; ?>',
                                text: '<?= htmlspecialchars($message); ?>',
                                confirmButtonColor: '<?= ($message_type == "success") ? "#3085d6" : "#d33"; ?>',
                                confirmButtonText: 'OK'
                            });
                        });
                        </script>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                            <h4 class="fw-bold mb-0 text-primary">
                                <span class="text-muted fw-light">Master Data /</span> Tambah Admin
                            </h4>
                            <i class="fas fa-user-plus fa-2x text-info" style="opacity: 0.6;"></i>
                        </div>

                        <div class="card shadow-lg">
                            <div class="card-header border-bottom">
                                <h5 class="mb-0">Formulir Tambah Data Admin</h5>
                                <small class="text-muted">Isi detail akun admin baru.</small>
                            </div>
                            <div class="card-body p-4">
                                <form action="" method="POST">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold" for="username">Username</label>
                                        <input type="text" name="username" id="username" class="form-control" required
                                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold" for="nama_admin">Nama Admin</label>
                                        <input type="text" name="nama_admin" id="nama_admin" class="form-control"
                                            required value="<?= htmlspecialchars($_POST['nama_admin'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold" for="email">Email (Opsional)</label>
                                        <input type="email" name="email" id="email" class="form-control"
                                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold" for="password">Password</label>
                                        <input type="password" name="password" id="password" class="form-control"
                                            required>
                                        <small class="form-text text-muted">Minimal 6 karakter.</small>
                                    </div>

                                    <div class="d-flex justify-content-between mt-4">
                                        <a href="master_data_admin.php" class="btn btn-outline-secondary">
                                            <i class="bx bx-arrow-back me-1"></i> Kembali
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-save me-1"></i> Tambah Admin
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

    <?php include './partials/script.php'; ?>
</body>

</html>