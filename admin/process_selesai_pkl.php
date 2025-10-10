<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
include 'partials/db.php';

function redirectWithMessage($koneksi, $message, $type, $title)
{
    $_SESSION['alert_message'] = $message;
    $_SESSION['alert_type'] = $type;
    $_SESSION['alert_title'] = $title;
    if ($koneksi) $koneksi->close();
    header('Location: dashboard_siswa.php');
    exit();
}

if (!isset($_SESSION['siswa_status_login']) || $_SESSION['siswa_status_login'] !== 'logged_in' || empty($_SESSION['id_siswa'])) {
    redirectWithMessage($koneksi, 'Login diperlukan.', 'error', 'Akses Ditolak!');
}

$siswa_id = $_SESSION['id_siswa'];
$tanggal_selesai = trim($_POST['tanggal_selesai'] ?? '');

if (!$tanggal_selesai) {
    redirectWithMessage($koneksi, 'Tanggal selesai wajib diisi.', 'error', 'Data Tidak Lengkap!');
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_selesai) || strtotime($tanggal_selesai) > strtotime(date('Y-m-d'))) {
    redirectWithMessage($koneksi, 'Tanggal tidak valid.', 'error', 'Tanggal Salah!');
}

// ✅ HANYA UPDATE TABEL SISWA — TIDAK INSERT KE ABSENSI
$stmt = $koneksi->prepare("UPDATE siswa SET status = 'Selesai', tanggal_selesai_pkl = ? WHERE id_siswa = ?");
if ($stmt && $stmt->bind_param("si", $tanggal_selesai, $siswa_id) && $stmt->execute()) {
    redirectWithMessage($koneksi, 'PKL Anda telah berhasil diakhiri!', 'success', 'PKL Selesai!');
} else {
    error_log("Gagal update status selesai: " . ($stmt ? $stmt->error : $koneksi->error));
    redirectWithMessage($koneksi, 'Gagal mengakhiri PKL.', 'error', 'Terjadi Kesalahan!');
}
