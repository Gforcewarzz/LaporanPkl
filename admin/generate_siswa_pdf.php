<?php
session_start();
date_default_timezone_set('Asia/Jakarta'); // Pastikan zona waktu konsisten

// Sertakan koneksi database
include 'partials/db.php'; // Pastikan file ini mengembalikan objek $koneksi yang valid dan terbuka

// Sertakan Dompdf Autoloader
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// --- LOGIKA KEAMANAN HALAMAN ---
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_admin && !$is_guru) {
    header('Location: ../login.php'); // Redirect ke halaman login jika tidak berhak
    exit();
}

// --- INISIALISASI FILTER DARI URL ---
$keyword = $_GET['keyword'] ?? '';
$kelas_filter_pdf = $_GET['kelas_pdf'] ?? '';
$pembimbing_id_filter = $_GET['pembimbing_id'] ?? null;

$where_clauses = [];
$query_params = [];
$query_types = "";

// Filter berdasarkan Guru Pembimbing
if ($is_guru) {
    $where_clauses[] = 's.pembimbing_id = ?';
    $query_params[] = $_SESSION['id_guru_pendamping'];
    $query_types .= 'i';
} elseif ($is_admin && !empty($pembimbing_id_filter)) {
    $where_clauses[] = 's.pembimbing_id = ?';
    $query_params[] = $pembimbing_id_filter;
    $query_types .= 'i';
}

// Filter berdasarkan Kelas
if (!empty($kelas_filter_pdf)) {
    $where_clauses[] = 's.kelas = ?';
    $query_params[] = $kelas_filter_pdf;
    $query_types .= 's';
}

// Filter Keyword (umum)
if (!empty($keyword)) {
    $like_keyword = "%" . $keyword . "%";
    $searchable_columns = ['s.nama_siswa', 's.no_induk', 's.nisn', 's.kelas', 'j.nama_jurusan', 'gp.nama_pembimbing', 'tp.nama_tempat_pkl', 's.status'];
    $search_conditions = [];
    foreach ($searchable_columns as $column) {
        $search_conditions[] = "$column LIKE ?";
        $query_params[] = $like_keyword;
        $query_types .= 's';
    }
    $where_clauses[] = "(" . implode(" OR ", $search_conditions) . ")";
}

$filter_sql = "";
if (!empty($where_clauses)) {
    $filter_sql = " WHERE " . implode(" AND ", $where_clauses);
}

// --- QUERY UTAMA UNTUK MENGAMBIL DATA SISWA ---
$query_sql = "
    SELECT
        s.id_siswa, s.nama_siswa, s.no_induk, s.nisn, 
        s.jenis_kelamin, s.kelas, s.status,
        j.nama_jurusan, gp.nama_pembimbing,
        tp.nama_tempat_pkl,
        (SELECT MIN(tanggal_absen) FROM absensi_siswa WHERE siswa_id = s.id_siswa) AS tanggal_absen_pertama
    FROM siswa s
    LEFT JOIN jurusan j ON s.jurusan_id = j.id_jurusan
    LEFT JOIN guru_pembimbing gp ON s.pembimbing_id = gp.id_pembimbing
    LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl
    $filter_sql
    ORDER BY s.nama_siswa ASC";

$stmt = $koneksi->prepare($query_sql);

if ($stmt === false) {
    error_log("Error preparing PDF data query: " . $koneksi->error);
    die("Terjadi kesalahan sistem saat menyiapkan laporan PDF.");
}

if (!empty($query_params)) {
    $bind_args = [];
    $bind_args[] = $query_types;
    foreach ($query_params as &$param) {
        $bind_args[] = &$param;
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_args);
}

$stmt->execute();
$result = $stmt->get_result();
$siswa_data = [];
while ($row = $result->fetch_assoc()) {
    $siswa_data[] = $row;
}
$stmt->close();
$koneksi->close();

