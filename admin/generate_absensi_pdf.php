<?php
session_start();

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

$debug_mode = false;

if ($debug_mode) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

$tanggal_mulai = $_GET['tanggal_mulai'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['tanggal_akhir'] ?? date('Y-m-t');
$filter_status = $_GET['status'] ?? 'Semua';
$keyword = $_GET['keyword'] ?? '';

if (!strtotime($tanggal_mulai) || !strtotime($tanggal_akhir) || $tanggal_mulai > $tanggal_akhir) {
    $_SESSION['alert_message'] = 'Rentang tanggal tidak valid.';
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_title'] = 'Gagal Cetak!';
    header('Location: master_data_absensi_siswa.php');
    exit();
}

$start_date_obj = new DateTime($tanggal_mulai);
$end_date_obj = new DateTime($tanggal_akhir);
$today_date_obj = new DateTime(date('Y-m-d'));

$interval_filter = $start_date_obj->diff($end_date_obj);
$total_days_in_filter_range = $interval_filter->days + 1;

$effective_end_date_for_alfa_calc_obj = min($today_date_obj, $end_date_obj);
$interval_for_alfa_calc = $start_date_obj->diff($effective_end_date_for_alfa_calc_obj);
$total_days_for_alfa_calc_up_to_today_or_end_date = $interval_for_alfa_calc->days + 1;


$query_base = "
    SELECT
        s.id_siswa,
        s.nama_siswa,
        s.kelas,
        s.no_induk,
        j.nama_jurusan,
        tp.nama_tempat_pkl,
        SUM(CASE WHEN as_abs.status_absen = 'Hadir' THEN 1 ELSE 0 END) AS total_hadir,
        SUM(CASE WHEN as_abs.status_absen = 'Sakit' THEN 1 ELSE 0 END) AS total_sakit,
        SUM(CASE WHEN as_abs.status_absen = 'Izin' THEN 1 ELSE 0 END) AS total_izin,
        SUM(CASE WHEN as_abs.status_absen = 'Libur' THEN 1 ELSE 0 END) AS total_libur,
        SUM(CASE WHEN as_abs.status_absen = 'Alfa' THEN 1 ELSE 0 END) AS total_alfa_manual,
        COUNT(as_abs.id_absensi) AS total_recorded_entries
    FROM
        siswa s
    LEFT JOIN absensi_siswa as_abs ON s.id_siswa = as_abs.siswa_id
                                   AND as_abs.tanggal_absen BETWEEN ? AND ?
    LEFT JOIN jurusan j ON s.jurusan_id = j.id_jurusan
    LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl
    WHERE s.status = 'Aktif'";

$data_params = [$tanggal_mulai, $tanggal_akhir];
$data_types = 'ss';

// Guru-specific filter logic
if ($is_guru) {
    $guru_id_session = $_SESSION['id_guru_pendamping'] ?? null;
    if ($guru_id_session) {
        $query_base .= " AND s.pembimbing_id = ?";
        $data_params[] = $guru_id_session;
        $data_types .= 'i';
    } else {
        $query_base .= " AND 1 = 0"; // Prevent showing any data if guru ID is not set
    }
}

// Keyword filter
if (!empty($keyword)) {
    $like_keyword = "%" . $keyword . "%";
    $search_columns = [
        's.nama_siswa',
        's.no_induk',
        's.nisn',
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
    $query_base .= " AND (" . implode(" OR ", $search_conditions) . ")";
}

$query_base .= " GROUP BY s.id_siswa, s.nama_siswa, s.kelas, j.nama_jurusan, tp.nama_tempat_pkl";

// Status filter
if (!empty($filter_status) && $filter_status !== 'Semua') {
    if ($filter_status !== 'Alfa') {
        $query_base .= " HAVING SUM(CASE WHEN as_abs.status_absen = ? THEN 1 ELSE 0 END) > 0";
        $data_params[] = $filter_status;
        $data_types .= 's';
    }
    // 'Alfa' status is filtered in PHP after calculation
}

$query_base .= " ORDER BY s.kelas ASC, s.nama_siswa ASC";

$stmt_data = $koneksi->prepare($query_base);
if ($stmt_data === false) {
    die("Error preparing data query: " . $koneksi->error);
}

if (!empty($data_params)) {
    $bind_args = [$data_types];
    foreach ($data_params as $key => $value) {
        $bind_args[] = &$data_params[$key];
    }
    call_user_func_array([$stmt_data, 'bind_param'], $bind_args);
}

$stmt_data->execute();
$result_absensi = $stmt_data->get_result();

$final_rekap_data_for_pdf = [];
while ($row = $result_absensi->fetch_assoc()) {
    $days_to_be_accounted_for = max(0, $total_days_for_alfa_calc_up_to_today_or_end_date - $row['total_libur']);
    $total_present_sick_izin_alfa_manual = $row['total_hadir'] + $row['total_sakit'] + $row['total_izin'] + $row['total_alfa_manual'];
    $alfa_auto_calculated = max(0, $days_to_be_accounted_for - $total_present_sick_izin_alfa_manual);
    $row['total_alfa_calculated'] = $alfa_auto_calculated;

    $include_row_in_final_report = true;
    if ($filter_status === 'Alfa' && $row['total_alfa_calculated'] == 0) {
        $include_row_in_final_report = false;
    } else if ($filter_status === 'Libur' && $row['total_libur'] == 0) {
        $include_row_in_final_report = false;
    }

    if ($include_row_in_final_report) {
        $final_rekap_data_for_pdf[] = $row;
    }
}

$total_siswa_found_final = count($final_rekap_data_for_pdf);
$stmt_data->close();
$koneksi->close();

$html = '
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekap Absensi Siswa - ' . date('d F Y', strtotime($tanggal_mulai)) . ' s.d. ' . date('d F Y', strtotime($tanggal_akhir)) . '</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 9pt; margin: 15mm; } /* Reduced margin and font size */
        h1 { text-align: center; margin-bottom: 5px; font-size: 18pt; color: #34495e; } /* Reduced font size */
        h2 { text-align: center; margin-bottom: 20px; font-size: 13pt; color: #7f8c8d; } /* Reduced font size and margin */
        .info { margin-bottom: 15px; padding: 8px; background-color: #ecf0f1; border-radius: 6px; border: 1px solid #bdc3c7; } /* Reduced padding, margin, border-radius */
        .info p { margin: 0; line-height: 1.5; color: #2c3e50; font-size: 8.5pt;} /* Reduced line-height and font size */
        strong { color: #34495e; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; } /* Reduced margin-top */
        th, td { border: 1px solid #bdc3c7; padding: 8px 6px; text-align: left; vertical-align: middle; } /* Reduced padding */
        th { background-color: #3498db; color: #ffffff; font-weight: bold; text-transform: uppercase; font-size: 8pt; text-align: center; } /* Reduced font size */
        td { font-size: 8pt; color: #34495e; } /* Reduced font size */

        .badge-container {
            display: block; /* Changed to block to allow stacking if needed */
            line-height: 1.2; /* Reduced line-height */
            word-break: break-word;
        }
        .badge {
            display: inline-block;
            margin: 1px 2px 1px 0; /* Reduced margin */
            padding: 3px 6px; /* Reduced padding */
            font-size: 7.5pt; /* Reduced font size */
            font-weight: 600;
            color: #fff;
            border-radius: 15px; /* Slightly smaller border-radius */
            text-align: center;
            white-space: nowrap;
            background-color: #ccc;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1); /* Smaller shadow */
        }
        .badge.bg-success { background-color: #28a745 !important; }
        .badge.bg-warning { background-color: #ffc107 !important; color: #343a40 !important; }
        .badge.bg-info    { background-color: #17a2b8 !important; }
        .badge.bg-danger  { background-color: #dc3545 !important; }
        .badge.bg-secondary { background-color: #6c757d !important; }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        
        /* Adjusted column widths for Portrait A4 */
        .table-no { width: 5%; }
        .table-nama { width: 25%; }
        .table-kelas { width: 10%; }
        .table-jurusan { width: 20%; } /* Slightly wider for jurusan name */
        .table-tempat { width: 20%; } /* Slightly wider for place name */
        .table-rekap { width: 20%; } /* Keep rekap compact */

        .footer {
            text-align: center;
            font-size: 7.5pt; /* Reduced font size */
            color: #7f8c8d;
            margin-top: 25px; /* Reduced margin */
        }
    </style>
</head>
<body>
    <h1>LAPORAN REKAPITULASI ABSENSI SISWA</h1>
    <h2>Periode: ' . date('d F Y', strtotime($tanggal_mulai)) . ' s.d. ' . date('d F Y', strtotime($tanggal_akhir)) . '</h2>
    
    <div class="info">
        <p><strong>Filter Status:</strong> ' . (empty($filter_status) ? 'Semua Status' : htmlspecialchars($filter_status)) . '</p>
        <p><strong>Kata Kunci Pencarian:</strong> ' . (empty($keyword) ? 'Tidak Ada' : htmlspecialchars($keyword)) . '</p>
        <p><strong>Total Siswa Ditemukan:</strong> ' . $total_siswa_found_final . ' siswa</p>
        <p><strong>Periode Absensi yang Diperhitungkan (Untuk Menentukan Alfa):</strong> ' . date('d F Y', strtotime($tanggal_mulai)) . ' s.d. ' . date('d F Y', strtotime($effective_end_date_for_alfa_calc_obj->format('Y-m-d'))) . ' (' . $total_days_for_alfa_calc_up_to_today_or_end_date . ' Hari)</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th class="table-no">No</th>
                <th class="table-nama">Nama Siswa</th>
                <th class="table-kelas">Kelas</th>
                <th class="table-jurusan">Jurusan</th>
                <th class="table-tempat">Tempat PKL</th>
                <th class="table-rekap">Rekap Absensi</th>
            </tr>
        </thead>
        <tbody>';

if ($total_siswa_found_final > 0) {
    $no = 1;
    foreach ($final_rekap_data_for_pdf as $row) {
        $rekap_absen_html = '<div class="badge-container">';
        $rekap_absen_html .= '<span class="badge bg-success">Hadir: ' . $row['total_hadir'] . '</span>';
        $rekap_absen_html .= '<span class="badge bg-warning">Sakit: ' . $row['total_sakit'] . '</span>';
        $rekap_absen_html .= '<span class="badge bg-info">Izin: ' . $row['total_izin'] . '</span>';
        $rekap_absen_html .= '<span class="badge bg-secondary">Libur: ' . $row['total_libur'] . '</span>';
        $rekap_absen_html .= '<span class="badge bg-danger">Alfa: ' . $row['total_alfa_calculated'] . '</span>';
        $rekap_absen_html .= '</div>';

        $html .= '<tr>
                    <td class="text-center">' . $no++ . '</td>
                    <td>' . htmlspecialchars($row['nama_siswa']) . '</td>
                    <td class="text-center">' . htmlspecialchars($row['kelas']) . '</td>
                    <td>' . htmlspecialchars($row['nama_jurusan'] ?? '-') . '</td>
                    <td>' . htmlspecialchars($row['nama_tempat_pkl'] ?? '-') . '</td>
                    <td>' . $rekap_absen_html . '</td>
                </tr>';
    }
} else {
    $html .= '<tr><td colspan="6" class="text-center" style="color: #888; padding: 20px;">Tidak ada data absensi ditemukan untuk filter ini.</td></tr>';
}

$html .= '</tbody>
    </table>

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis pada ' . date('d F Y H:i:s') . ' WIB.</p>
        <p>&copy; 2025 E-Jurnal PKL. Semua Hak Dilindungi.</p>
    </div>
</body>
</html>';

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');
$options->set('chroot', realpath(__DIR__));

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'portrait'); // Set to portrait

$dompdf->render();

$filename = 'Rekap_Absensi_Siswa_' . date('Ymd', strtotime($tanggal_mulai)) . '_' . date('Ymd', strtotime($tanggal_akhir)) . '.pdf';
$dompdf->stream($filename, ["Attachment" => false]);
exit();