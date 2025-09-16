<?php

session_start();
date_default_timezone_set('Asia/Jakarta'); // Pastikan zona waktu konsisten

// Aktifkan pelaporan error untuk debugging, nonaktifkan di production
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

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

// --- LOGIKA FILTER UTAMA BERDASARKAN PERAN ---
$final_filter_siswa_id = null;
$final_filter_pembimbing_id = null;

if ($is_siswa) {
    $final_filter_siswa_id = $_SESSION['id_siswa'] ?? null;
    if ($final_filter_siswa_id) {
        $where_clauses[] = 'as_abs.siswa_id = ?';
        $query_params[] = &$final_filter_siswa_id;
        $query_types .= 'i';
    } else {
        die("Siswa ID tidak ditemukan dalam sesi.");
    }
} elseif ($is_guru) {
    $final_filter_pembimbing_id = $_SESSION['id_guru_pendamping'] ?? null;
    if ($siswa_id_from_form !== null) {
        $final_filter_siswa_id = $siswa_id_from_form;
        $where_clauses[] = 'as_abs.siswa_id = ?';
        $query_params[] = &$final_filter_siswa_id;
        $query_types .= 'i';
    } elseif ($final_filter_pembimbing_id !== null) {
        $where_clauses[] = 's.pembimbing_id = ?';
        $query_params[] = &$final_filter_pembimbing_id;
        $query_types .= 'i';
    } else {
        die("ID Guru tidak ditemukan dalam sesi.");
    }
} elseif ($is_admin) {
    $param_siswa_id = $_GET['siswa_id'] ?? null;
    if ($param_siswa_id !== null) {
        $final_filter_siswa_id = $param_siswa_id;
        $where_clauses[] = 'as_abs.siswa_id = ?';
        $query_params[] = &$final_filter_siswa_id;
        $query_types .= 'i';
    } elseif ($pembimbing_id_from_form !== null) {
        $final_filter_pembimbing_id = $pembimbing_id_from_form;
        $where_clauses[] = 's.pembimbing_id = ?';
        $query_params[] = &$final_filter_pembimbing_id;
        $query_types .= 'i';
    }
}

// Filter Rentang Tanggal
if (strtotime($tanggal_mulai) && strtotime($tanggal_akhir) && $tanggal_mulai <= $tanggal_akhir) {
    $where_clauses[] = 'as_abs.tanggal_absen BETWEEN ? AND ?';
    $query_params[] = &$tanggal_mulai;
    $query_params[] = &$tanggal_akhir;
    $query_types .= 'ss';
}

// Filter Status Absensi untuk laporan detil
$is_detailed_report = $is_siswa || ($final_filter_siswa_id !== null);
if ($is_detailed_report && !empty($filter_status) && $filter_status !== 'Semua') {
    $where_clauses[] = 'as_abs.status_absen = ?';
    $query_params[] = &$filter_status;
    $query_types .= 's';
}

// Filter Kelas (hanya Admin)
if ($is_admin && !empty($kelas_filter_pdf)) {
    $where_clauses[] = 's.kelas = ?';
    $query_params[] = &$kelas_filter_pdf;
    $query_types .= 's';
}

$filter_sql = !empty($where_clauses) ? " WHERE " . implode(" AND ", $where_clauses) : "";
$generate_recap_report = !$is_detailed_report;

// --- AMBIL DATA ABSENSI ---
$query_sql = "
    SELECT
        s.id_siswa, s.nama_siswa, s.kelas, j.nama_jurusan, tp.nama_tempat_pkl,
        gp.nama_pembimbing AS nama_guru_pembimbing,
        (SELECT MIN(tanggal_absen) FROM absensi_siswa WHERE siswa_id = s.id_siswa) AS tanggal_mulai_pkl,
        as_abs.tanggal_absen, as_abs.status_absen
    FROM absensi_siswa as_abs
    JOIN siswa s ON as_abs.siswa_id = s.id_siswa
    LEFT JOIN jurusan j ON s.jurusan_id = j.id_jurusan
    LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl
    LEFT JOIN guru_pembimbing gp ON s.pembimbing_id = gp.id_pembimbing
    $filter_sql
    ORDER BY s.kelas ASC, s.nama_siswa ASC, as_abs.tanggal_absen ASC";

$stmt = $koneksi->prepare($query_sql);
if ($stmt === false) {
    die("Gagal menyiapkan query absensi: " . $koneksi->error);
}
if (!empty($query_params)) {
    // [PERBAIKAN] Menggunakan call_user_func_array untuk stabilitas
    $bind_args = [];
    $bind_args[] = $query_types;
    foreach ($query_params as $key => $value) {
        $bind_args[] = &$query_params[$key];
    }
    call_user_func_array(array($stmt, 'bind_param'), $bind_args);
}
$stmt->execute();
$result = $stmt->get_result();
$absensi_data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();


