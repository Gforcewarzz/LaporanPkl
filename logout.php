<?php
session_start();

// Simpan informasi peran pengguna sebelum menghapus semua sesi
// Kita akan menggunakan $_SESSION['user_role'] yang sudah kita tetapkan di login_petugas_act.php dan login siswa
$last_user_role = $_SESSION['user_role'] ?? null;

// Hapus semua session
session_unset();
session_destroy();

// Tentukan halaman login tujuan berdasarkan peran terakhir
$redirect_to_login = 'login.php'; // Default: login.php (untuk siswa)

if ($last_user_role) {
    switch ($last_user_role) {
        case 'admin':
        case 'guru_pendamping':
            // Jika terakhir login sebagai admin atau guru, arahkan ke login_petugas.php
            $redirect_to_login = 'login_petugas.php'; // Sesuaikan jika nama file Anda berbeda, misal: login.php (jika itu memang login petugas)
            break;
        case 'siswa':
            // Jika terakhir login sebagai siswa, arahkan ke login.php
            $redirect_to_login = 'login.php';
            break;
        // Anda bisa menambahkan case lain jika ada peran lain di masa depan
        default:
            // Jika peran tidak dikenali, tetap ke default login.php
            $redirect_to_login = 'login.php';
            break;
    }
}

// Redirect ke halaman login yang sesuai
header("Location: " . $redirect_to_login);
exit;