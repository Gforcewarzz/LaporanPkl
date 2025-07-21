<?php
session_start();
require_once 'partials/db.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Keamanan
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';
if (!$is_admin && !$is_guru) {
    die("Akses ditolak.");
}

// Ambil ID siswa dari URL
$siswa_id = isset($_GET['siswa_id']) ? (int)$_GET['siswa_id'] : 0;
if ($siswa_id === 0) {
    die("ID Siswa tidak valid.");
}

// Ambil data detail siswa, tempat pkl, dan guru pembimbing
$query_detail = "SELECT s.nama_siswa, s.nisn, s.kelas, tp.nama_tempat_pkl, gp.nama_pembimbing
                 FROM siswa s
                 LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl
                 LEFT JOIN guru_pembimbing gp ON s.pembimbing_id = gp.id_pembimbing
                 WHERE s.id_siswa = ?";
$stmt_siswa = $koneksi->prepare($query_detail);
$stmt_siswa->bind_param("i", $siswa_id);
$stmt_siswa->execute();
$siswa = $stmt_siswa->get_result()->fetch_assoc();
$stmt_siswa->close();

if (!$siswa) {
    die("Data siswa tidak ditemukan.");
}

// Ambil semua TP dan susun dalam hierarki
$tp_result = $koneksi->query("SELECT * FROM tujuan_pembelajaran ORDER BY id_induk, kode_tp");
$semua_tp = [];
$tp_anak = [];
while($row = $tp_result->fetch_assoc()){
    $semua_tp[$row['id_tp']] = $row;
    $tp_anak[$row['id_induk']][] = $row['id_tp'];
}

// --- PERUBAHAN DI SINI: Cari deskripsi untuk Pekerjaan/Proyek ---
$pekerjaan_proyek = ''; // Default kosong
foreach ($semua_tp as $tp) {
    if (isset($tp['kode_tp']) && $tp['kode_tp'] === '3.1') {
        $pekerjaan_proyek = $tp['deskripsi_tp'];
        break; // Hentikan loop jika sudah ketemu
    }
}
// --- AKHIR PERUBAHAN ---


$cache_nilai = [];

// Fungsi-fungsi Logika
function hitung_nilai($id_siswa, $id_tp, $koneksi, $tp_anak, &$cache_nilai) {
    $cache_key = "$id_siswa-$id_tp";
    if (isset($cache_nilai[$cache_key])) return $cache_nilai[$cache_key];
    $punya_anak = isset($tp_anak[$id_tp]);
    if (!$punya_anak) {
        $stmt = $koneksi->prepare("SELECT nilai FROM nilai_siswa WHERE siswa_id = ? AND id_tp = ?");
        $stmt->bind_param("ii", $id_siswa, $id_tp); $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc(); $stmt->close();
        $nilai = $result['nilai'] ?? 0; $cache_nilai[$cache_key] = $nilai; return $nilai;
    } else {
        $nilai_anak_arr = [];
        foreach ($tp_anak[$id_tp] as $id_anak) { $nilai_anak_arr[] = hitung_nilai($id_siswa, $id_anak, $koneksi, $tp_anak, $cache_nilai); }
        $total = array_sum($nilai_anak_arr); $jumlah = count($nilai_anak_arr);
        $rata_rata = ($jumlah > 0) ? $total / $jumlah : 0;
        $cache_nilai[$cache_key] = $rata_rata; return $rata_rata;
    }
}

function generate_deskripsi_narasi($id_siswa, $id_tp_utama, $koneksi, $semua_tp, $tp_anak) {
    $anak_utama = $tp_anak[$id_tp_utama] ?? [];
    if (empty($anak_utama)) return "-";
    $nilai_kompetensi = [];
    $cache_nilai_lokal = [];
    foreach ($anak_utama as $id_anak) {
        $nilai_kompetensi[] = ['deskripsi' => $semua_tp[$id_anak]['deskripsi_tp'], 'nilai' => hitung_nilai($id_siswa, $id_anak, $koneksi, $tp_anak, $cache_nilai_lokal)];
    }
    if (empty(array_filter($nilai_kompetensi, fn($n) => $n['nilai'] > 0))) return "Nilai belum terisi.";
    $tertinggi = ['nilai' => -1, 'deskripsi' => '']; $terendah = ['nilai' => 101, 'deskripsi' => ''];
    foreach ($nilai_kompetensi as $kompetensi) {
        if ($kompetensi['nilai'] > 0) {
            if ($kompetensi['nilai'] > $tertinggi['nilai']) { $tertinggi = $kompetensi; }
            if ($kompetensi['nilai'] < $terendah['nilai']) { $terendah = $kompetensi; }
        }
    }
    $deskripsi_utama = $semua_tp[$id_tp_utama]['deskripsi_tp'];
    $tertinggi['deskripsi'] = explode('(', $tertinggi['deskripsi'])[0]; $terendah['deskripsi'] = explode('(', $terendah['deskripsi'])[0];
    if ($tertinggi['nilai'] <= 0) return "Nilai belum lengkap.";
    if ($tertinggi['nilai'] == $terendah['nilai']) {
        return "Peserta didik sudah menunjukkan penguasaan yang baik terutama dalam hal " . lcfirst(trim($tertinggi['deskripsi'])) . ".";
    }
    return "Peserta didik menunjukkan kompetensi yang baik dalam " . lcfirst(trim($tertinggi['deskripsi'])) . ", namun masih perlu bimbingan pada " . lcfirst(trim($terendah['deskripsi'])) . ".";
}

