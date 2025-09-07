<?php
// Pastikan path ke db.php benar relatif dari lokasi file ini
include 'partials/db.php';
session_start();

// --- DEBUGGING START (untuk pengembangan, hapus setelah selesai) ---
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>Debugging ganti_password_act.php:</h3>";
echo "Metode Request: " . $_SERVER['REQUEST_METHOD'] . "<br>";
echo "POST Data yang Diterima: <pre>" . print_r($_POST, true) . "</pre><br>";
// --- DEBUGGING END ---

// Ambil data sesi universal dari pengguna yang sedang login
$loggedInUserId = $_SESSION['user_id'] ?? null;
$loggedInUserRole = $_SESSION['user_role'] ?? null;

// Jika tidak ada sesi login yang valid atau request bukan POST, arahkan kembali
if (!$loggedInUserId || !$loggedInUserRole || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['ganti_password_message'] = 'Akses tidak sah atau sesi tidak valid.';
    $_SESSION['ganti_password_message_type'] = 'error';
    $_SESSION['ganti_password_message_title'] = 'Error!';
    header('Location: login.php'); // Sesuaikan path ke halaman login Anda
    exit();
}

// --- PERBAIKAN DI SINI: Ambil data dari form sesuai yang dikirimkan ---
$form_user_id = mysqli_real_escape_string($koneksi, $_POST['user_id'] ?? ''); // Ambil dari hidden input
$form_user_role = mysqli_real_escape_string($koneksi, $_POST['user_role'] ?? ''); // Ambil dari hidden input
$current_password = mysqli_real_escape_string($koneksi, $_POST['current_password'] ?? '');
$new_password = mysqli_real_escape_string($koneksi, $_POST['new_password'] ?? '');
$confirm_new_password = mysqli_real_escape_string($koneksi, $_POST['confirm_new_password'] ?? '');

// Inisialisasi variabel untuk pesan notifikasi SweetAlert
$message = '';
$message_type = '';
$message_title = '';

// --- VALIDASI AWAL ---
// 1. Keamanan Kritis: Verifikasi ID dan peran yang dikirim dari form harus cocok dengan sesi
//    Gunakan $loggedInUserId dan $loggedInUserRole karena itu yang terjamin dari sesi
if ($form_user_id != $loggedInUserId || $form_user_role != $loggedInUserRole) {
    $message = 'Terjadi kesalahan keamanan. Data sesi tidak cocok dengan data form.';
    $message_type = 'error';
    $message_title = 'Peringatan Keamanan!';
}
// 2. Semua kolom password harus diisi
elseif (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
    $message = 'Semua kolom password harus diisi.';
    $message_type = 'error';
    $message_title = 'Input Tidak Lengkap!';
}
// 3. Password baru dan konfirmasi harus cocok
elseif ($new_password !== $confirm_new_password) {
    $message = 'Password baru dan konfirmasi password tidak cocok.';
    $message_type = 'error';
    $message_title = 'Password Tidak Cocok!';
}
// 4. Validasi panjang password baru
elseif (strlen($new_password) < 6) { // Bisa ditambah validasi kompleksitas lainnya
    $message = 'Password baru minimal 6 karakter.';
    $message_type = 'error';
    $message_title = 'Password Lemah!';
}
// --- Jika semua validasi awal lolos, lanjutkan ke proses database ---
else {
    // Tentukan nama tabel dan kolom ID berdasarkan peran yang login
    // Gunakan $loggedInUserRole karena itu yang terjamin dari sesi
    $table_name = '';
    $id_column = '';

    switch ($loggedInUserRole) {
        case 'siswa':
            $table_name = 'siswa';
            $id_column = 'id_siswa';
            break;
        case 'guru_pendamping':
            $table_name = 'guru_pembimbing';
            $id_column = 'id_pembimbing';
            break;
        case 'admin':
            $table_name = 'admin';
            $id_column = 'id_admin';
            break;
        default:
            // Jika peran dari sesi tidak dikenali (ini seharusnya tidak terjadi jika login action sudah benar)
            $message = 'Peran pengguna tidak dikenali.';
            $message_type = 'error';
            $message_title = 'Peran Tidak Valid!';
            // Lewati proses database jika peran tidak valid
            break;
    }

    // Hanya lanjutkan proses database jika $table_name berhasil ditentukan
    if (!empty($table_name)) {
        // 1. Ambil hash password lama dari database menggunakan prepared statement
        // Pastikan nama kolom 'password' benar di semua tabel (siswa, guru_pembimbing, admin)
        $stmt_get_password = mysqli_prepare($koneksi, "SELECT password FROM $table_name WHERE $id_column = ?");

        if ($stmt_get_password) {
            mysqli_stmt_bind_param($stmt_get_password, "i", $loggedInUserId); // Bind ID pengguna dari sesi
            mysqli_stmt_execute($stmt_get_password);
            $result_get_password = mysqli_stmt_get_result($stmt_get_password);
            $user_data = mysqli_fetch_assoc($result_get_password);
            mysqli_stmt_close($stmt_get_password);

            if (!$user_data) {
                $message = 'Data pengguna tidak ditemukan di database.';
                $message_type = 'error';
                $message_title = 'Pengguna Tidak Ditemukan!';
            } else {
                $hashed_password_db = $user_data['password'];

                // 2. Verifikasi password saat ini yang dimasukkan pengguna
                if (password_verify($current_password, $hashed_password_db)) {
                    // 3. Hash password baru sebelum menyimpannya
                    $new_password_hashed = password_hash($new_password, PASSWORD_BCRYPT);

                    // 4. Update password di database menggunakan prepared statement
                    $stmt_update_password = mysqli_prepare($koneksi, "UPDATE $table_name SET password = ? WHERE $id_column = ?");
                    if ($stmt_update_password) {
                        mysqli_stmt_bind_param($stmt_update_password, "si", $new_password_hashed, $loggedInUserId);
                        if (mysqli_stmt_execute($stmt_update_password)) {
                            $message = 'Password berhasil diganti!';
                            $message_type = 'success';
                            $message_title = 'Berhasil!';
                        } else {
                            $message = 'Gagal mengganti password: ' . mysqli_error($koneksi);
                            $message_type = 'error';
                            $message_title = 'Gagal Update!';
                        }
                        mysqli_stmt_close($stmt_update_password);
                    } else {
                        $message = 'Terjadi kesalahan saat menyiapkan query update: ' . mysqli_error($koneksi);
                        $message_type = 'error';
                        $message_title = 'Error Database!';
                    }
                } else {
                    $message = 'Password saat ini salah.';
                    $message_type = 'error';
                    $message_title = 'Password Salah!';
                }
            }
        } else {
            $message = 'Terjadi kesalahan saat menyiapkan query pengambilan password: ' . mysqli_error($koneksi);
            $message_type = 'error';
            $message_title = 'Error Database!';
        }
    }
}

// Simpan pesan notifikasi ke session dan redirect kembali ke form ganti password
$_SESSION['ganti_password_message'] = $message;
$_SESSION['ganti_password_message_type'] = $message_type;
$_SESSION['ganti_password_message_title'] = $message_title;
mysqli_close($koneksi);
header('Location: ganti_password.php');
exit();