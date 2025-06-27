<?php
include 'partials/db.php'; // Pastikan path ini benar
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
$id_admin = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : null;

// Jika tidak ada ID admin, redirect
if (!$id_admin) {
    $_SESSION['admin_message'] = 'ID Admin tidak ditemukan.';
    $_SESSION['admin_message_type'] = 'error';
    $_SESSION['admin_message_title'] = 'Error!';
    header('Location: master_data_admin.php');
    exit();
}

$message = '';
$message_type = '';

// Ambil data admin yang akan diedit
$query_admin = "SELECT username, nama_admin, email FROM admin WHERE id_admin = '$id_admin'";
$result_admin = mysqli_query($koneksi, $query_admin);
$data_admin = mysqli_fetch_assoc($result_admin);

if (!$data_admin) {
    $_SESSION['admin_message'] = 'Data admin tidak ditemukan.';
    $_SESSION['admin_message_type'] = 'error';
    $_SESSION['admin_message_title'] = 'Error!';
    header('Location: master_data_admin.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $new_nama_admin = mysqli_real_escape_string($koneksi, $_POST['nama_admin']);
    $new_email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $new_password_input = $_POST['password'] ?? null; // Password bisa kosong jika tidak diganti

    // Validasi sederhana
    if (empty($new_username) || empty($new_nama_admin)) {
        $message = 'Username dan Nama Admin tidak boleh kosong.';
        $message_type = 'error';
    } elseif (!empty($new_password_input) && strlen($new_password_input) < 6) {
        $message = 'Password baru minimal 6 karakter.';
        $message_type = 'error';
    } else {
        // Cek duplikasi username (kecuali username saat ini)
        $check_stmt = mysqli_prepare($koneksi, "SELECT id_admin FROM admin WHERE username = ? AND id_admin != ?");
        mysqli_stmt_bind_param($check_stmt, "si", $new_username, $id_admin);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $message = 'Username sudah digunakan oleh admin lain, silakan pilih username lain.';
            $message_type = 'error';
            mysqli_stmt_close($check_stmt);
        } else {
            mysqli_stmt_close($check_stmt);

            $password_update_query = "";
            if (!empty($new_password_input)) {
                $hashed_new_password = password_hash($new_password_input, PASSWORD_BCRYPT);
                $password_update_query = ", password = '" . $hashed_new_password . "'";
            }

            $update_query = "UPDATE admin SET 
                                username = '$new_username',
                                nama_admin = '$new_nama_admin',
                                email = '$new_email'
                                $password_update_query
                            WHERE id_admin = '$id_admin'";

            if (mysqli_query($koneksi, $update_query)) {
                $_SESSION['admin_message'] = 'Data admin berhasil diperbarui!';
                $_SESSION['admin_message_type'] = 'success';
                $_SESSION['admin_message_title'] = 'Berhasil!';
                header('Location: master_data_admin.php');
                exit();
            } else {
                $message = 'Gagal memperbarui data admin: ' . mysqli_error($koneksi);
                $message_type = 'error';
            }
        }
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
                                <span class="text-muted fw-light">Master Data /</span> Edit Admin
                            </h4>
                            <i class="fas fa-user-edit fa-2x text-info" style="opacity: 0.6;"></i>
                        </div>

                        <div class="card shadow-lg">
                            <div class="card-header border-bottom">
                                <h5 class="mb-0">Formulir Edit Data Admin</h5>
                                <small class="text-muted">Silakan sesuaikan data admin berikut.</small>
                            </div>
                            <div class="card-body p-4">
                                <form action="" method="POST">
                                    <input type="hidden" name="id_admin" value="<?= htmlspecialchars($id_admin) ?>">

                                    <div class="mb-3">
                                        <label class="form-label fw-bold" for="username">Username</label>
                                        <input type="text" name="username" id="username" class="form-control" required
                                            value="<?= htmlspecialchars($data_admin['username']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold" for="nama_admin">Nama Admin</label>
                                        <input type="text" name="nama_admin" id="nama_admin" class="form-control"
                                            required value="<?= htmlspecialchars($data_admin['nama_admin']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold" for="email">Email (Opsional)</label>
                                        <input type="email" name="email" id="email" class="form-control"
                                            value="<?= htmlspecialchars($data_admin['email'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold" for="password">Password (Kosongkan jika tidak
                                            ingin mengganti)</label>
                                        <input type="password" name="password" id="password" class="form-control"
                                            placeholder="Masukkan password baru jika ingin mengganti">
                                        <small class="form-text text-muted">Minimal 6 karakter.</small>
                                    </div>

                                    <div class="d-flex justify-content-between mt-4">
                                        <a href="master_data_admin.php" class="btn btn-outline-secondary">
                                            <i class="bx bx-arrow-back me-1"></i> Kembali
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-save me-1"></i> Simpan Perubahan
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