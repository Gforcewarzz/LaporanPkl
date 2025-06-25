<?php
session_start();
include 'admin/partials/db.php'; // Pastikan file ini mendefinisikan $koneksi

// Ambil input dari form
$nisn = trim($_POST['nisn']);
$password = trim($_POST['password']);

// Validasi input
if (empty($nisn) || empty($password)) {
    echo "<script>alert('NISN dan Password wajib diisi!');window.location='login.php';</script>";
    exit;
}

// Ambil data siswa berdasarkan NISN
$query = mysqli_query($koneksi, "SELECT * FROM siswa WHERE nisn = '$nisn'");
if (!$query) {
    die("Query error: " . mysqli_error($koneksi));
}

$data = mysqli_fetch_assoc($query);

// Cek apakah NISN ditemukan
if ($data) {
    $hash_dari_database = $data['password'];

    // Cek password cocok atau tidak
    if (password_verify($password, $hash_dari_database)) {
        // Login berhasil
        $_SESSION['id_siswa'] = $data['id_siswa'];
        $_SESSION['siswa'] = 'login';

        header("Location: admin/master_kegiatan_harian.php");
        exit;
    } else {
        // Password salah
        echo "<script>alert('Password salah!');window.location='login.php';</script>";
        exit;
    }
} else {
    // NISN tidak ditemukan
    echo "<script>alert('NISN tidak ditemukan!');window.location='login.php';</script>";
    exit;
}
?>
