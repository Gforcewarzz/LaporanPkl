<?php

session_start();
date_default_timezone_set('Asia/Jakarta'); // Pastikan zona waktu konsisten

include 'partials/db.php'; // Pastikan file ini mengembalikan objek $koneksi yang valid dan terbuka

// Sertakan Dompdf Autoloader
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
$filter_status = $_GET['status'] ?? 'Semua';
$keyword = $_GET['keyword_pdf'] ?? '';
$kelas_filter_pdf = $_GET['kelas_pdf'] ?? '';

$siswa_id_from_form = $_GET['siswa_id_pdf'] ?? null;
$pembimbing_id_from_form = $_GET['pembimbing_id_pdf'] ?? null;

// --- INISIALISASI QUERY PARAMS DAN KONDISI ---
$where_clauses = [];
$query_params = [];
$query_types = "";
$report_title_suffix = "";
$siswa_detail_for_header = [
    'nama_peserta_didik' => '',
    'dunia_kerja_tempat_pkl' => '',
    'nama_guru_pembimbing' => '',
    'nama_instruktur_pkl' => ''
];

// --- LOGIKA FILTER UTAMA BERDASARKAN PERAN YANG LOGIN ---
$final_filter_siswa_id = null;
$final_filter_pembimbing_id = null;

if ($is_siswa) {
    $final_filter_siswa_id = $_SESSION['id_siswa'] ?? null;
    if ($final_filter_siswa_id) {
        $where_clauses[] = 'as_abs.siswa_id = ?';
        $query_params[] = $final_filter_siswa_id;
        $query_types .= 'i';
    } else {
        die("Siswa ID tidak ditemukan dalam sesi.");
    }
    $report_title_suffix = "Pribadi";
} elseif ($is_guru) {
    $final_filter_pembimbing_id = $_SESSION['id_guru_pendamping'] ?? null;

    if ($siswa_id_from_form !== null) {
        $final_filter_siswa_id = $siswa_id_from_form;
        $where_clauses[] = 'as_abs.siswa_id = ?';
        $query_params[] = $final_filter_siswa_id;
        $query_types .= 'i';
        $report_title_suffix = "Siswa Bimbingan";
    } elseif ($final_filter_pembimbing_id !== null) {
        $where_clauses[] = 's.pembimbing_id = ?';
        $query_params[] = $final_filter_pembimbing_id;
        $query_types .= 'i';
        $report_title_suffix = "Siswa Bimbingan";
    } else {
        die("ID Guru tidak ditemukan dalam sesi.");
    }
} elseif ($is_admin) {
    $param_siswa_id = $_GET['siswa_id'] ?? null;
    $param_pembimbing_id_from_url = $_GET['pembimbing_id'] ?? null;

    if ($param_siswa_id !== null) {
        $final_filter_siswa_id = $param_siswa_id;
        $where_clauses[] = 'as_abs.siswa_id = ?';
        $query_params[] = $final_filter_siswa_id;
        $query_types .= 'i';
        $report_title_suffix = "Siswa Spesifik";
    } elseif ($pembimbing_id_from_form !== null) {
        $final_filter_pembimbing_id = $pembimbing_id_from_form;
        $where_clauses[] = 's.pembimbing_id = ?';
        $query_params[] = $final_filter_pembimbing_id;
        $query_types .= 'i';
        $report_title_suffix = "Siswa Per Guru Pembimbing";
    } else {
        $report_title_suffix = "Seluruh Siswa";
    }
}

// --- Filter Rentang Tanggal Absensi (Diterapkan untuk SEMUA PERAN) ---
if (!strtotime($tanggal_mulai) || !strtotime($tanggal_akhir) || $tanggal_mulai > $tanggal_akhir) {
    $_SESSION['alert_message'] = 'Rentang tanggal tidak valid untuk laporan PDF.';
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_title'] = 'Gagal Cetak!';
    header('Location: master_data_absensi_siswa.php');
    exit();
}
$where_clauses[] = 'as_abs.tanggal_absen BETWEEN ? AND ?';
$query_params[] = $tanggal_mulai;
$query_params[] = $tanggal_akhir;
$query_types .= 'ss';

// --- Filter Status Absensi (Hanya jika laporan BUKAN rekap keseluruhan oleh admin/guru) ---
$is_detailed_report_for_single_student = $is_siswa || ($is_admin && $final_filter_siswa_id !== null) || ($is_guru && $final_filter_siswa_id !== null);

