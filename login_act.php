<?php
session_start(); // Biarkan session_start() di sini

include 'admin/partials/db.php'; // Pastikan path ini benar relatif dari lokasi login_act.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nisn = trim($_POST['nisn']);
    $password = trim($_POST['password']);

    // --- PENTING: Jika form login universal, Anda perlu tahu role yang dipilih ---
    // Di sini, karena ini aksi login siswa, kita asumsikan role-nya adalah 'siswa'.
    // Jika form login Anda satu untuk semua (siswa, admin, guru), maka 'role' harus diambil dari $_POST.
    // Untuk saat ini, kita asumsikan ini hanya untuk login siswa.
    $role_selected = 'siswa'; // Set role secara eksplisit karena ini aksi login siswa

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

            // --- PERBAIKAN DI SINI: Atur sesi universal yang dibaca navbar ---
            $_SESSION['user_id'] = $data_siswa['id_siswa'];
            $_SESSION['user_role'] = $role_selected; // Akan menjadi 'siswa'
            $_SESSION['user_name'] = $data_siswa['nama_siswa'];

            // --- Sesi spesifik lama (pertahankan jika masih digunakan di tempat lain) ---
            $_SESSION['id_siswa'] = $data_siswa['id_siswa'];
            $_SESSION['siswa_nama'] = $data_siswa['nama_siswa'];
            $_SESSION['siswa_status_login'] = 'logged_in';

            mysqli_close($koneksi);
            header("Location: admin/dashboard_siswa.php"); // Redirect ke dashboard siswa
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
