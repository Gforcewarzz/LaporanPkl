<?php
include 'partials/db.php'; // Pastikan path ini benar
session_start();

// if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
//     header('Location: ../login.php');
//     exit();
// }

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