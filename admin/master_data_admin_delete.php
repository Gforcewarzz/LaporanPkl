<?php
include 'partials/db.php'; // Pastikan path ini benar
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

$id_admin_to_delete = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : null;

if (!$id_admin_to_delete) {
    $_SESSION['admin_message'] = 'ID Admin tidak ditemukan untuk dihapus.';
    $_SESSION['admin_message_type'] = 'error';
    $_SESSION['admin_message_title'] = 'Gagal!';
    header('Location: master_data_admin.php');
    exit();
}

// Pencegahan: Admin tidak bisa menghapus akunnya sendiri
if ($id_admin_to_delete == $_SESSION['user_id']) {
    $_SESSION['admin_message'] = 'Anda tidak dapat menghapus akun admin Anda sendiri saat ini sedang login.';
    $_SESSION['admin_message_type'] = 'warning';
    $_SESSION['admin_message_title'] = 'Peringatan!';
    header('Location: master_data_admin.php');
    exit();
}

$delete_stmt = mysqli_prepare($koneksi, "DELETE FROM admin WHERE id_admin = ?");
if ($delete_stmt) {
    mysqli_stmt_bind_param($delete_stmt, "i", $id_admin_to_delete);
    if (mysqli_stmt_execute($delete_stmt)) {
        $_SESSION['admin_message'] = 'Data admin berhasil dihapus!';
        $_SESSION['admin_message_type'] = 'success';
        $_SESSION['admin_message_title'] = 'Berhasil!';
    } else {
        $_SESSION['admin_message'] = 'Gagal menghapus data admin: ' . mysqli_error($koneksi);
        $_SESSION['admin_message_type'] = 'error';
        $_SESSION['admin_message_title'] = 'Gagal!';
    }
    mysqli_stmt_close($delete_stmt);
} else {
    $_SESSION['admin_message'] = 'Terjadi kesalahan pada query delete: ' . mysqli_error($koneksi);
    $_SESSION['admin_message_type'] = 'error';
    $_SESSION['admin_message_title'] = 'Gagal!';
}

mysqli_close($koneksi);
header('Location: master_data_admin.php');
exit();