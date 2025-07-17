<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

include 'partials/db.php';

require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// --- LOGIKA KEAMANAN HALAMAN ---
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';

if (!$is_admin && !$is_guru && !$is_siswa) {
    header('Location: ../login.php');
    exit();
}

// --- INISIALISASI FILTER DARI URL ---
$tanggal_mulai = $_GET['tanggal_mulai'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['tanggal_akhir'] ?? date('Y-m-t');
$filter_status = $_GET['status'] ?? 'Hadir';
$keyword = $_GET['keyword'] ?? '';
$pembimbing_id_filter_url = $_GET['pembimbing_id'] ?? null;
$siswa_id_filter_url = $_GET['siswa_id'] ?? null;

// --- INISIALISASI QUERY PARAMS DAN KONDISI ---
$where_clauses = [];
$query_params = [];
$query_types = "";
$report_title_suffix = "";
$header_siswa_info = [];

$target_siswa_id = null;
$target_pembimbing_id = null;

// --- LOGIKA FILTER BERDASARKAN PERAN YANG LOGIN ---
if ($is_siswa) {
    $target_siswa_id = $_SESSION['id_siswa'] ?? null;
    if ($target_siswa_id) {
        $where_clauses[] = 'as_abs.siswa_id = ?';
        $query_params[] = $target_siswa_id;
        $query_types .= 'i';
    } else {
        die("Siswa ID tidak ditemukan dalam sesi.");
    }
    $report_title_suffix = "Pribadi";
} elseif ($is_guru) {
    $loggedInGuruId = $_SESSION['id_guru_pendamping'] ?? null;

    if ($siswa_id_filter_url !== null) {
        $target_siswa_id = $siswa_id_filter_url;
        $where_clauses[] = 'as_abs.siswa_id = ?';
        $query_params[] = $target_siswa_id;
        $query_types .= 'i';
        $report_title_suffix = "Siswa Bimbingan";
    } elseif ($loggedInGuruId !== null) {
        $target_pembimbing_id = $loggedInGuruId;
        $where_clauses[] = 's.pembimbing_id = ?';
        $query_params[] = $target_pembimbing_id;
        $query_types .= 'i';
        $report_title_suffix = "Siswa Bimbingan";
    } else {
        die("ID Guru tidak ditemukan dalam sesi.");
    }
} elseif ($is_admin) {
    $param_siswa_id = $_GET['siswa_id'] ?? null;
    $param_pembimbing_id_from_url = $_GET['pembimbing_id'] ?? null;

    if ($param_siswa_id !== null) {
        $target_siswa_id = $param_siswa_id;
        $where_clauses[] = 'as_abs.siswa_id = ?';
        $query_params[] = $target_siswa_id;
        $query_types .= 'i';
        $report_title_suffix = "Siswa Spesifik";
    } elseif ($param_pembimbing_id_from_url !== null) {
        $target_pembimbing_id = $param_pembimbing_id_from_url;
        $where_clauses[] = 's.pembimbing_id = ?';
        $query_params[] = $target_pembimbing_id;
        $query_types .= 'i';
        $report_title_suffix = "Siswa Per Guru Pembimbing";
    } else {
        $report_title_suffix = "Seluruh Siswa";
    }
}

// --- Filter Rentang Tanggal Absensi ---
if (!strtotime($tanggal_mulai) || !strtotime($tanggal_akhir) || $tanggal_mulai > $tanggal_akhir) {
    $_SESSION['alert_message'] = 'Rentang tanggal tidak valid untuk laporan PDF.';
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_title'] = 'Gagal Cetak!';
    if ($koneksi) {
        $koneksi->close();
    }
    header('Location: master_data_absensi_siswa.php');
    exit();
}
$where_clauses[] = 'as_abs.tanggal_absen BETWEEN ? AND ?';
$query_params[] = $tanggal_mulai;
$query_params[] = $tanggal_akhir;
$query_types .= 'ss';

// --- Filter Status Absensi ---
if (!empty($filter_status) && $filter_status !== 'Semua') {
    $where_clauses[] = 'as_abs.status_absen = ?';
    $query_params[] = $filter_status;
    $query_types .= 's';
} else {
    $where_clauses[] = 'as_abs.status_absen = "Hadir"';
}

// Filter Keyword (jika ada)
if (!empty($keyword)) {
    $like_keyword = "%" . $keyword . "%";
    $searchable_columns = [
        's.nama_siswa',
        's.kelas',
        'j.nama_jurusan',
        'tp.nama_tempat_pkl',
        'as_abs.status_absen'
    ];
    $search_conditions = [];
    foreach ($searchable_columns as $column) {
        $search_conditions[] = "$column LIKE ?";
        $query_params[] = $like_keyword;
        $query_types .= 's';
    }
    $where_clauses[] = "(" . implode(" OR ", $search_conditions) . ")";
}

// Bangun klausa WHERE akhir
$filter_sql = "";
if (!empty($where_clauses)) {
    $filter_sql = " WHERE " . implode(" AND ", $where_clauses);
}

// --- QUERY UTAMA UNTUK MENGAMBIL DATA ABSENSI DETIL ---
$query_sql = "
    SELECT
        s.id_siswa,
        s.nama_siswa,
        s.kelas,
        j.nama_jurusan,
        tp.nama_tempat_pkl,
        as_abs.tanggal_absen,
        as_abs.jam_datang,
        as_abs.jam_pulang,
        as_abs.status_absen
    FROM
        absensi_siswa as_abs
    JOIN siswa s ON as_abs.siswa_id = s.id_siswa
    LEFT JOIN jurusan j ON s.jurusan_id = j.id_jurusan
    LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl
    LEFT JOIN guru_pembimbing gp ON s.pembimbing_id = gp.id_pembimbing
    $filter_sql
    ORDER BY s.kelas ASC, s.nama_siswa ASC, as_abs.tanggal_absen ASC";

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
$absensi_data = [];
while ($row = $result->fetch_assoc()) {
    $absensi_data[] = $row;
}
$stmt->close();
$koneksi->close();

// --- PENGELOMPOKAN DATA UNTUK LAPORAN (Per Siswa) ---
$grouped_absensi = [];
$nama_sekolah = "SMKN 1 GANTAR";
$tahun_pkl = "2025";

$siswa_detail_for_header = [
    'nama_peserta_didik' => '',
    'dunia_kerja_tempat_pkl' => ''
];

if (!empty($absensi_data)) {
    if ($is_siswa || ($is_guru && $target_siswa_id) || ($is_admin && $target_siswa_id)) {
        $first_row_data = $absensi_data[0];
        $siswa_detail_for_header['nama_peserta_didik'] = htmlspecialchars($first_row_data['nama_siswa']);
        $siswa_detail_for_header['dunia_kerja_tempat_pkl'] = htmlspecialchars($first_row_data['nama_tempat_pkl'] ?? '-');
    } elseif ($is_guru && !$target_siswa_id) {
        $siswa_detail_for_header['nama_peserta_didik'] = "Siswa Bimbingan Anda";
        $siswa_detail_for_header['dunia_kerja_tempat_pkl'] = "Beragam (Siswa Bimbingan)";
    } elseif ($is_admin && !$siswa_id_filter_url && !$pembimbing_id_filter_url) {
        $siswa_detail_for_header['nama_peserta_didik'] = "Seluruh Siswa";
        $siswa_detail_for_header['dunia_kerja_tempat_pkl'] = "Beragam";
    } elseif ($is_admin && $pembimbing_id_filter_url) {
        $siswa_detail_for_header['nama_peserta_didik'] = "Siswa Bimbingan Guru (ID: " . htmlspecialchars($pembimbing_id_filter_url) . ")";
        $siswa_detail_for_header['dunia_kerja_tempat_pkl'] = "Beragam";
    }

    foreach ($absensi_data as $row) {
        $grouped_absensi[$row['id_siswa']][] = $row;
    }
} else {
    if ($is_siswa) {
        $siswa_detail_for_header['nama_peserta_didik'] = $_SESSION['siswa_nama'] ?? 'Tidak Ditemukan';
        $koneksi_temp = new mysqli($host, $username, $password, $database);
        if ($koneksi_temp && $koneksi_temp->connect_errno === 0) {
            $stmt_tp = $koneksi_temp->prepare("SELECT tp.nama_tempat_pkl FROM siswa s LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl WHERE s.id_siswa = ?");
            if ($stmt_tp) {
                $stmt_tp->bind_param("i", $_SESSION['id_siswa']);
                $stmt_tp->execute();
                $tp_res = $stmt_tp->get_result()->fetch_assoc();
                $siswa_detail_for_header['dunia_kerja_tempat_pkl'] = $tp_res['nama_tempat_pkl'] ?? '-';
                $stmt_tp->close();
            }
            $koneksi_temp->close();
        }
    } elseif ($is_admin && $siswa_id_filter_url) {
        $siswa_detail_for_header['nama_peserta_didik'] = "Siswa (ID: " . htmlspecialchars($siswa_id_filter_url) . ") - Tidak Ada Absensi";
        $siswa_detail_for_header['dunia_kerja_tempat_pkl'] = "-";
    }
}


$html = '
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>DAFTAR HADIR PRAKTIK KERJA LAPANGAN</title>
    <style>
        body { 
            font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; /* Modern sans-serif font */
            font-size: 10pt; /* Default font size */
            margin: 15mm; /* Consistent margins around the page */
            color: #333; /* Darker text for readability */
            background-color: #ffffff; /* White background */
            -webkit-print-color-adjust: exact; /* Ensure background colors are printed */
        }
        .header-title { 
            text-align: center; 
            font-size: 18pt; 
            font-weight: bold; 
            margin-bottom: 5px; 
            color: #0056b3; /* Darker blue for title */
            text-transform: uppercase;
        }
        .school-info { 
            text-align: center; 
            font-size: 12pt; 
            margin-top: 5px; 
            margin-bottom: 20px; 
            color: #555; 
            border-bottom: 1px solid #ddd; /* Subtle line below school info */
            padding-bottom: 10px;
        }
        .student-info { 
            font-size: 11pt; 
            margin-bottom: 15px; 
            line-height: 1.6; 
            background-color: #f8f9fa; /* Light background for student info box */
            padding: 10px 15px;
            border-radius: 5px;
            border: 1px solid #e9ecef; /* Light border */
        }
        .student-info table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        .student-info td { 
            border: none; 
            padding: 3px 0; 
        }
        .student-info td:first-child { 
            width: 200px; 
            font-weight: bold; 
            color: #495057; 
        }

        table.attendance { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08); /* More pronounced shadow */
            border-radius: 8px; 
            overflow: hidden; 
            border: 1px solid #e0e0e0; /* Overall table border */
        }
        table.attendance th, 
        table.attendance td { 
            border: 1px solid #e0e0e0; /* Lighter borders for cells */
            padding: 12px 8px; 
            text-align: center; 
            font-size: 9.5pt; /* Slightly larger for readability */
            vertical-align: middle; 
            line-height: 1.4; /* Better line spacing */
        }
        table.attendance th { 
            background-color: #007bff; /* Primary blue header */
            color: #fff; 
            font-weight: bold; 
            text-transform: uppercase; 
        }
        table.attendance td.left-align { 
            text-align: left; 
            padding-left: 15px; 
        }
        table.attendance tr:nth-child(even) { 
            background-color: #f8f9fa; /* Lighter zebra striping */
        }
        table.attendance tbody tr:hover { 
            background-color: #e9ecef; 
        }

        .page-break { page-break-before: always; }
        
        /* Optimized Column Widths (Adjust as needed based on content) */
        /* Total width should be 100% */
        .table-no { width: 5%; }
        .tanggal-column { width: 15%; }
        .jam-column { width: 10%; } /* Both Jam Datang and Jam Pulang */
        .para-column { width: 10%; }
        .keterangan-column { width: 20%; } 
        /* Pastikan total width pas 100% atau sedikit lebih jika ada padding/border */
        /* Contoh: 5% + 15% + 10% + 10% + 10% + 20% = 70%. Sisanya terbagi rata atau disesuaikan */


        .footer-timestamp { 
            text-align: right; 
            font-size: 8pt; 
            color: #6c757d; 
            margin-top: 25px; 
        }

        /* Styling untuk badge status di kolom Keterangan */
        .status-badge {
            display: inline-block; 
            padding: 5px 10px; 
            font-size: 9pt; 
            font-weight: 500; 
            color: #fff; 
            border-radius: 5px; 
            white-space: nowrap; 
            min-width: 60px; /* Minimal width for better consistency */
            text-align: center;
        }
        /* Warna berdasarkan status */
        .status-Hadir { background-color: #28a745; } /* Green */
        .status-Sakit { background-color: #ffc107; color: #212529; } /* Orange with dark text */
        .status-Izin { background-color: #17a2b8; } /* Cyan */
        .status-Alfa { background-color: #dc3545; } /* Red */
        .status-Libur { background-color: #6c757d; } /* Gray */

    </style>
</head>
<body>';

$siswa_counter = 0;

if (empty($grouped_absensi)) {
    // Tampilan jika TIDAK ADA DATA sama sekali
    $html .= '<div class="header-title">DAFTAR HADIR PRAKTIK KERJA LAPANGAN</div>';
    $html .= '<div class="school-info">' . htmlspecialchars($nama_sekolah) . ' TAHUN ' . htmlspecialchars($tahun_pkl) . '</div>';
    $html .= '<div class="student-info">
                <table>
                    <tr><td>Nama Peserta Didik</td><td>: ' . htmlspecialchars($siswa_detail_for_header['nama_peserta_didik']) . '</td></tr>
                    <tr><td>Dunia Kerja Tempat PKL</td><td>: ' . htmlspecialchars($siswa_detail_for_header['dunia_kerja_tempat_pkl']) . '</td></tr>
                </table>
              </div>';
    $html .= '<p style="text-align: center; padding: 20px; color: #777;">Tidak ada data absensi ditemukan untuk kriteria ini.</p>';
} else {
    // Loop untuk setiap siswa
    foreach ($grouped_absensi as $current_siswa_id => $absensi_records) {
        if ($siswa_counter > 0) {
            $html .= '<div class="page-break"></div>'; // Halaman baru untuk siswa berikutnya
        }
        $siswa_counter++;

        $current_siswa_data = $absensi_records[0]; // Ambil data siswa dari record absensi pertama

        $html .= '<div class="header-title">DAFTAR HADIR PRAKTIK KERJA LAPANGAN</div>';
        $html .= '<div class="school-info">' . htmlspecialchars($nama_sekolah) . ' TAHUN ' . htmlspecialchars($tahun_pkl) . '</div>';

        $html .= '<div class="student-info">
                    <table>
                        <tr><td>Nama Peserta Didik</td><td>: ' . htmlspecialchars($current_siswa_data['nama_siswa']) . '</td></tr>
                        <tr><td>Dunia Kerja Tempat PKL</td><td>: ' . htmlspecialchars($current_siswa_data['nama_tempat_pkl'] ?? '-') . '</td></tr>
                    </table>
                  </div>';

        $html .= '<table class="attendance">
            <thead>
                <tr>
                    <th class="table-no">No</th>
                    <th class="tanggal-column">Tanggal</th>
                    <th class="jam-column">Jam Datang</th>
                    <th class="jam-column">Jam Pulang</th>
                    <th class="para-column">Paraf</th>
                    <th class="keterangan-column">Keterangan</th>
                </tr>
            </thead>
            <tbody>';

        $record_no = 1;
        foreach ($absensi_records as $record) {
            $tanggal_display = date('d F Y', strtotime($record['tanggal_absen']));
            $jam_datang_display = !empty($record['jam_datang']) ? date('H:i', strtotime($record['jam_datang'])) : '-';
            $jam_pulang_display = !empty($record['jam_pulang']) ? date('H:i', strtotime($record['jam_pulang'])) : '-';

            // Kolom Keterangan diambil dari status_absen dan ditambahi badge styling
            $status_class = 'status-' . htmlspecialchars($record['status_absen']);
            $keterangan_kolom_html = '<span class="status-badge ' . $status_class . '">' . htmlspecialchars($record['status_absen']) . '</span>';

            $html .= '<tr>
                        <td>' . $record_no++ . '</td>
                        <td>' . $tanggal_display . '</td>
                        <td>' . $jam_datang_display . '</td>
                        <td>' . $jam_pulang_display . '</td>
                        <td></td> <td class="left-align">' . $keterangan_kolom_html . '</td>
                    </tr>';
        }
        $html .= '</tbody>
        </table>';
    }
}

$html .= '
    <div class="footer-timestamp">
        Laporan ini dibuat secara otomatis pada ' . date('d F Y H:i:s') . ' WIB.
    </div>
</body>
</html>';

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Segoe UI'); // Menggunakan Segoe UI, jika tidak ada akan fallback ke sans-serif

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = "Daftar_Hadir_PKL_" . date('Ymd_His');
if ($target_siswa_id && !empty($siswa_detail_for_header['nama_peserta_didik'])) {
    $filename .= "_" . str_replace(' ', '_', $siswa_detail_for_header['nama_peserta_didik']);
} elseif (!empty($report_title_suffix)) {
    $filename .= "_" . str_replace(' ', '_', $report_title_suffix);
}
$filename .= ".pdf";

$dompdf->stream($filename, ["Attachment" => false]);
exit();
