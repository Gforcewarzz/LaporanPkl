<?php

session_start();
date_default_timezone_set('Asia/Jakarta');

require_once 'partials/db.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru  = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_siswa && !$is_admin && !$is_guru) {
    header('Location: ../login.php');
    exit();
}

$id_siswa_filter  = null;
$guru_id_bimbingan = $_SESSION['id_guru_pendamping'] ?? null;

$where_clauses = [];
$query_params  = [];
$query_types   = "";

$keyword    = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date   = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : '';

if ($is_siswa) {
    $id_siswa_filter = $_SESSION['id_siswa'] ?? null;
    if ($id_siswa_filter) {
        $where_clauses[] = "jh.siswa_id = ?";
        $query_params[]  = $id_siswa_filter;
        $query_types    .= "i";
    }
} elseif ($is_admin) {
    if (isset($_GET['siswa_id']) && !empty($_GET['siswa_id'])) {
        $id_siswa_filter = (int)$_GET['siswa_id'];
        $where_clauses[] = "jh.siswa_id = ?";
        $query_params[]  = $id_siswa_filter;
        $query_types    .= "i";
    }
} elseif ($is_guru) {
    if ($guru_id_bimbingan) {
        $where_clauses[] = "s.pembimbing_id = ?";
        $query_params[]  = $guru_id_bimbingan;
        $query_types    .= "i";
    }
    if (isset($_GET['siswa_id']) && !empty($_GET['siswa_id'])) {
        $id_siswa_filter = (int)$_GET['siswa_id'];
        $where_clauses[] = "jh.siswa_id = ?";
        $query_params[]  = $id_siswa_filter;
        $query_types    .= "i";
    }
}

if (!empty($keyword)) {
    $where_clauses[] = "(jh.pekerjaan LIKE ? OR jh.catatan LIKE ?)";
    $query_params[]  = "%" . $keyword . "%";
    $query_params[]  = "%" . $keyword . "%";
    $query_types    .= "ss";
}

if (!empty($start_date)) {
    $where_clauses[] = "jh.tanggal >= ?";
    $query_params[]  = $start_date;
    $query_types    .= "s";
}
if (!empty($end_date)) {
    $where_clauses[] = "jh.tanggal <= ?";
    $query_params[]  = $end_date;
    $query_types    .= "s";
}

$query_sql = "
    SELECT
        jh.id_jurnal_harian, jh.tanggal, jh.pekerjaan, jh.catatan,
        s.nama_siswa, s.kelas, s.no_induk,
        j.nama_jurusan,
        tp.nama_tempat_pkl, tp.nama_instruktur AS nama_instruktur_pkl,
        gp.nama_pembimbing AS nama_guru_pembimbing
    FROM
        jurnal_harian jh
    LEFT JOIN
        siswa s ON jh.siswa_id = s.id_siswa
    LEFT JOIN
        tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl
    LEFT JOIN
        guru_pembimbing gp ON s.pembimbing_id = gp.id_pembimbing
    LEFT JOIN
        jurusan j ON s.jurusan_id = j.id_jurusan";

if (!empty($where_clauses)) {
    $query_sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$query_sql .= " ORDER BY jh.tanggal ASC, jh.id_jurnal_harian ASC";

$stmt = $koneksi->prepare($query_sql);

if ($stmt === false) {
    error_log("Error preparing statement: " . $koneksi->error);
    die("Terjadi kesalahan sistem saat menyiapkan laporan.");
}

if (!empty($query_params)) {
    $stmt->bind_param($query_types, ...$query_params);
}

$stmt->execute();
$result              = $stmt->get_result();
$laporan_harian_data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $laporan_harian_data[] = $row;
    }
}
$stmt->close();

$nama_peserta_didik_header     = '-';
$kelas_header                  = '-';
$dunia_kerja_tempat_pkl_header = '-';
$nama_instruktur_header        = '-';
$nama_guru_pembimbing_header   = '-';
$info_tambahan_pdf             = [];