if ($is_detailed_report_for_single_student && !empty($filter_status) && $filter_status !== 'Semua') {
    $where_clauses[] = 'as_abs.status_absen = ?';
    $query_params[] = $filter_status;
    $query_types .= 's';
}

// --- Filter Keyword (Diterapkan jika ADMIN/GURU memfilter keyword) ---
if (!$is_siswa && !empty($keyword) && $is_detailed_report_for_single_student) {
    $like_keyword = "%" . $keyword . "%";
    $searchable_columns = ['s.nama_siswa', 's.kelas', 'j.nama_jurusan', 'tp.nama_tempat_pkl', 'as_abs.status_absen'];
    $search_conditions = [];
    foreach ($searchable_columns as $column) {
        $search_conditions[] = "$column LIKE ?";
        $query_params[] = $like_keyword;
        $query_types .= 's';
    }
    $where_clauses[] = "(" . implode(" OR ", $search_conditions) . ")";
}

// --- Filter Kelas (Hanya Diterapkan jika ADMIN dan filter kelas ada) ---
if ($is_admin && !empty($kelas_filter_pdf)) {
    $where_clauses[] = 's.kelas = ?';
    $query_params[] = $kelas_filter_pdf;
    $query_types .= 's';
}

// Bangun klausa WHERE akhir
$filter_sql = "";
if (!empty($where_clauses)) {
    $filter_sql = " WHERE " . implode(" AND ", $where_clauses);
}

$generate_recap_report = ($is_admin && $final_filter_siswa_id === null) || ($is_guru && $final_filter_siswa_id === null);

$query_sql = "
    SELECT
        s.id_siswa, s.nama_siswa, s.kelas, j.nama_jurusan, tp.nama_tempat_pkl,
        gp.nama_pembimbing AS nama_guru_pembimbing,
        tp.nama_instruktur AS nama_instruktur_pkl,
        as_abs.tanggal_absen, as_abs.jam_datang, as_abs.jam_pulang, as_abs.status_absen
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

$recap_data = [];
if ($generate_recap_report) {
    $all_relevant_students = [];
    $siswa_query_params = [];
    $siswa_query_types = "";
    $siswa_where_clauses = [];

    if ($final_filter_siswa_id !== null) {
        $siswa_where_clauses[] = 's.id_siswa = ?';
        $siswa_query_params[] = $final_filter_siswa_id;
        $siswa_query_types .= 'i';
    } elseif ($final_filter_pembimbing_id !== null) {
        $siswa_where_clauses[] = 's.pembimbing_id = ?';
        $siswa_query_params[] = $final_filter_pembimbing_id;
        $siswa_query_types .= 'i';
    }
    if (!empty($kelas_filter_pdf)) {
        $siswa_where_clauses[] = 's.kelas = ?';
        $siswa_query_params[] = $kelas_filter_pdf;
        $siswa_query_types .= 's';
    }
    if (!empty($keyword)) {
        $siswa_where_clauses[] = 's.nama_siswa LIKE ?';
        $siswa_query_params[] = "%" . $keyword . "%";
        $siswa_query_types .= 's';
    }

    $siswa_filter_sql = "";
    if (!empty($siswa_where_clauses)) {
        $siswa_filter_sql = " WHERE " . implode(" AND ", $siswa_where_clauses);
    }

    $siswa_detail_sql = "SELECT s.id_siswa, s.nama_siswa, s.kelas FROM siswa s $siswa_filter_sql ORDER BY s.kelas ASC, s.nama_siswa ASC";
    $stmt_siswa = $koneksi->prepare($siswa_detail_sql);

    if ($stmt_siswa === false) {
        die("Terjadi kesalahan sistem saat menyiapkan data siswa untuk rekap.");
    }
    if (!empty($siswa_query_params)) {
        $bind_args_siswa = [];
        $bind_args_siswa[] = $siswa_query_types;
        foreach ($siswa_query_params as &$param) {
            $bind_args_siswa[] = &$param;
        }
        call_user_func_array([$stmt_siswa, 'bind_param'], $bind_args_siswa);
    }
    $stmt_siswa->execute();
    $result_siswa = $stmt_siswa->get_result();
    while ($s_row = $result_siswa->fetch_assoc()) {
        $all_relevant_students[$s_row['id_siswa']] = ['nama_siswa' => $s_row['nama_siswa'], 'kelas' => $s_row['kelas'], 'Hadir' => 0, 'Sakit' => 0, 'Izin' => 0, 'Libur' => 0, 'Alfa' => 0];
    }
    $stmt_siswa->close();

    $start_ts = strtotime($tanggal_mulai);
    $end_ts = strtotime($tanggal_akhir);
    $work_days_count = 0;
    $all_dates_in_range = [];

    for ($i = $start_ts; $i <= $end_ts; $i = strtotime('+1 day', $i)) {
        $day_of_week = date('N', $i);
        if ($day_of_week >= 1 && $day_of_week <= 5) {
            $work_days_count++;
            $all_dates_in_range[] = date('Y-m-d', $i);
        }
    }

    $absensi_per_siswa_tanggal = [];
    foreach ($absensi_data as $record) {
        $absensi_per_siswa_tanggal[$record['id_siswa']][$record['tanggal_absen']] = $record['status_absen'];
    }

    foreach ($all_relevant_students as $siswa_id => $siswa_info) {
        $total_hadir = 0;
        $total_sakit = 0;
        $total_izin = 0;
        $total_libur = 0;
        $total_alfa = 0;

        foreach ($all_dates_in_range as $date) {
            if (isset($absensi_per_siswa_tanggal[$siswa_id][$date])) {
                $status = $absensi_per_siswa_tanggal[$siswa_id][$date];
                switch ($status) {
                    case 'Hadir':
                        $total_hadir++;
                        break;
                    case 'Sakit':
                        $total_sakit++;
                        break;
                    case 'Izin':
                        $total_izin++;
                        break;
                    case 'Libur':
                        $total_libur++;
                        break;
                }
            } else {
                $total_alfa++;
            }
        }
        $recap_data[] = [
            'id_siswa' => $siswa_id,
            'nama_siswa' => $siswa_info['nama_siswa'],
            'kelas' => $siswa_info['kelas'],
            'Hadir' => $total_hadir,
            'Sakit' => $total_sakit,
            'Izin' => $total_izin,
            'Libur' => $total_libur,
            'Alfa' => $total_alfa
        ];
    }
}