function generate_pdf_table_rows($id_siswa, $id_induk, $level, $koneksi, $semua_tp, $tp_anak, &$cache_nilai) {
    if (!isset($tp_anak[$id_induk])) {
        return '';
    }
    $html_rows = '';
    foreach ($tp_anak[$id_induk] as $id_tp) {
        $item = $semua_tp[$id_tp];
        $nilai = hitung_nilai($id_siswa, $id_tp, $koneksi, $tp_anak, $cache_nilai);
        $padding = $level * 20;

        if ($nilai > 0) {
            $html_rows .= "<tr>";
            if($level == 0){
                $html_rows .= "<td style='text-align: center; font-weight: bold;'>" . htmlspecialchars($item['kode_tp']) . "</td>";
                $html_rows .= "<td style='font-weight: bold;'>" . htmlspecialchars($item['deskripsi_tp']) . "</td>";
            } else {
                $html_rows .= "<td></td>";
                $html_rows .= "<td style='padding-left: " . ($padding) . "px;'>" . htmlspecialchars($item['kode_tp']) . ". " . htmlspecialchars($item['deskripsi_tp']) . "</td>";
            }
            $html_rows .= "<td style='text-align: center;'>" . number_format($nilai, 2) . "</td>";
            $html_rows .= "<td>";
            if ($level == 0) {
                $html_rows .= htmlspecialchars(generate_deskripsi_narasi($id_siswa, $id_tp, $koneksi, $semua_tp, $tp_anak));
            }
            $html_rows .= "</td>";
            $html_rows .= "</tr>";
            
            $html_rows .= generate_pdf_table_rows($id_siswa, $id_tp, $level + 1, $koneksi, $semua_tp, $tp_anak, $cache_nilai);
        }
    }
    return $html_rows;
}

// Membuat Konten HTML untuk PDF
$table_content = generate_pdf_table_rows($siswa_id, NULL, 0, $koneksi, $semua_tp, $tp_anak, $cache_nilai);

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Penilaian Kompetensi</title>
    <style>
        @page { margin: 25mm; }
        body { font-family: Arial, sans-serif; font-size: 11pt; color: #333; }
        .header-info { margin-bottom: 20px; }
        .header-info table { width: 100%; border-collapse: collapse; }
        .header-info td { padding: 3px 0; }
        .header-info .label { width: 200px; }
        table.report { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table.report th, table.report td { border: 1px solid black; padding: 8px; text-align: left; vertical-align: top; }
        table.report th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
        .signature-section { margin-top: 40px; }
        .signature-section table { width: 100%; border: none; }
        .signature-section .signature-cell { width: 50%; text-align: center; border: none; }
        .signature-name { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header-info">
        <table>
            <tr><td class="label">Nama Peserta Didik</td><td>: ' . htmlspecialchars($siswa['nama_siswa']) . '</td></tr>
            <tr><td class="label">Dunia Kerja Tempat PKL</td><td>: ' . htmlspecialchars($siswa['nama_tempat_pkl'] ?? '-') . '</td></tr>
            <tr><td class="label">Nama Instruktur</td><td>: .....................................................</td></tr>
            <tr><td class="label">Nama Guru Pembimbing</td><td>: ' . htmlspecialchars($siswa['nama_pembimbing'] ?? '-') . '</td></tr>
            <tr><td class="label">Pekerjaan/Proyek</td><td>: ' . htmlspecialchars($pekerjaan_proyek) . '</td></tr>
        </table>
    </div>

    <table class="report">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 35%;">Tujuan Pembelajaran/Indikator</th>
                <th style="width: 15%;">Nilai</th>
                <th>Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            ' . $table_content . '
        </tbody>
    </table>

    <div class="signature-section">
        <p>Keterangan:</p>
        <br>
        <table>
            <tr>
                <td class="signature-cell">
                    Guru Pembimbing
                    <br><br><br><br><br>
                    <span class="signature-name">' . htmlspecialchars($siswa['nama_pembimbing'] ?? '....................') . '</span>
                </td>
                <td class="signature-cell">
                    ...................., ' . date('d F Y') . '
                    <br>
                    Pembimbing Dunia Kerja
                    <br><br><br><br><br>
                    <span class="signature-name">....................</span>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>';

// Proses Generate PDF dengan Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Laporan_Nilai_" . str_replace(' ', '_', $siswa['nama_siswa']) . ".pdf", ["Attachment" => false]);
exit();
?>