if (!empty($laporan_harian_data)) {
    $first_row                     = $laporan_harian_data[0];
    $nama_peserta_didik_header     = htmlspecialchars($first_row['nama_siswa'] ?? '-');
    $kelas_header                  = htmlspecialchars($first_row['kelas'] ?? '-');
    $dunia_kerja_tempat_pkl_header = htmlspecialchars($first_row['nama_tempat_pkl'] ?? '-');
    $nama_instruktur_header        = htmlspecialchars($first_row['nama_instruktur_pkl'] ?? '-');
    $nama_guru_pembimbing_header   = htmlspecialchars($first_row['nama_guru_pembimbing'] ?? '-');

    if ($is_admin && !isset($_GET['siswa_id'])) {
        $nama_peserta_didik_header     = 'Seluruh Siswa';
        $kelas_header                  = 'Beragam';
        $dunia_kerja_tempat_pkl_header = 'Beragam';
        $nama_instruktur_header        = 'Beragam';
        $nama_guru_pembimbing_header   = 'Beragam';
    } elseif ($is_guru && !isset($_GET['siswa_id'])) {
        $nama_peserta_didik_header     = 'Siswa Bimbingan Anda';
        $kelas_header                  = 'Beragam';
        $dunia_kerja_tempat_pkl_header = 'Beragam';
        $nama_instruktur_header        = 'Beragam';
        $nama_guru_pembimbing_header   = 'Beragam';
    }
} else {
    if ($is_siswa && ($id_siswa = $_SESSION['id_siswa'] ?? null)) {
        $nama_peserta_didik_header = $_SESSION['siswa_nama'] ?? '-';
        $query_siswa_detail = "SELECT s.nama_siswa, s.kelas, tp.nama_tempat_pkl, tp.nama_instruktur AS nama_instruktur_pkl, gp.nama_pembimbing AS nama_guru_pembimbing FROM siswa s LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl LEFT JOIN guru_pembimbing gp ON s.pembimbing_id = gp.id_pembimbing WHERE s.id_siswa = ?";
        $stmt_siswa_detail  = $koneksi->prepare($query_siswa_detail);
        if ($stmt_siswa_detail) {
            $stmt_siswa_detail->bind_param("i", $id_siswa);
            $stmt_siswa_detail->execute();
            $res_siswa_detail          = $stmt_siswa_detail->get_result()->fetch_assoc();
            $kelas_header              = htmlspecialchars($res_siswa_detail['kelas'] ?? '-');
            $dunia_kerja_tempat_pkl_header = htmlspecialchars($res_siswa_detail['nama_tempat_pkl'] ?? '-');
            $nama_instruktur_header        = htmlspecialchars($res_siswa_detail['nama_instruktur_pkl'] ?? '-');
            $nama_guru_pembimbing_header   = htmlspecialchars($res_siswa_detail['nama_pembimbing'] ?? '-');
            $stmt_siswa_detail->close();
        }
    } elseif ($is_admin && isset($_GET['siswa_id']) && ($id_siswa_from_get = (int)$_GET['siswa_id'])) {
        $query_siswa_detail = "SELECT nama_siswa, kelas FROM siswa WHERE id_siswa = ?";
        $stmt_siswa_detail  = $koneksi->prepare($query_siswa_detail);
        if ($stmt_siswa_detail) {
            $stmt_siswa_detail->bind_param("i", $id_siswa_from_get);
            $stmt_siswa_detail->execute();
            $res_siswa_detail          = $stmt_siswa_detail->get_result()->fetch_assoc();
            $nama_peserta_didik_header = htmlspecialchars($res_siswa_detail['nama_siswa'] ?? '-') . ' (Tidak Ada Laporan)';
            $kelas_header              = htmlspecialchars($res_siswa_detail['kelas'] ?? '-');
            $stmt_siswa_detail->close();
        }
    } elseif ($is_guru && isset($_GET['siswa_id']) && ($id_siswa_from_get = (int)$_GET['siswa_id'])) {
        $query_siswa_detail = "SELECT nama_siswa, kelas FROM siswa WHERE id_siswa = ?";
        $stmt_siswa_detail  = $koneksi->prepare($query_siswa_detail);
        if ($stmt_siswa_detail) {
            $stmt_siswa_detail->bind_param("i", $id_siswa_from_get);
            $stmt_siswa_detail->execute();
            $res_siswa_detail          = $stmt_siswa_detail->get_result()->fetch_assoc();
            $nama_peserta_didik_header = htmlspecialchars($res_siswa_detail['nama_siswa'] ?? '-') . ' (Tidak Ada Laporan)';
            $kelas_header              = htmlspecialchars($res_siswa_detail['kelas'] ?? '-');
            $stmt_siswa_detail->close();
        }
    } else {
        $nama_peserta_didik_header = 'Tidak Ada Data Laporan';
        $kelas_header              = '-';
    }
}

