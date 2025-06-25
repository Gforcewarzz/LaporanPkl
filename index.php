<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['siswa'])) {
    // Jika belum login, redirect ke login.php
    header("Location: login.php");
    exit;
}else{
    header("Location: master_kegiatan_harian");
}

// Jika sudah login, bisa arahkan ke dashboard atau tampilkan konten

?>
