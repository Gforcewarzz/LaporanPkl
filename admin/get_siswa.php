<?php
session_start();
require_once 'partials/db.php';

// Keamanan: Pastikan hanya user yang login yang bisa akses
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_admin && !$is_guru) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Akses ditolak']);
    exit();
}

header('Content-Type: application/json');

$search_term = $_GET['search'] ?? '';

// Query dasar untuk mengambil data siswa
$sql = "
    SELECT 
        s.id_siswa, 
        s.nama_siswa, 
        s.jurusan_id,
        (SELECT COUNT(*) FROM nilai_siswa ns WHERE ns.siswa_id = s.id_siswa) as jumlah_nilai
    FROM siswa s
    WHERE s.status = 'aktif'
";

$params = [];
$types = '';

// Filter berdasarkan nama jika ada keyword pencarian
if (!empty($search_term)) {
    $sql .= " AND s.nama_siswa LIKE ?";
    $params[] = "%" . $search_term . "%";
    $types .= 's';
}

// Filter berdasarkan guru pembimbing jika yang login adalah guru
if ($is_guru) {
    $sql .= " AND s.pembimbing_id = ?";
    $params[] = $_SESSION['id_guru_pendamping'];
    $types .= 'i';
}

$sql .= " ORDER BY s.nama_siswa ASC LIMIT 20"; // Batasi hasil untuk performa

$stmt = $koneksi->prepare($sql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Query preparation failed: ' . $koneksi->error]);
    exit();
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($students);

$stmt->close();
$koneksi->close();