// --- PENGATURAN HEADER LAPORAN PDF ---
$nama_sekolah = "SMKN 1 GANTAR";
$tahun_pkl = date('Y', strtotime($tanggal_mulai)); // Mengambil tahun dari tanggal mulai

$siswa_detail_for_header = [
    'nama_peserta_didik' => '',
    'dunia_kerja_tempat_pkl' => '',
    'nama_guru_pembimbing' => '',
    'nama_instruktur_pkl' => ''
];

if ($generate_recap_report) {
    $siswa_detail_for_header['nama_peserta_didik'] = "Seluruh Siswa";
    $siswa_detail_for_header['dunia_kerja_tempat_pkl'] = "Beragam";
    $siswa_detail_for_header['nama_guru_pembimbing'] = "Beragam";
    $siswa_detail_for_header['nama_instruktur_pkl'] = "Beragam";

    if ($kelas_filter_pdf) {
        $siswa_detail_for_header['nama_peserta_didik'] .= " Kelas " . htmlspecialchars($kelas_filter_pdf);
    }
    if ($pembimbing_id_from_form) {
        $stmt_guru = $koneksi->prepare("SELECT nama_pembimbing FROM guru_pembimbing WHERE id_pembimbing = ?");
        if ($stmt_guru) {
            $stmt_guru->bind_param("i", $pembimbing_id_from_form);
            $stmt_guru->execute();
            $guru_res = $stmt_guru->get_result()->fetch_assoc();
            $siswa_detail_for_header['nama_guru_pembimbing'] = htmlspecialchars($guru_res['nama_pembimbing'] ?? 'N/A');
            $siswa_detail_for_header['nama_peserta_didik'] .= " (Pembimbing: " . $siswa_detail_for_header['nama_guru_pembimbing'] . ")";
            $stmt_guru->close();
        }
    }
} else {
    if (!empty($absensi_data)) {
        $first_row_data = $absensi_data[0];
        $siswa_detail_for_header['nama_peserta_didik'] = htmlspecialchars($first_row_data['nama_siswa']);
        $siswa_detail_for_header['dunia_kerja_tempat_pkl'] = htmlspecialchars($first_row_data['nama_tempat_pkl'] ?? '-');

        $siswa_detail_for_header['nama_guru_pembimbing'] = htmlspecialchars($first_row_data['nama_guru_pembimbing'] ?? '-');
        $siswa_detail_for_header['nama_instruktur_pkl'] = htmlspecialchars($first_row_data['nama_instruktur_pkl'] ?? '-');
    } else {
        if ($is_siswa) {
            $siswa_detail_for_header['nama_peserta_didik'] = $_SESSION['siswa_nama'] ?? 'Tidak Ditemukan';
            $siswa_id_session = $_SESSION['id_siswa'] ?? null;
            if ($siswa_id_session) {
                $stmt_tp = $koneksi->prepare("SELECT tp.nama_tempat_pkl, tp.nama_instruktur, gp.nama_pembimbing FROM siswa s LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl LEFT JOIN guru_pembimbing gp ON s.pembimbing_id = gp.id_pembimbing WHERE s.id_siswa = ?");
                if ($stmt_tp) {
                    $stmt_tp->bind_param("i", $siswa_id_session);
                    $stmt_tp->execute();
                    $tp_res = $stmt_tp->get_result()->fetch_assoc();
                    $siswa_detail_for_header['dunia_kerja_tempat_pkl'] = $tp_res['nama_tempat_pkl'] ?? '-';
                    $siswa_detail_for_header['nama_instruktur_pkl'] = $tp_res['nama_instruktur'] ?? '-';
                    $siswa_detail_for_header['nama_guru_pembimbing'] = $tp_res['nama_pembimbing'] ?? '-';
                    $stmt_tp->close();
                }
            }
        } elseif ($is_admin || $is_guru) {
            $siswa_detail_for_header['nama_peserta_didik'] = "Tidak Ada Siswa Dengan Absensi Ditemukan";
            $siswa_detail_for_header['dunia_kerja_tempat_pkl'] = "-";
            $siswa_detail_for_header['nama_guru_pembimbing'] = "-";
            $siswa_detail_for_header['nama_instruktur_pkl'] = "-";
        }
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
            font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10pt;
            margin: 15mm;
            color: #333;
            background-color: #ffffff;
            -webkit-print-color-adjust: exact;
        }
        .header-title {
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
            color: #444; /* Abu-abu gelap */
            text-transform: uppercase;
        }
        .school-info {
            text-align: center;
            font-size: 12pt;
            margin-top: 5px;
            margin-bottom: 20px;
            color: #555;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .report-period {
            text-align: center;
            font-size: 11pt;
            margin-bottom: 15px;
            color: #495057;
        }
        .student-info {
            font-size: 11pt;
            margin-bottom: 15px;
            line-height: 1.6;
            background-color: #f8f9fa;
            padding: 10px 15px;
            border: 1px solid #e9ecef;
            border-radius: 5px;
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
            width: 230px;
            font-weight: bold;
            color: #495057;
            text-align: left;
        }
        .student-info td:last-child {
            text-align: left;
        }

        table.attendance, table.recap-attendance {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid #c0c0c0; /* Border abu-abu */
            border-radius: 8px;
        }
        table.attendance th,
        table.attendance td,
        table.recap-attendance th,
        table.recap-attendance td {
            border: 1px solid #d0d0d0; /* Border abu-abu muda */
            padding: 12px 8px;
            text-align: center;
            font-size: 9.5pt;
            vertical-align: middle;
            line-height: 1.4;
        }
        table.attendance th,
        table.recap-attendance th {
            background-color: #a0a0a0; /* Header lebih gelap (abu-abu) */
            color: #fff; /* Teks putih untuk kontras */
            font-weight: bold;
            text-transform: uppercase;
        }
        table.attendance td.left-align,
        table.recap-attendance td.left-align {
            text-align: left;
            padding-left: 15px;
        }
        table.attendance tr:nth-child(even),
        table.recap-attendance tr:nth-child(even) {
            background-color: #f8f8f8; /* Warna latar baris genap */
        }
        table.attendance tbody tr:hover,
        table.recap-attendance tbody tr:hover {
            background-color: #e0e0e0; /* Hover abu-abu */
        }
        .page-break { page-break-before: always; }
        .table-no { width: 5%; }
        .tanggal-column { width: 15%; }
        .jam-column { width: 10%; }
        .para-column { width: 10%; }
        .keterangan-column { width: 20%; }
      
        
        .signature-section {
            width: 100%;
            margin-top: 40px;
            font-size: 10pt;
            line-height: 1.5;
            position: relative;
            text-align: right;
            page-break-inside: avoid; /* Perubahan di sini */
        }
        .signature-date-location {
            margin-bottom: 20px;
        }
        .signature-line-placeholder {
            margin-top: 60px;
            height: 15px;
            line-height: 15px;
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }
        .name-below-line {
            display: inline-block;
            margin-top: 5px;
        }
    </style>
</head>
<body>';

if ($generate_recap_report) {
    // LAPORAN REKAPITULASI (Admin/Guru melihat banyak siswa)
    $html .= '<div class="header-title">REKAPITULASI DAFTAR HADIR PRAKTIK KERJA LAPANGAN</div>';
    $html .= '<div class="school-info">' . htmlspecialchars($nama_sekolah) . ' TAHUN ' . htmlspecialchars($tahun_pkl) . '</div>';
    $html .= '<div class="report-period">Periode: ' . date('d F Y', strtotime($tanggal_mulai)) . ' s.d. ' . date('d F Y', strtotime($tanggal_akhir)) . '</div>';

    $html .= '<div class="student-info">';
    $html .= '<table>';
    $html .= '<tr>';
    $html .= '<td>Laporan Rekapitulasi Untuk</td>';
    $html .= '<td>: ' . htmlspecialchars($siswa_detail_for_header['nama_peserta_didik']) . '</td>';
    $html .= '</tr>';
    $html .= '<tr>';
    $html .= '<td>Dunia Kerja Tempat PKL</td>';
    $html .= '<td>: ' . htmlspecialchars($siswa_detail_for_header['dunia_kerja_tempat_pkl']) . '</td>';
    $html .= '</tr>';
    $html .= '</table>';
    $html .= '</div>';

    if (empty($recap_data) && empty($all_relevant_students)) {
        $html .= '<p style="text-align: center; padding: 20px; color: #777;">Tidak ada data rekap absensi ditemukan untuk kriteria ini.</p>';
    } else {
        $html .= '<table class="recap-attendance">
            <thead>
                <tr>
                    <th style="width:5%;">No</th>
                    <th style="width:30%;">Nama Siswa</th>
                    <th style="width:10%;">Kelas</th>
                    <th style="width:11%;">Hadir</th>
                    <th style="width:11%;">Sakit</th>
                    <th style="width:11%;">Izin</th>
                    <th style="width:11%;">Libur</th>
                    <th style="width:11%;">Alfa</th>
                </tr>
            </thead>
            <tbody>';

        $recap_row_no = 1;
        foreach ($recap_data as $row) {
            $html .= '<tr>
                    <td>' . $recap_row_no++ . '</td>
                    <td class="left-align">' . htmlspecialchars($row['nama_siswa']) . '</td>
                    <td>' . htmlspecialchars($row['kelas']) . '</td>
                    <td>' . htmlspecialchars($row['Hadir']) . '</td>
                    <td>' . htmlspecialchars($row['Sakit']) . '</td>
                    <td>' . htmlspecialchars($row['Izin']) . '</td>
                    <td>' . htmlspecialchars($row['Libur']) . '</td>
                    <td>' . htmlspecialchars($row['Alfa']) . '</td>
                </tr>';
        }
        $html .= '</tbody>
        </table>';
    }
} else {
    // LAPORAN DETIL (Siswa atau Admin/Guru melihat satu siswa spesifik)
    if (empty($absensi_data)) {
        $html .= '<div class="header-title">DAFTAR HADIR PRAKTIK KERJA LAPANGAN</div>';
        $html .= '<div class="school-info">' . htmlspecialchars($nama_sekolah) . ' TAHUN ' . htmlspecialchars($tahun_pkl) . '</div>';
        $html .= '<div class="report-period">Periode: ' . date('d F Y', strtotime($tanggal_mulai)) . ' s.d. ' . date('d F Y', strtotime($tanggal_akhir)) . '</div>';
        $html .= '<div class="student-info">
                        <table>
                            <tr><td>Nama Peserta Didik</td><td>: ' . htmlspecialchars($siswa_detail_for_header['nama_peserta_didik']) . '</td></tr>
                            <tr><td>Dunia Kerja Tempat PKL</td><td>: ' . htmlspecialchars($siswa_detail_for_header['dunia_kerja_tempat_pkl']) . '</td></tr>
                            <tr><td>Guru Pembimbing Sekolah</td><td>: ' . htmlspecialchars($siswa_detail_for_header['nama_guru_pembimbing']) . '</td></tr>
                            <tr><td>Pembimbing Dunia Kerja</td><td>: ' . htmlspecialchars($siswa_detail_for_header['nama_instruktur_pkl']) . '</td></tr>
                        </table>
                    </div>';
        $html .= '<p style="text-align: center; padding: 20px; color: #777;">Tidak ada data absensi ditemukan untuk kriteria ini.</p>';
    } else {
        $grouped_absensi_per_siswa = [];
        foreach ($absensi_data as $row) {
            $grouped_absensi_per_siswa[$row['id_siswa']][] = $row;
        }

        $siswa_counter = 0;
        foreach ($grouped_absensi_per_siswa as $current_siswa_id_grouped => $absensi_records_for_this_student) {
            if ($siswa_counter > 0) {
                $html .= '<div class="page-break"></div>';
            }
            $siswa_counter++;

            $current_siswa_data_header = $absensi_records_for_this_student[0];

            $html .= '<div class="header-title">DAFTAR HADIR PRAKTIK KERJA LAPANGAN</div>';
            $html .= '<div class="school-info">' . htmlspecialchars($nama_sekolah) . ' TAHUN ' . htmlspecialchars($tahun_pkl) . '</div>';
            $html .= '<div class="report-period">Periode: ' . date('d F Y', strtotime($tanggal_mulai)) . ' s.d. ' . date('d F Y', strtotime($tanggal_akhir)) . '</div>';

            $html .= '<div class="student-info">
                                <table>
                                    <tr><td>Nama Peserta Didik</td><td>: ' . htmlspecialchars($current_siswa_data_header['nama_siswa']) . '</td></tr>
                                    <tr><td>Dunia Kerja Tempat PKL</td><td>: ' . htmlspecialchars($current_siswa_data_header['nama_tempat_pkl'] ?? '-') . '</td></tr>
                                    <tr><td>Guru Pembimbing Sekolah</td><td>: ' . htmlspecialchars($current_siswa_data_header['nama_guru_pembimbing'] ?? '-') . '</td></tr>
                                    <tr><td>Pembimbing Dunia Kerja</td><td>: ' . htmlspecialchars($current_siswa_data_header['nama_instruktur_pkl'] ?? '-') . '</td></tr>
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
            foreach ($absensi_records_for_this_student as $record) {

                $tanggal_display_obj = new DateTime($record['tanggal_absen']);
                $nama_hari_inggris = $tanggal_display_obj->format('l');
                $nama_hari_indonesia = match ($nama_hari_inggris) {
                    'Sunday' => 'Minggu',
                    'Monday' => 'Senin',
                    'Tuesday' => 'Selasa',
                    'Wednesday' => 'Rabu',
                    'Thursday' => 'Kamis',
                    'Friday' => 'Jumat',
                    'Saturday' => 'Sabtu',
                    default => ''
                };
                $tanggal_display = $nama_hari_indonesia . ', ' . date('d F Y', strtotime($record['tanggal_absen']));

                $jam_datang_display = !empty($record['jam_datang']) ? date('H:i', strtotime($record['jam_datang'])) : '-';
                $jam_pulang_display = !empty($record['jam_pulang']) ? date('H:i', strtotime($record['jam_pulang'])) : '-';

                $html .= '<tr>
                                        <td>' . $record_no++ . '</td>
                                        <td>' . $tanggal_display . '</td>
                                        <td>' . $jam_datang_display . '</td>
                                        <td>' . $jam_pulang_display . '</td>
                                        <td></td>
                                        <td>' . htmlspecialchars($record['status_absen']) . '</td>
                                    </tr>';
            }
            $html .= '</tbody>
                            </table>';

            // Blok Tanda Tangan hanya untuk Pembimbing Dunia Kerja
            $html .= '
            <div class="signature-section">
                <p>...................., ......................... ' . htmlspecialchars($tahun_pkl) . '</p> <p>Pembimbing Dunia Kerja</p>
                <div style="height: 60px;"></div>
                <p>( ................................... )</p>
            </div>';
        }
    }
}

$html .= '
</body>
</html>';

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Segoe UI');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = "Daftar_Hadir_PKL_" . date('Ymd_His');
if (!empty($siswa_detail_for_header['nama_peserta_didik']) && $siswa_detail_for_header['nama_peserta_didik'] !== "Seluruh Siswa" && strpos($siswa_detail_for_header['nama_peserta_didik'], 'Beragam') === false) {
    $filename .= "_" . str_replace(' ', '_', $siswa_detail_for_header['nama_peserta_didik']);
} elseif (!empty($report_title_suffix)) {
    $filename .= "_" . str_replace(' ', '_', $report_title_suffix);
}
$filename .= ".pdf";

$dompdf->stream($filename, ["Attachment" => false]);
exit();