// --- Ekstrak informasi unik dari data hasil query untuk ditampilkan ---
$guru_display_from_data = '';
$kelas_display_from_data = '';
$jurusan_display_from_data = '';
if (!empty($siswa_data)) {
    // Ambil semua nama guru, kelas, dan jurusan dari hasil
    $all_gurus = array_column($siswa_data, 'nama_pembimbing');
    $all_kelas = array_column($siswa_data, 'kelas');
    $all_jurusan = array_column($siswa_data, 'nama_jurusan');

    // Filter nilai unik dan hapus nilai kosong
    $unique_gurus = array_unique(array_filter($all_gurus));
    $unique_kelas = array_unique(array_filter($all_kelas));
    $unique_jurusan = array_unique(array_filter($all_jurusan));

    // Hanya set variabel display jika hanya ada SATU nilai unik dalam hasil
    if (count($unique_gurus) === 1) {
        $guru_display_from_data = reset($unique_gurus); // Ambil satu-satunya elemen
    }
    if (count($unique_kelas) === 1) {
        $kelas_display_from_data = reset($unique_kelas);
    }
    if (count($unique_jurusan) === 1) {
        $jurusan_display_from_data = reset($unique_jurusan);
    }
}


// --- GENERASI KONTEN HTML untuk PDF ---
$html = '
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Siswa PKL</title>
    <style>
        body { font-family: "Helvetica", Arial, sans-serif; font-size: 10pt; line-height: 1.4; color: #333; margin: 25px; }
        .header-main { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header-main .title { margin: 0; font-size: 16pt; text-transform: uppercase; letter-spacing: 1px; }
        .header-main .subtitle { margin: 5px 0 0 0; font-size: 12pt; font-weight: normal; }
        .info-section { margin-bottom: 20px; font-size: 10pt; text-align: left; }
        .info-section div { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 7px 10px; text-align: left; vertical-align: top; font-size: 8.5pt; line-height: 1.3; }
        th { background-color: #e9e9e9; font-weight: bold; color: #333; }
        tr:nth-child(even) { background-color: #f8f8f8; }
        .no-data { text-align: center; padding: 20px; color: #777; font-style: italic; }
        .footer-timestamp { text-align: right; font-size: 8pt; color: #888; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="header-main">
        <div class="title">Rekapitulasi Siswa Praktik Kerja Lapangan</div>
        <div class="subtitle">DATA SISWA SMKN 1 GANTAR</div>
    </div>';

// Menampilkan informasi Guru, Kelas, dan Jurusan di kiri atas, diambil dari data
if (!empty($guru_display_from_data) || !empty($kelas_display_from_data) || !empty($jurusan_display_from_data)) {
    $html .= '<div class="info-section">';
    if (!empty($guru_display_from_data)) {
        $html .= '<div><b>Pembimbing Sekolah:</b> ' . htmlspecialchars($guru_display_from_data) . '</div>';
    }
    if (!empty($kelas_display_from_data)) {
        $html .= '<div><b>Kelas:</b> ' . htmlspecialchars($kelas_display_from_data) . '</div>';
    }
    if (!empty($jurusan_display_from_data)) {
        $html .= '<div><b>Jurusan:</b> ' . htmlspecialchars($jurusan_display_from_data) . '</div>';
    }
    $html .= '</div>';
}

if (!empty($siswa_data)) {
    $html .= '<table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>Tempat PKL</th>
                <th>Tanggal Mulai PKL</th>
            </tr>
        </thead>
        <tbody>';
    $no = 1;
    foreach ($siswa_data as $row) {
        $absen_pertama_display = !empty($row['tanggal_absen_pertama']) ? date('d F Y', strtotime($row['tanggal_absen_pertama'])) : 'Belum Absen';
        $html .= '
            <tr>
                <td>' . $no++ . '</td>
                <td>' . htmlspecialchars($row['nama_siswa']) . '</td>
                <td>' . htmlspecialchars($row['nama_tempat_pkl'] ?? '-') . '</td>
                <td>' . $absen_pertama_display . '</td>
            </tr>';
    }
    $html .= '
        </tbody>
    </table>';
} else {
    $html .= '<p class="no-data">Tidak ada data siswa ditemukan untuk kriteria ini.</p>';
}

$html .= '
    <div class="footer-timestamp">
        Laporan ini dibuat pada ' . date('d F Y H:i:s') . ' WIB.
    </div>
</body>
</html>';

// --- KONFIGURasi DOMPDF DAN OUTPUT PDF ---
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Helvetica');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Atur nama file PDF
$filename = "Rekap_Siswa_PKL_" . date('Ymd_His');
if (!empty($kelas_filter_pdf)) {
    $filename .= "_Kelas_" . str_replace(' ', '_', $kelas_filter_pdf);
}
if ($is_guru && !empty($_SESSION['guru_nama'])) {
    $filename .= "_Guru_" . str_replace(' ', '_', $_SESSION['guru_nama']);
}
$filename .= ".pdf";

// Output PDF ke browser
$dompdf->stream($filename, ["Attachment" => false]);
exit();