// --- LOGIKA UNTUK LAPORAN REKAPITULASI ---
$recap_data = [];
$pembimbing_name_for_recap = "Semua Guru";

if ($generate_recap_report) {
    // Ambil semua siswa yang relevan dengan tanggal mulai PKL mereka
    $siswa_where_clauses = [];
    $siswa_query_params = [];
    $siswa_query_types = "";
    if ($final_filter_pembimbing_id !== null) {
        $siswa_where_clauses[] = 's.pembimbing_id = ?';
        $siswa_query_params[] = &$final_filter_pembimbing_id;
        $siswa_query_types .= 'i';
    }
    if (!empty($kelas_filter_pdf)) {
        $siswa_where_clauses[] = 's.kelas = ?';
        $siswa_query_params[] = &$kelas_filter_pdf;
        $siswa_query_types .= 's';
    }

    $siswa_filter_sql = !empty($siswa_where_clauses) ? " WHERE " . implode(" AND ", $siswa_where_clauses) : "";
    $siswa_detail_sql = "
        SELECT s.id_siswa, s.nama_siswa, s.kelas, gp.nama_pembimbing,
               (SELECT MIN(tanggal_absen) FROM absensi_siswa WHERE siswa_id = s.id_siswa) AS tanggal_mulai_pkl
        FROM siswa s
        LEFT JOIN guru_pembimbing gp ON s.pembimbing_id = gp.id_pembimbing
        $siswa_filter_sql ORDER BY s.kelas ASC, s.nama_siswa ASC";

    $stmt_siswa = $koneksi->prepare($siswa_detail_sql);
    if ($stmt_siswa === false) {
        die("Gagal menyiapkan query siswa untuk rekap: " . $koneksi->error);
    }
    if (!empty($siswa_query_params)) {
        // [PERBAIKAN] Menggunakan call_user_func_array
        $bind_args_siswa = [];
        $bind_args_siswa[] = $siswa_query_types;
        foreach ($siswa_query_params as $key => $value) {
            $bind_args_siswa[] = &$siswa_query_params[$key];
        }
        call_user_func_array(array($stmt_siswa, 'bind_param'), $bind_args_siswa);
    }

    $stmt_siswa->execute();
    $result_siswa = $stmt_siswa->get_result();
    $all_relevant_students = $result_siswa->fetch_all(MYSQLI_ASSOC);
    $stmt_siswa->close();

    // Ambil nama pembimbing jika hanya satu
    if ($final_filter_pembimbing_id) {
        $unique_pembimbings = array_unique(array_column($all_relevant_students, 'nama_pembimbing'));
        if (count($unique_pembimbings) === 1) {
            $pembimbing_name_for_recap = reset($unique_pembimbings);
        }
    }

    $absensi_per_siswa_tanggal = [];
    foreach ($absensi_data as $record) {
        $absensi_per_siswa_tanggal[$record['id_siswa']][$record['tanggal_absen']] = $record['status_absen'];
    }

    foreach ($all_relevant_students as $siswa) {
        $rekap = ['Hadir' => 0, 'Sakit' => 0, 'Izin' => 0, 'Libur' => 0, 'Alfa' => 0];
        $start_ts = strtotime($tanggal_mulai);
        $end_ts = strtotime($tanggal_akhir);
        $siswa_mulai_pkl_ts = !empty($siswa['tanggal_mulai_pkl']) ? strtotime($siswa['tanggal_mulai_pkl']) : $start_ts;

        for ($i = $start_ts; $i <= $end_ts; $i = strtotime('+1 day', $i)) {
            $day_of_week = date('N', $i);
            $current_date_str = date('Y-m-d', $i);

            if ($i < $siswa_mulai_pkl_ts) {
                continue;
            }

            if ($day_of_week >= 1 && $day_of_week <= 5) {
                if (isset($absensi_per_siswa_tanggal[$siswa['id_siswa']][$current_date_str])) {
                    $status = $absensi_per_siswa_tanggal[$siswa['id_siswa']][$current_date_str];
                    if (array_key_exists($status, $rekap)) {
                        $rekap[$status]++;
                    }
                } else {
                    $rekap['Alfa']++;
                }
            }
        }
        $recap_data[] = array_merge($siswa, $rekap);
    }
}

$koneksi->close();

// --- PENGATURAN HTML & PDF ---
$nama_sekolah = "SMKN 1 GANTAR";
$tahun_pkl = date('Y'); // Mengambil tahun saat ini
$detail_pembimbing = "";
if (!$generate_recap_report && !empty($absensi_data)) {
    $detail_pembimbing = $absensi_data[0]['nama_guru_pembimbing'] ?? '';
} elseif ($generate_recap_report) {
    $detail_pembimbing = $pembimbing_name_for_recap;
}

