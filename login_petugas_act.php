<?php
session_start();
// Pastikan path ini benar menuju file koneksi database Anda
include 'admin/partials/db.php'; 

// Jika akses bukan melalui metode POST, hentikan dan kembalikan ke form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login_petugas.php');
    exit();
}

// 1. Ambil data dari form
$input_identifier = $_POST['username'];
$password_input   = $_POST['password'];
$role_selected    = $_POST['role'];

// 2. Tentukan konfigurasi berdasarkan peran (role) yang dipilih
$table_name = '';
$id_column = '';
$identifier_column = '';
$name_column = '';
$redirect_path = '';

switch ($role_selected) {
    case 'admin':
        $table_name = 'admin';
        $id_column = 'id_admin';
        $identifier_column = 'username';
        $name_column = 'nama_admin';
        $redirect_path = 'admin/index.php';
        break;
    case 'guru_pendamping':
        $table_name = 'guru_pembimbing';
        $id_column = 'id_pembimbing';
        $identifier_column = 'nip'; // Sesuaikan jika nama kolom berbeda
        $name_column = 'nama_pembimbing';
        $redirect_path = 'admin/dashboard_guru.php';
        break;
    default:
        // Jika peran tidak valid, atur alert dan kembali ke form login
        $_SESSION['alert'] = [
            'type'  => 'error',
            'title' => 'Gagal!',
            'text'  => 'Peran login yang Anda pilih tidak valid.'
        ];
        header('Location: login_petugas.php');
        exit();
}

// 3. Gunakan Prepared Statement untuk mencari pengguna dengan aman
$stmt = mysqli_prepare($koneksi, "SELECT $id_column, $name_column, password FROM $table_name WHERE $identifier_column = ?");

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $input_identifier);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user_data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // 4. Verifikasi pengguna dan password
    if ($user_data && password_verify($password_input, $user_data['password'])) {
        // --- LOGIN BERHASIL ---
        // Atur semua sesi yang diperlukan di sini
        $_SESSION['user_id'] = $user_data[$id_column];
        $_SESSION['user_role'] = $role_selected;
        $_SESSION['user_name'] = $user_data[$name_column];

        if ($role_selected === 'admin') {
            $_SESSION['admin_status_login'] = 'logged_in';
            $_SESSION['admin'] = 'login';
        } elseif ($role_selected === 'guru_pendamping') {
            $_SESSION['guru_pendamping_status_login'] = 'logged_in';
            $_SESSION['guru_pendamping'] = 'login';
            $_SESSION['id_guru_pendamping']=$id_column;
        }

        // Tutup koneksi dan alihkan ke dashboard
        mysqli_close($koneksi);
        header("Location: $redirect_path");
        exit();
    } else {
        // --- LOGIN GAGAL ---
        // Atur alert untuk username/password salah dan kembali ke form login
        $_SESSION['alert'] = [
            'type'  => 'error',
            'title' => 'Login Gagal',
            'text'  => 'Kombinasi Username/NIP dan Password salah.'
        ];
        mysqli_close($koneksi);
        header('Location: login_petugas.php');
        exit();
    }
} else {
    // --- KESALAHAN SISTEM ---
    // Atur alert untuk kesalahan sistem dan kembali ke form login
    $_SESSION['alert'] = [
        'type'  => 'error',
        'title' => 'Oops...',
        'text'  => 'Terjadi kesalahan pada sistem. Silakan coba lagi nanti.'
    ];
    mysqli_close($koneksi);
    header('Location: login_petugas.php');
    exit();
}