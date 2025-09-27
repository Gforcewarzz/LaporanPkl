<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

require_once 'partials/db.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// --- CEK ROLE ---
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';

// hanya siswa yang boleh cetak
if (!$is_siswa) {
    die("Laporan hanya bisa dicetak oleh peserta didik.");
}

$id_siswa_filter = $_SESSION['id_siswa'] ?? null;
if (!$id_siswa_filter) {
    die("Data siswa tidak ditemukan.");
}

// --- QUERY DATA JURNAL ---
$query_sql = "
    SELECT
        jh.id_jurnal_harian, jh.tanggal, jh.pekerjaan, jh.catatan,
        s.nama_siswa, s.kelas,
        tp.nama_tempat_pkl
    FROM jurnal_harian jh
    LEFT JOIN siswa s ON jh.siswa_id = s.id_siswa
    LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl
    WHERE jh.siswa_id = ?
    ORDER BY jh.tanggal ASC, jh.id_jurnal_harian ASC
";

$stmt = $koneksi->prepare($query_sql);
$stmt->bind_param("i", $id_siswa_filter);
$stmt->execute();
$result = $stmt->get_result();

$laporan_harian_data = [];
while ($row = $result->fetch_assoc()) {
    $laporan_harian_data[] = $row;
}
$stmt->close();

$nama_peserta_didik_header     = strtoupper($laporan_harian_data[0]['nama_siswa'] ?? '-');
$kelas_header                  = $laporan_harian_data[0]['kelas'] ?? '-';
$dunia_kerja_tempat_pkl_header = strtoupper($laporan_harian_data[0]['nama_tempat_pkl'] ?? '-');

// --- HTML & STYLE ---
$html = '
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jurnal Kegiatan PKL Harian</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, sans-serif;
            margin: 25px;
            font-size: 9pt;
            color: #333;
        }
        h1 {
            text-align: center;
            color: #222;
            font-size: 16pt;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        h2 {
            text-align: center;
            color: #555;
            font-size: 11pt;
            margin: 0 0 18px 0;
            text-transform: uppercase;
        }
        .header-info p {
            margin: 4px 0;
            font-size: 9.5pt;
            color: #333;
        }
        .header-info strong {
            display: inline-block;
            width: 190px;
            color: #444;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        th, td {
            border: 1px solid #bbb;
            padding: 8px 10px;
            font-size: 8.5pt;
        }
        th {
            background: #666;
            color: #fff;
            text-transform: uppercase;
        }
        tr:nth-child(even) { background: #f9f9f9; }
        .footer-notes {
            margin-top: 15px;
            font-size: 8pt;
            color: #555;
        }
    </style>
</head>
<body>
    <h1>JURNAL KEGIATAN HARIAN PKL</h1>
    <h2>PESERTA DIDIK SMKN 1 GANTAR</h2>

    <div class="header-info">
        <p><strong>Nama Peserta Didik</strong>: ' . htmlspecialchars($nama_peserta_didik_header) . '</p>
        <p><strong>Kelas</strong>: ' . htmlspecialchars($kelas_header) . '</p>
        <p><strong>Dunia Kerja/Tempat PKL</strong>: ' . htmlspecialchars($dunia_kerja_tempat_pkl_header) . '</p>
        <p><strong>Pembimbing Dunia Kerja</strong>: ........................................</p>
        <p><strong>Guru Pembimbing Sekolah</strong>: ........................................</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:20%">Hari/Tanggal</th>
                <th style="width:40%">Unit Kerja/Pekerjaan</th>
                <th style="width:25%">Catatan Pembimbing*</th>
                <th style="width:10%">Paraf</th>
            </tr>
        </thead>
        <tbody>';

$no = 1;
if (!empty($laporan_harian_data)) {
    $hari_indonesia = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];
    foreach ($laporan_harian_data as $row) {
        $nama_hari = $hari_indonesia[date('l', strtotime($row['tanggal']))];
        $tgl = $nama_hari . ', ' . date('d F Y', strtotime($row['tanggal']));
        $html .= "<tr>
            <td style='text-align:center;'>{$no}</td>
            <td>{$tgl}</td>
            <td>" . nl2br(htmlspecialchars($row['pekerjaan'] ?? '-')) . "</td>
            <td>" . nl2br(htmlspecialchars($row['catatan'] ?? '-')) . "</td>
            <td></td>
        </tr>";
        $no++;
    }
} else {
    $html .= "<tr><td colspan='5' style='text-align:center;color:#777;padding:15px'>Tidak ada data</td></tr>";
}

$html .= '
        </tbody>
    </table>

    <div class="footer-notes">
        <p>Jurnal kegiatan disusun oleh peserta didik sebagai dokumen pekerjaan.</p>
        <p>*) Catatan diberikan oleh pembimbing dunia kerja pada setiap kegiatan.</p>
    </div>
</body>
</html>';

// --- CETAK PDF ---
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream("Jurnal_Harian_PKL.pdf", ["Attachment" => false]);
exit;
