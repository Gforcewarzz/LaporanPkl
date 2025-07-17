<?php
session_start();
date_default_timezone_set('Asia/Jakarta'); // Pastikan zona waktu konsisten

// Sertakan koneksi database
include 'partials/db.php'; // Pastikan file ini mengembalikan objek $koneksi yang valid dan terbuka

// Sertakan Dompdf Autoloader
// PENTING: Jalur ini diasumsikan file PHP ini ada di folder yang SAMA dengan folder 'vendor'.
// Contoh: Jika generate_siswa_pdf.php ada di 'admin/' dan 'vendor' juga ada di 'admin/vendor/'.
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// --- LOGIKA KEAMANAN HALAMAN ---
// Pastikan hanya admin atau guru yang bisa mengakses laporan PDF ini
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_admin && !$is_guru) {
    header('Location: ../login.php'); // Redirect ke halaman login jika tidak berhak
    exit();
}

// --- INISIALISASI FILTER DARI URL ---
$keyword = $_GET['keyword'] ?? '';
$kelas_filter_pdf = $_GET['kelas_pdf'] ?? '';
$pembimbing_id_filter = $_GET['pembimbing_id'] ?? null; // Digunakan jika admin/guru lain filter per guru

$where_clauses = [];
$query_params = [];
$query_types = "";
$filter_info_display = []; // Untuk menampilkan informasi filter di header PDF

// [TAMBAH] Filter berdasarkan Guru Pembimbing
// Jika guru yang login, atau admin melihat siswa bimbingan guru tertentu
if ($is_guru) {
    $where_clauses[] = 's.pembimbing_id = ?';
    $query_params[] = $_SESSION['id_guru_pendamping'];
    $query_types .= 'i';
    $filter_info_display[] = "Guru Pembimbing: " . ($_SESSION['guru_nama'] ?? 'Tidak Dikenal');
} elseif ($is_admin && $pembimbing_id_filter !== null) {
    // Jika admin mencetak laporan untuk guru tertentu (diteruskan dari URL)
    $where_clauses[] = 's.pembimbing_id = ?';
    $query_params[] = $pembimbing_id_filter;
    $query_types .= 'i';
    // Ambil nama guru untuk display info
    // Perbaikan: Pastikan koneksi masih terbuka sebelum query ini
    if ($koneksi && $koneksi->connect_errno === 0) {
        $stmt_guru_name = $koneksi->prepare("SELECT nama_pembimbing FROM guru_pembimbing WHERE id_pembimbing = ?");
        if ($stmt_guru_name) {
            $stmt_guru_name->bind_param("i", $pembimbing_id_filter);
            $stmt_guru_name->execute();
            $guru_name_res = $stmt_guru_name->get_result()->fetch_assoc();
            if ($guru_name_res) $filter_info_display[] = "Untuk Guru: " . htmlspecialchars($guru_name_res['nama_pembimbing']);
            $stmt_guru_name->close();
        }
    }
}

// [TAMBAH] Filter berdasarkan Kelas
if (!empty($kelas_filter_pdf)) {
    $where_clauses[] = 's.kelas = ?';
    $query_params[] = $kelas_filter_pdf;
    $query_types .= 's';
    $filter_info_display[] = "Kelas: " . htmlspecialchars($kelas_filter_pdf);
}

// Filter Keyword (umum)
if (!empty($keyword)) {
    $like_keyword = "%" . $keyword . "%";
    $searchable_columns = [
        's.nama_siswa',
        's.no_induk',
        's.nisn',
        's.kelas',
        'j.nama_jurusan',
        'gp.nama_pembimbing',
        'tp.nama_tempat_pkl',
        's.status'
    ];
    $search_conditions = [];
    foreach ($searchable_columns as $column) {
        $search_conditions[] = "$column LIKE ?";
        $query_params[] = $like_keyword;
        $query_types .= 's';
    }
    $where_clauses[] = "(" . implode(" OR ", $search_conditions) . ")";
    $filter_info_display[] = "Kata Kunci: \"" . htmlspecialchars($keyword) . "\"";
}

// Bangun klausa WHERE akhir
$filter_sql = "";
if (!empty($where_clauses)) {
    $filter_sql = " WHERE " . implode(" AND ", $where_clauses);
}

// --- QUERY UTAMA UNTUK MENGAMBIL DATA SISWA DENGAN TANGGAL ABSENSI PERTAMA ---
// Menggunakan subquery untuk mendapatkan tanggal absen pertama dan JOIN tabel lain
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
    ORDER BY s.nama_siswa ASC"; // PERUBAHAN UTAMA: Hanya urutkan berdasarkan nama siswa ASC

