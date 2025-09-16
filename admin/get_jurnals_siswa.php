<?php
session_start();
require_once 'partials/db.php';

// Keamanan dasar
if (!isset($_SESSION['admin_status_login']) && !isset($_SESSION['guru_pendamping_status_login'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit();
}

header('Content-Type: application/json');

$siswa_id = isset($_GET['siswa_id']) ? (int)$_GET['siswa_id'] : 0;
if ($siswa_id === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID Siswa tidak valid']);
    exit();
}

$stmt_jurnal = $koneksi->prepare(
    "SELECT id_jurnal_kegiatan, nama_pekerjaan, tanggal_laporan FROM jurnal_kegiatan 
     WHERE siswa_id = ? ORDER BY tanggal_laporan DESC"
);
$stmt_jurnal->bind_param("i", $siswa_id);
$stmt_jurnal->execute();
$jurnal_list = $stmt_jurnal->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_jurnal->close();

echo json_encode($jurnal_list);