<?php
header('Content-Type: application/json');
require_once 'partials/db.php';

// Validasi input
$jurusan_id = isset($_GET['jurusan_id']) ? (int)$_GET['jurusan_id'] : 0;
$siswa_id = isset($_GET['siswa_id']) ? (int)$_GET['siswa_id'] : 0; // Ambil siswa_id

if ($jurusan_id === 0 || $siswa_id === 0) {
    echo json_encode(['error' => 'ID Jurusan atau Siswa tidak valid.']);
    exit;
}

// 1. Ambil semua TP yang relevan (tidak ada perubahan di sini)
$query_tp = "SELECT id_tp, id_induk, kode_tp, deskripsi_tp 
             FROM tujuan_pembelajaran 
             WHERE jurusan_id = ? OR jurusan_id IS NULL 
             ORDER BY id_induk, kode_tp";
$stmt_tp = $koneksi->prepare($query_tp);
$stmt_tp->bind_param("i", $jurusan_id);
$stmt_tp->execute();
$tp_result = $stmt_tp->get_result();

$semua_tp = [];
$tp_anak = [];
while($row = $tp_result->fetch_assoc()){
    $semua_tp[$row['id_tp']] = $row;
    $tp_anak[$row['id_induk']][] = $row['id_tp'];
}
$stmt_tp->close();


// 2. Ambil semua ID TP level terdalam (tidak ada perubahan di sini)
$leaf_node_ids = [];
if (!empty($semua_tp)) {
    $parent_ids = array_keys($tp_anak);
    foreach ($semua_tp as $id => $tp_data) {
        if (!in_array($id, $parent_ids)) {
            $leaf_node_ids[] = $id;
        }
    }
}

// --- PERUBAHAN DI SINI ---
// 3. Ambil nilai yang sudah ada untuk siswa yang dipilih
$nilai_siswa = [];
$query_nilai = "SELECT id_tp, nilai FROM nilai_siswa WHERE siswa_id = ?";
$stmt_nilai = $koneksi->prepare($query_nilai);
$stmt_nilai->bind_param("i", $siswa_id);
$stmt_nilai->execute();
$nilai_result = $stmt_nilai->get_result();
while($row = $nilai_result->fetch_assoc()) {
    // Buat array asosiatif: [id_tp => nilai]
    $nilai_siswa[$row['id_tp']] = $row['nilai'];
}
$stmt_nilai->close();
// --- AKHIR PERUBAHAN ---

// Kembalikan semua data dalam format JSON, termasuk data nilai
echo json_encode([
    'semua_tp' => $semua_tp,
    'tp_anak' => $tp_anak,
    'leaf_node_ids' => $leaf_node_ids,
    'nilai_siswa' => $nilai_siswa // Tambahkan nilai siswa ke response
]);

$koneksi->close();
?>