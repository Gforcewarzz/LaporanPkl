<?php
session_start();

// Definisikan status login untuk setiap peran
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

// Logika Pengalihan Dashboard Berdasarkan Peran
if ($is_admin) {
    // Jika admin login, arahkan ke dashboard admin
    header("Location: admin/index.php");
    exit();
} elseif ($is_siswa) {
    // Jika siswa login, arahkan ke dashboard siswa
    header("Location: admin/dashboard_siswa.php");
    exit();
} elseif ($is_guru) {
    // Jika guru login, arahkan ke halaman guru (diasumsikan ada di luar folder admin, sesuai path di code sebelumnya)
    header("Location: admin/halaman_guru.php");
    exit();
} else {
    // Jika tidak ada peran yang login, arahkan ke halaman login
    header("Location: login.php");
    exit();
}

// Tidak ada konten HTML di sini karena halaman ini hanya untuk redirect