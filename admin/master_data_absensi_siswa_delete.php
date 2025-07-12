<?php
session_start();

// Keamanan: Hanya admin atau guru yang boleh mengakses halaman ini
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_admin && !$is_guru) {
    $_SESSION['alert_message'] = 'Anda tidak memiliki izin untuk melakukan aksi ini.';
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_title'] = 'Akses Ditolak!';
    header('Location: ../login.php');
    exit();
}

include 'partials/db.php'; // Sertakan file koneksi database

$id_absensi = $_GET['id'] ?? null;

if (empty($id_absensi)) {
    $_SESSION['alert_message'] = 'ID Absensi tidak ditemukan.';
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_title'] = 'Error!';
    header('Location: master_data_absensi_siswa.php');
    exit();
}

// 1. Ambil nama file bukti foto sebelum menghapus record dari DB
$bukti_foto_to_delete = null;
$query_get_file = "SELECT bukti_foto FROM absensi_siswa WHERE id_absensi = ?";
$stmt_get_file = $koneksi->prepare($query_get_file);
if ($stmt_get_file) {
    $stmt_get_file->bind_param("i", $id_absensi);
    $stmt_get_file->execute();
    $result_get_file = $stmt_get_file->get_result();
    if ($row = $result_get_file->fetch_assoc()) {
        $bukti_foto_to_delete = $row['bukti_foto'];
    }
    $stmt_get_file->close();
}

// 2. Hapus record dari database
$delete_stmt = $koneksi->prepare("DELETE FROM absensi_siswa WHERE id_absensi = ?");

if ($delete_stmt) {
    $delete_stmt->bind_param("i", $id_absensi);
    if ($delete_stmt->execute()) {
        // 3. Hapus file fisik jika ada
        if (!empty($bukti_foto_to_delete)) {
            $file_path = "image_absensi/" . $bukti_foto_to_delete; // Pastikan path ini benar
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        $_SESSION['alert_message'] = 'Absensi berhasil dihapus!';
        $_SESSION['alert_type'] = 'success';
        $_SESSION['alert_title'] = 'Hapus Berhasil!';
    } else {
        $_SESSION['alert_message'] = 'Gagal menghapus absensi: ' . $delete_stmt->error;
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_title'] = 'Gagal Hapus!';
        error_log("Error deleting attendance: " . $delete_stmt->error);
    }
    $delete_stmt->close();
} else {
    $_SESSION['alert_message'] = 'Terjadi kesalahan internal saat menyiapkan query hapus.';
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_title'] = 'Error Database!';
    error_log("Error preparing delete_stmt: " . $koneksi->error);
}

$koneksi->close();
header('Location: master_data_absensi_siswa.php'); // Redirect kembali ke halaman daftar absensi
exit();