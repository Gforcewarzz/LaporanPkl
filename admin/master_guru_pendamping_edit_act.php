<?php

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
include 'partials/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = mysqli_real_escape_string($koneksi, $_POST['id_pembimbing']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_pembimbing']);
    $nip = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $password = $_POST['password'];

    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE guru_pembimbing SET 
                    nama_pembimbing = '$nama', 
                    nip = '$nip', 
                    password = '$password_hash'
                  WHERE id_pembimbing = '$id'";
    } else {
        $query = "UPDATE guru_pembimbing SET 
                    nama_pembimbing = '$nama', 
                    nip = '$nip'
                  WHERE id_pembimbing = '$id'";
    }

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data guru berhasil diperbarui.'); window.location.href = 'master_guru_pendamping.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data.'); window.history.back();</script>";
    }
} else {
    header('Location: master_guru_pendamping.php');
    exit;
}
?>
