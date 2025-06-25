<?php
session_start(); // Biarkan session_start() di sini untuk login_act.php

include 'admin/partials/db.php'; // Pastikan path ini benar dari lokasi login_act.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nisn = trim($_POST['nisn']);
    $password = trim($_POST['password']);

    if (empty($nisn) || empty($password)) {
        mysqli_close($koneksi);
        echo "<script>alert('NISN dan Password wajib diisi!');window.location='login.php';</script>";
        exit;
    }

    $stmt = mysqli_prepare($koneksi, "SELECT id_siswa, nama_siswa, password FROM siswa WHERE nisn = ?");

    if ($stmt === false) {
        mysqli_close($koneksi);
        die("Error mempersiapkan statement: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt, "s", $nisn);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data_siswa = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if ($data_siswa) {
        $hash_dari_database = $data_siswa['password'];

        if (password_verify($password, $hash_dari_database)) {
            // Login berhasil, simpan data ke session
            $_SESSION['id_siswa'] = $data_siswa['id_siswa'];
            $_SESSION['siswa_nama'] = $data_siswa['nama_siswa'];
            $_SESSION['siswa_status_login'] = 'logged_in';

            mysqli_close($koneksi);
            header("Location: admin/master_kegiatan_harian.php"); // Redirect ke dashboard siswa
            exit;
        } else {
            // Password salah
            mysqli_close($koneksi);
            echo "<script>alert('Password salah!');window.location='login.php';</script>";
            exit;
        }
    } else {
        // NISN tidak ditemukan
        mysqli_close($koneksi);
        echo "<script>alert('NISN tidak ditemukan!');window.location='login.php';</script>";
        exit;
    }
} else {
    // Jika akses bukan dari POST (misalnya langsung diakses via URL), redirect ke halaman login
    header("Location: login.php");
    exit;
}
// Tidak ada kode lain di sini setelah blok 'else' terakhir. HAPUS SEMUA YANG ADA DI BAWAH INI