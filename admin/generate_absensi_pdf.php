<?php
session_start();

// Keamanan: Hanya admin atau guru yang boleh mengakses halaman ini
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_admin && !$is_guru) {
    header('Location: ../login.php');
    exit();
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

include 'partials/db.php';

// --- Pengaturan Debugging (Aktifkan jika PDF gagal diunduh) ---
$debug_mode = false; // Setel ke TRUE untuk melihat HTML mentah di browser (bukan PDF)

if ($debug_mode) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// --- Ambil Filter Rentang Tanggal dari URL ---
$tanggal_mulai = $_GET['tanggal_mulai'] ?? date('Y-m-01'); // Default awal bulan
$tanggal_akhir = $_GET['tanggal_akhir'] ?? date('Y-m-t'); // Default akhir bulan
$filter_status = $_GET['status'] ?? 'Semua'; // Filter status (untuk laporan, default Semua)
$keyword = $_GET['keyword'] ?? ''; // Keyword pencarian

// Validasi sederhana tanggal
if (!strtotime($tanggal_mulai) || !strtotime($tanggal_akhir) || $tanggal_mulai > $tanggal_akhir) {
    $_SESSION['alert_message'] = 'Rentang tanggal tidak valid.';
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_title'] = 'Gagal Cetak!';
    header('Location: master_data_absensi_siswa.php');
    exit();
}

// --- LOGIKA BARU UNTUK MENGHITUNG HARI EFEKTIF UNTUK ALFA ---
$today_date = new DateTime(date('Y-m-d')); // Tanggal hari ini
$report_end_date_obj = new DateTime($tanggal_akhir); // Tanggal akhir yang dipilih pengguna

// Tanggal akhir efektif untuk perhitungan ALFA: pilih yang lebih kecil antara tanggal akhir filter atau tanggal hari ini
$effective_end_date_for_alfa_calc_obj = min($today_date, $report_end_date_obj);

// Pastikan tanggal mulai tidak lebih besar dari tanggal akhir efektif
$start_date_for_alfa_calc_obj = new DateTime($tanggal_mulai);
if ($start_date_for_alfa_calc_obj > $effective_end_date_for_alfa_calc_obj) {
    $total_days_for_alfa_calc = 0; // Tidak ada hari yang relevan jika rentang di masa depan
} else {
    $interval_for_alfa = $start_date_for_alfa_calc_obj->diff($effective_end_date_for_alfa_calc_obj);
    $total_days_for_alfa_calc = $interval_for_alfa->days + 1; // +1 untuk jumlah hari
}


// --- Query Agregasi Data Absensi per Siswa dalam Rentang Tanggal ---
$query_sql = "SELECT
                s.id_siswa,
                s.nama_siswa,
                s.kelas,
                s.no_induk,
                j.nama_jurusan,
                tp.nama_tempat_pkl,
                SUM(CASE WHEN as_abs.status_absen = 'Hadir' THEN 1 ELSE 0 END) AS total_hadir,
                SUM(CASE WHEN as_abs.status_absen = 'Sakit' THEN 1 ELSE 0 END) AS total_sakit,
                SUM(CASE WHEN as_abs.status_absen = 'Izin' THEN 1 ELSE 0 END) AS total_izin,
                -- Hitung total absensi yang tercatat (tidak termasuk Alfa otomatis)
                SUM(CASE WHEN as_abs.id_absensi IS NOT NULL THEN 1 ELSE 0 END) AS total_recorded_actual_absensi,
                -- Total Alfa yang akurat: total hari yang harus diabsen dikurangi yang sudah tercatat
                (" . $total_days_for_alfa_calc . " - SUM(CASE WHEN as_abs.id_absensi IS NOT NULL THEN 1 ELSE 0 END)) AS total_alfa_calculated_accurate
            FROM
                siswa s
            LEFT JOIN absensi_siswa as_abs ON s.id_siswa = as_abs.siswa_id
                                           AND as_abs.tanggal_absen BETWEEN ? AND ?
            LEFT JOIN jurusan j ON s.jurusan_id = j.id_jurusan
            LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl
            WHERE s.status = 'Aktif'"; // Hanya siswa aktif

$data_params = [$tanggal_mulai, $tanggal_akhir];
$data_types = 'ss';

// Tambahkan filter keyword jika ada
if (!empty($keyword)) {
    $like_keyword = "%" . $keyword . "%";
    $search_columns = [
        's.nama_siswa',
        's.no_induk',
        's.kelas',
        'j.nama_jurusan',
        'tp.nama_tempat_pkl'
    ];
    $search_conditions = [];
    foreach ($search_columns as $col) {
        $search_conditions[] = "$col LIKE ?";
        $data_params[] = $like_keyword;
        $data_types .= 's';
    }
    $query_sql .= " AND (" . implode(" OR ", $search_conditions) . ")";
}

$query_sql .= " GROUP BY s.id_siswa, s.nama_siswa, s.kelas, j.nama_jurusan, tp.nama_tempat_pkl";

// Tambahkan HAVING untuk filter status setelah agregasi
if (!empty($filter_status) && $filter_status !== 'Semua') {
    if ($filter_status === 'Alfa') {
        // Jika filter 'Alfa', hanya tampilkan siswa yang punya alfa di rentang efektif
        $query_sql .= " HAVING total_alfa_calculated_accurate > 0";
    } else {
        // Untuk status Hadir, Sakit, Izin, filter jika count > 0
        $query_sql .= " HAVING SUM(CASE WHEN as_abs.status_absen = ? THEN 1 ELSE 0 END) > 0";
        $data_params[] = $filter_status;
        $data_types .= 's';
    }
}

$query_sql .= " ORDER BY s.kelas ASC, s.nama_siswa ASC";

$stmt_data = $koneksi->prepare($query_sql);
if ($stmt_data === false) {
    die("Error preparing data query: " . $koneksi->error);
}

// Bind parameter secara dinamis
if (!empty($data_params)) {
    $bind_names = [];
    foreach ($data_params as $key => $value) {
        $bind_name = 'bind' . $key;
        $$bind_name = $value;
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array([$stmt_data, 'bind_param'], array_merge([$data_types], $bind_names));
}

$stmt_data->execute();
$result_absensi = $stmt_data->get_result();
$total_siswa_found = $result_absensi->num_rows;
$stmt_data->close();
$koneksi->close();

// --- Generate HTML untuk PDF ---
$html = '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekap Absensi Siswa - ' . date('d F Y', strtotime($tanggal_mulai)) . ' s.d. ' . date('d F Y', strtotime($tanggal_akhir)) . '</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; margin: 20px; }
        h1 { text-align: center; margin-bottom: 5px; font-size: 18pt; color: #333; }
        h2 { text-align: center; margin-bottom: 20px; font-size: 14pt; color: #555; }
        .info { margin-bottom: 15px; font-size: 10pt; }
        .info p { margin: 0; line-height: 1.5; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #c0c0c0; padding: 10px 8px; text-align: left; vertical-align: top; }
        th { background-color: #e0e0e0; font-weight: bold; text-transform: uppercase; font-size: 9pt; }
        td { font-size: 9pt; }
        .badge {
            display: inline-block;
            padding: 0.3em 0.6em;
            font-size: 85%;
            font-weight: 600;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            color: #fff;
        }
        .bg-label-success { background-color: #28a745 !important; } 
        .bg-label-warning { background-color: #ffc107 !important; } 
        .bg-label-info    { background-color: #17a2b8 !important; } 
        .bg-label-danger  { background-color: #dc3545 !important; } 
        .text-center { text-align: center; }
        .summary-counts span { margin-right: 10px; } 
    </style>
</head>
<body>
    <h1>Laporan Rekapitulasi Absensi Siswa</h1>
    <h2>Periode: ' . date('d F Y', strtotime($tanggal_mulai)) . ' s.d. ' . date('d F Y', strtotime($tanggal_akhir)) . '</h2>
    
    <div class="info">
        <p><strong>Filter Status:</strong> ' . (empty($filter_status) ? 'Semua' : htmlspecialchars($filter_status)) . '</p>
        <p><strong>Kata Kunci Pencarian:</strong> ' . (empty($keyword) ? 'Tidak Ada' : htmlspecialchars($keyword)) . '</p>
        <p><strong>Total Siswa Ditemukan:</strong> ' . $total_siswa_found . '</p>
        <p><strong>Periode Absensi yang Diperhitungkan (untuk Alfa):</strong> ' . date('d F Y', strtotime($tanggal_mulai)) . ' s.d. ' . date('d F Y', strtotime($effective_end_date_for_alfa_calc_obj->format('Y-m-d'))) . ' (' . $total_days_for_alfa_calc . ' Hari)</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Jurusan</th>
                <th>Tempat PKL</th>
                <th>Rekap Absen</th>
            </tr>
        </thead>
        <tbody>';

if ($total_siswa_found > 0) {
    $no = 1;
    while ($row = $result_absensi->fetch_assoc()) {
        $rekap_absen_html = '<div class="summary-counts">';
        $rekap_absen_html .= '<span class="badge bg-label-success">Hadir: ' . $row['total_hadir'] . '</span>';
        $rekap_absen_html .= '<span class="badge bg-label-warning">Sakit: ' . $row['total_sakit'] . '</span>';
        $rekap_absen_html .= '<span class="badge bg-label-info">Izin: ' . $row['total_izin'] . '</span>';

        // Pastikan total_alfa_calculated_accurate tidak negatif
        $actual_alfa = max(0, $row['total_alfa_calculated_accurate']);
        $rekap_absen_html .= '<span class="badge bg-label-danger">Alfa: ' . $actual_alfa . '</span>';
        $rekap_absen_html .= '</div>';

        $html .= '<tr>
                    <td>' . $no++ . '</td>
                    <td>' . htmlspecialchars($row['nama_siswa']) . '</td>
                    <td>' . htmlspecialchars($row['kelas']) . '</td>
                    <td>' . htmlspecialchars($row['nama_jurusan'] ?? '-') . '</td>
                    <td>' . htmlspecialchars($row['nama_tempat_pkl'] ?? '-') . '</td>
                    <td>' . $rekap_absen_html . '</td>
                </tr>';
    }
} else {
    $html .= '<tr><td colspan="6" style="text-align: center; color: #888;">Tidak ada data absensi ditemukan untuk filter ini.</td></tr>';
}

$html .= '</tbody>
    </table>
</body>
</html>';

// --- Debugging: Tampilkan HTML jika debug_mode true ---
if ($debug_mode) {
    echo $html;
    exit();
}

// --- Inisialisasi Dompdf ---
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'landscape');

$dompdf->render();

// --- Keluarkan PDF ke browser (View, bukan Download Otomatis) ---
$filename = 'Rekap_Absensi_Siswa_' . date('Ymd', strtotime($tanggal_mulai)) . '_' . date('Ymd', strtotime($tanggal_akhir)) . '.pdf';
$dompdf->stream($filename, ["Attachment" => false]);
exit();