$stmt = $koneksi->prepare($query_sql);

// Cek jika prepared statement gagal
if ($stmt === false) {
    error_log("Error preparing PDF data query: " . $koneksi->error);
    die("Terjadi kesalahan sistem saat menyiapkan laporan PDF.");
}

// Bind parameter ke prepared statement
if (!empty($query_params)) {
    // Membangun array argumen untuk call_user_func_array dengan referensi
    $bind_args = [];
    $bind_args[] = $query_types; // String tipe adalah elemen pertama
    foreach ($query_params as &$param) { // Looping dengan referensi
        $bind_args[] = &$param; // Tambahkan referensi setiap parameter
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
$koneksi->close(); // Tutup koneksi setelah semua data diambil

// --- GENERASI KONTEN HTML untuk PDF ---
$html = '
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Siswa PKL</title>
    <style>
        body { font-family: "Helvetica Neue", "Helvetica", Arial, sans-serif; font-size: 9pt; line-height: 1.4; color: #333; margin: 25px; }
        h1 { text-align: center; color: #222; font-size: 16pt; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.5px; }
        .header-info { margin-bottom: 25px; font-size: 9.5pt; text-align: center; }
        .header-info div { margin-bottom: 3px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 7px 10px; text-align: left; vertical-align: top; font-size: 8.5pt; line-height: 1.3; }
        th { background-color: #e0e0e0; font-weight: bold; color: #444; text-transform: uppercase; }
        tr:nth-child(even) { background-color: #f5f5f5; }
        .no-data { text-align: center; padding: 20px; color: #777; font-style: italic; }
        .footer-timestamp { text-align: right; font-size: 7pt; color: #888; margin-top: 30px; }
    </style>
</head>
<body>
    <h1>Data Siswa Peserta PKL</h1>';

// Menampilkan informasi filter di header PDF
if (!empty($filter_info_display)) {
    $html .= '<div class="header-info">';
    foreach ($filter_info_display as $info) {
        $html .= '<div>' . $info . '</div>';
    }
    $html .= '</div>';
} else {
    $html .= '<div class="header-info"><div>Semua Siswa</div></div>';
}

if (!empty($siswa_data)) {
    $html .= '<table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Jurusan</th>
                <th>Guru Pembimbing</th>
                <th>Tempat PKL</th>
                <th>Absen Pertama</th>
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
                <td>' . htmlspecialchars($row['kelas']) . '</td>
                <td>' . htmlspecialchars($row['nama_jurusan'] ?? '-') . '</td>
                <td>' . htmlspecialchars($row['nama_pembimbing'] ?? '-') . '</td>
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

// --- KONFIGURASI DOMPDF DAN OUTPUT PDF ---
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Helvetica'); // Atau font lain yang sesuai

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Atur nama file PDF
$filename = "Data_Siswa_PKL_" . date('Ymd_His');
if (!empty($kelas_filter_pdf)) {
    $filename .= "_Kelas_" . str_replace(' ', '_', $kelas_filter_pdf);
}
// Tambahkan nama guru jika filter diterapkan oleh guru atau admin melihat filter guru
if ($is_guru && !empty($_SESSION['guru_nama'])) {
    $filename .= "_Guru_" . str_replace(' ', '_', $_SESSION['guru_nama']);
} elseif ($is_admin && $pembimbing_id_filter !== null) {
    // Perbaikan: Ambil nama guru dari database jika tidak ada di sesi (misal admin melihat guru lain)
    // Gunakan koneksi singkat baru di sini karena $koneksi utama sudah ditutup.
    $temp_koneksi_for_filename = new mysqli($host, $username, $password, $database);
    if ($temp_koneksi_for_filename->connect_errno === 0) {
        $stmt_guru_name_filename = $temp_koneksi_for_filename->prepare("SELECT nama_pembimbing FROM guru_pembimbing WHERE id_pembimbing = ?");
        if ($stmt_guru_name_filename) {
            $stmt_guru_name_filename->bind_param("i", $pembimbing_id_filter);
            $stmt_guru_name_filename->execute();
            $temp_guru_res = $stmt_guru_name_filename->get_result()->fetch_assoc();
            if ($temp_guru_res) $filename .= "_Guru_" . str_replace(' ', '_', $temp_guru_res['nama_pembimbing']);
            $stmt_guru_name_filename->close();
        }
        $temp_koneksi_for_filename->close();
    }
}
$filename .= ".pdf";


// Output PDF ke browser
$dompdf->stream($filename, ["Attachment" => false]);
exit();