$html = '
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Daftar Hadir PKL</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; margin: 15mm; color: #333; }
        .header-title { text-align: center; font-size: 14pt; font-weight: bold; margin-bottom: 5px; text-transform: uppercase; }
        .school-info { text-align: center; font-size: 12pt; margin-bottom: 20px; border-bottom: 1px solid #333; padding-bottom: 10px; }
        .report-period { text-align: center; font-size: 11pt; margin-bottom: 20px; }
        .student-info { font-size: 10pt; margin-bottom: 15px; line-height: 1.6; }
        .student-info td { padding: 2px 0; }
        .student-info td:first-child { width: 150px; font-weight: bold; }
        table.attendance { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: center; font-size: 9.5pt; }
        th { background-color: #e0e0e0; font-weight: bold; }
        td.left-align { text-align: left; }
        .signature-section { margin: 40px 0 0 40px; font-size: 10pt; page-break-inside: avoid; width: 100%; }
    </style>
</head>
<body>
    <div class="header-title">REKAPITULASI DAFTAR HADIR PESERTA PKL</div>
    <div class="school-info">' . htmlspecialchars($nama_sekolah) . ' TAHUN ' . htmlspecialchars($tahun_pkl) . '</div>
    <div class="report-period">Periode: ' . date('d F Y', strtotime($tanggal_mulai)) . ' s.d. ' . date('d F Y', strtotime($tanggal_akhir)) . '</div>';

if ($generate_recap_report) {
    if (empty($recap_data)) {
        $html .= '<p style="text-align: center;">Tidak ada data rekapitulasi untuk ditampilkan.</p>';
    } else {
        $html .= '<table class="attendance">
            <thead>
                <tr>
                    <th style="width:5%;">No</th>
                    <th style="width:30%;" class="left-align">Nama Siswa</th>
                    <th style="width:15%;">Kelas</th>
                    <th>Hadir</th>
                    <th>Sakit</th>
                    <th>Izin</th>
                    <th>Libur</th>
                    <th>Alfa</th>
                </tr>
            </thead>
            <tbody>';
        $no = 1;
        foreach ($recap_data as $row) {
            $html .= '<tr>
                <td>' . $no++ . '</td>
                <td class="left-align">' . htmlspecialchars($row['nama_siswa']) . '</td>
                <td>' . htmlspecialchars($row['kelas']) . '</td>
                <td>' . $row['Hadir'] . '</td>
                <td>' . $row['Sakit'] . '</td>
                <td>' . $row['Izin'] . '</td>
                <td>' . $row['Libur'] . '</td>
                <td>' . $row['Alfa'] . '</td>
            </tr>';
        }
        $html .= '</tbody></table>';
    }
} else {
    if (empty($absensi_data)) {
        $html .= '<p style="text-align: center;">Tidak ada data absensi untuk ditampilkan.</p>';
    } else {
        $first_record = $absensi_data[0];
        $html .= '
        <div class="student-info">
            <table>
                <tr><td>Nama Peserta Didik</td><td>: ' . htmlspecialchars($first_record['nama_siswa']) . '</td></tr>
                <tr><td>Tempat PKL</td><td>: ' . htmlspecialchars($first_record['nama_tempat_pkl'] ?? '-') . '</td></tr>
            </table>
        </div>
        <table class="attendance">
            <thead>
                <tr>
                    <th style="width:5%;">No</th>
                    <th style="width:25%;">Tanggal</th>
                    <th style="width:20%;">Status Kehadiran</th>
                    <th>Paraf</th>
                </tr>
            </thead>
            <tbody>';
        $no = 1;
        foreach ($absensi_data as $record) {
            $html .= '<tr>
                <td>' . $no++ . '</td>
                <td>' . date('d F Y', strtotime($record['tanggal_absen'])) . '</td>
                <td>' . htmlspecialchars($record['status_absen']) . '</td>
                <td></td>
            </tr>';
        }
        $html .= '</tbody></table>';
    }
}

// Bagian Tanda Tangan
$html .= '
<div class="signature-section">
    <table style="width: 100%; border-collapse: collapse;">
        <tbody>
            <tr>
                <td style="width: 60%; border: none;"></td>
                <td style="width: 40%; border: none; text-align: left;">
                    <p>...................., .................... ' . $tahun_pkl . '</p>
                    <p>Mengetahui,</p>
                    <p>Pembimbing Sekolah</p>
                    <div style="height: 60px;"></div>
                    <p><b>(' . htmlspecialchars($detail_pembimbing) . ')</b></p>
                </td>
            </tr>
        </tbody>
    </table>
</div>';

$html .= '
</body>
</html>';

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = "Rekap_Hadir_PKL_" . date('Ymd_His') . ".pdf";
$dompdf->stream($filename, ["Attachment" => false]);
exit();