if (!empty($keyword)) {
    $info_tambahan_pdf[] = '<strong>Kata Kunci:</strong> "' . htmlspecialchars($keyword) . '"';
}
if (!empty($start_date) && !empty($end_date)) {
    $info_tambahan_pdf[] = '<strong>Rentang Tanggal:</strong> ' . date('d F Y', strtotime($start_date)) . ' - ' . date('d F Y', strtotime($end_date));
} elseif (!empty($start_date)) {
    $info_tambahan_pdf[] = '<strong>Dari Tanggal:</strong> ' . date('d F Y', strtotime($start_date));
} elseif (!empty($end_date)) {
    $info_tambahan_pdf[] = '<strong>Sampai Tanggal:</strong> ' . date('d F Y', strtotime($end_date));
}

$koneksi->close();

$html = '
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jurnal Kegiatan PKL Harian</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            margin: 25px;
            font-size: 9pt;
            color: #333;
        }
        h1 {
            text-align: center;
            color: #444; /* Abu-abu gelap */
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        h2 {
            text-align: center;
            color: #666; /* Abu-abu sedang */
            font-size: 12pt;
            margin-top: 0;
            margin-bottom: 25px;
        }
        .header-section {
            padding: 0;
            margin-bottom: 30px;
            background-color: #f8f8f8; /* Abu-abu terang */
            padding: 15px 20px;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
        }
        .header-info p {
            margin: 6px 0;
            line-height: 1.4;
            font-size: 9.5pt;
        }
        .header-info strong {
            display: inline-block;
            width: 180px;
            font-weight: bold;
            color: #34495e;
            margin-right: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #d0d0d0; /* Abu-abu muda */
            padding: 10px 12px;
            text-align: left;
            font-size: 8.5pt;
            vertical-align: top;
            line-height: 1.5;
        }
        th {
            background-color: #a0a0a0; /* Abu-abu lebih gelap */
            font-weight: bold;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        tr:nth-child(even) {
            background-color: #f8f8f8;
        }
        tr:hover {
            background-color: #e0e0e0;
        }
    </style>
</head>
<body>
    <h1>JURNAL KEGIATAN HARIAN PRAKTEK KERJA LAPANGAN</h1>
    <h2>PESERTA DIDIK SMKN 1 GANTAR</h2>
    <div class="header-section">
        <div class="header-info">
            <p><strong>Nama Peserta Didik</strong>: ' . $nama_peserta_didik_header . '</p>
            <p><strong>Kelas</strong>: ' . $kelas_header . '</p>
            <p><strong>Dunia Kerja/Tempat PKL</strong>: ' . $dunia_kerja_tempat_pkl_header . '</p>
            <p><strong>Pembimbing Dunia Kerja</strong>: ' . $nama_instruktur_header . '</p>
            <p><strong>Guru Pembimbing Sekolah</strong>: ' . $nama_guru_pembimbing_header . '</p>
            ' . (!empty($info_tambahan_pdf) ? '<p>' . implode('<br>', $info_tambahan_pdf) . '</p>' : '') . '
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Hari/Tanggal</th>
                <th>Unit Kerja/Pekerjaan</th>
                <th>Catatan*</th>
            </tr>
        </thead>
        <tbody>';

$no = 1;
if (!empty($laporan_harian_data)) {
    foreach ($laporan_harian_data as $row) {
        $hari_indonesia = [
            'Sunday'    => 'Minggu',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu'
        ];
        $nama_hari_inggris = date('l', strtotime($row['tanggal']));
        $formatted_date_id = $hari_indonesia[$nama_hari_inggris] . ', ' . date('d F Y', strtotime($row['tanggal']));

        $html .= "<tr>
            <td>{$no}</td>
            <td>" . htmlspecialchars($formatted_date_id) . "</td>
            <td>" . nl2br(htmlspecialchars($row['pekerjaan'] ?? '-')) . "</td>
            <td>" . nl2br(htmlspecialchars($row['catatan'] ?? '-')) . "</td>
        </tr>";
        $no++;
    }
} else {
    $html .= "<tr><td colspan='4' style='text-align: center; padding: 20px; color: #7f8c8d;'>Tidak ada laporan harian ditemukan untuk kriteria ini.</td></tr>";
}
$html .= '
        </tbody>
    </table>
</body>
</html>';

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');
$options->set('chroot', realpath(__DIR__));

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = "Jurnal_Kegiatan_PKL_Harian_" . date('Ymd_His');
if (!empty($id_siswa_filter)) {
    $filename .= "_Siswa_" . $id_siswa_filter;
}
if (!empty($start_date) || !empty($end_date)) {
    $filename .= "_Periode";
    if (!empty($start_date)) {
        $filename .= "_" . str_replace('-', '', $start_date);
    }
    if (!empty($end_date)) {
        $filename .= "_" . str_replace('-', '', $end_date);
    }
}
$filename .= ".pdf";

$dompdf->stream($filename, ["Attachment" => false]);
exit();
