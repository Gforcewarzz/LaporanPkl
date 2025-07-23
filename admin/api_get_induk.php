<?php
require_once 'partials/db.php';

// Ambil jurusan_id dari request, 0 berarti "Semua Jurusan"
$jurusan_id = isset($_GET['jurusan_id']) ? (int)$_GET['jurusan_id'] : 0;

// Siapkan query berdasarkan jurusan yang dipilih
$query_tp_sql = "SELECT * FROM tujuan_pembelajaran";
$params = [];
$types = '';

if ($jurusan_id > 0) {
    $query_tp_sql .= " WHERE jurusan_id = ? OR jurusan_id IS NULL";
    $params[] = $jurusan_id;
    $types .= 'i';
}
$query_tp_sql .= " ORDER BY id_induk, kode_tp";

// Eksekusi query
$stmt = $koneksi->prepare($query_tp_sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$tp_result = $stmt->get_result();

$semua_tp = [];
$tp_anak = [];
while($row = $tp_result->fetch_assoc()){
    $semua_tp[$row['id_tp']] = $row;
    $tp_anak[$row['id_induk']][] = $row['id_tp'];
}

// Fungsi rekursif untuk membuat opsi dropdown
function generate_tp_options_api($id_induk, $semua_tp, $tp_anak, $level = 0) {
    if (!isset($tp_anak[$id_induk])) return;
    
    $indent = str_repeat('&nbsp;&nbsp;&nbsp;↳&nbsp;', $level);
    foreach ($tp_anak[$id_induk] as $id_tp) {
        $item = $semua_tp[$id_tp];
        // Kirim output HTML
        echo "<option value='{$item['id_tp']}'>{$indent}" . htmlspecialchars($item['kode_tp']) . " - " . htmlspecialchars($item['deskripsi_tp']) . "</option>";
        generate_tp_options_api($id_tp, $semua_tp, $tp_anak, $level + 1);
    }
}

// Panggil fungsi untuk menghasilkan output
generate_tp_options_api(NULL, $semua_tp, $tp_anak);

$koneksi->close();
?>