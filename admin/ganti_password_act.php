<?php
include 'partials/db.php'; // Koneksi database
session_start(); // Pastikan session sudah dimulai

// Ambil ID siswa dari sesi. Ini KRITIS untuk keamanan.
$loggedInUserId = $_SESSION['id_siswa'] ?? null;

// Verifikasi status login dan metode request
if (!isset($_SESSION['siswa_status_login']) || $_SESSION['siswa_status_login'] !== 'logged_in' || !$loggedInUserId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['ganti_password_message'] = 'Akses tidak sah.';
    $_SESSION['ganti_password_message_type'] = 'error';
    header('Location: login.php'); // Redirect ke halaman login
    exit();
}

// Ambil data dari form POST
$id_siswa_from_form = $_POST['id_siswa'] ?? null; // ID dari hidden input
$current_password   = $_POST['current_password'] ?? '';
$new_password       = $_POST['new_password'] ?? '';
$confirm_new_password = $_POST['confirm_new_password'] ?? '';

// Inisialisasi variabel pesan
$message = '';
$message_type = '';

// Penting: Verifikasi ID siswa yang dikirim dari form harus sama dengan ID siswa di sesi
if ($id_siswa_from_form != $loggedInUserId) {
    $message = 'Terjadi kesalahan keamanan. ID pengguna tidak cocok.';
    $message_type = 'error';
} elseif (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
    $message = 'Semua kolom password harus diisi.';
    $message_type = 'error';
} elseif ($new_password !== $confirm_new_password) {
    $message = 'Password baru dan konfirmasi password tidak cocok.';
    $message_type = 'error';
} elseif (strlen($new_password) < 6) { // Validasi panjang password minimal 6 karakter
    $message = 'Password baru minimal 6 karakter.';
    $message_type = 'error';
} else {
    // 1. Ambil password lama dari database menggunakan prepared statement
    // Pastikan kolom 'password' ada di tabel 'siswa'
    $stmt_get_password = mysqli_prepare($koneksi, "SELECT password FROM siswa WHERE id_siswa = ?");
    if ($stmt_get_password) {
        mysqli_stmt_bind_param($stmt_get_password, "i", $loggedInUserId); // 'i' untuk integer ID siswa
        mysqli_stmt_execute($stmt_get_password);
        $result_get_password = mysqli_stmt_get_result($stmt_get_password);
        $siswa_data = mysqli_fetch_assoc($result_get_password);
        mysqli_stmt_close($stmt_get_password);

        if (!$siswa_data) {
            $message = 'Data siswa tidak ditemukan.';
            $message_type = 'error';
        } else {
            $hashed_password_db = $siswa_data['password'];

            // 2. Verifikasi password saat ini
            if (password_verify($current_password, $hashed_password_db)) {
                // 3. Hash password baru
                $new_password_hashed = password_hash($new_password, PASSWORD_BCRYPT);

                // 4. Update password di database menggunakan prepared statement
                $stmt_update_password = mysqli_prepare($koneksi, "UPDATE siswa SET password = ? WHERE id_siswa = ?");
                if ($stmt_update_password) {
                    mysqli_stmt_bind_param($stmt_update_password, "si", $new_password_hashed, $loggedInUserId); // 's' string, 'i' integer
                    if (mysqli_stmt_execute($stmt_update_password)) {
                        $message = 'Password berhasil diganti!';
                        $message_type = 'success';
                    } else {
                        $message = 'Gagal mengganti password: ' . mysqli_error($koneksi);
                        $message_type = 'error';
                    }
                    mysqli_stmt_close($stmt_update_password);
                } else {
                    $message = 'Terjadi kesalahan pada query update: ' . mysqli_error($koneksi);
                    $message_type = 'error';
                }
            } else {
                $message = 'Password saat ini salah.';
                $message_type = 'error';
            }
        }
    } else {
        $message = 'Terjadi kesalahan pada query select: ' . mysqli_error($koneksi);
        $message_type = 'error';
    }
}

// Simpan pesan ke session dan redirect kembali ke form ganti password
$_SESSION['ganti_password_message'] = $message;
$_SESSION['ganti_password_message_type'] = $message_type;
header('Location: ganti_password_siswa_form.php');
exit();