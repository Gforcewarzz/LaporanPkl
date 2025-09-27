<?php
session_start();
require_once 'partials/db.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Keamanan
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';
if (!$is_admin && !$is_guru) die("Akses ditolak.");

$siswa_id = isset($_GET['siswa_id']) ? (int)$_GET['siswa_id'] : 0;
if ($siswa_id === 0) die("ID Siswa tidak valid.");

// Query untuk mengambil semua data siswa yang dibutuhkan
$query_detail = "
    SELECT 
        s.nama_siswa, s.nisn, s.kelas,
        j.nama_jurusan,
        j.prodi,
        tp.nama_tempat_pkl,
        gp.nama_pembimbing
    FROM siswa s
    LEFT JOIN jurusan j ON s.jurusan_id = j.id_jurusan
    LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl
    LEFT JOIN guru_pembimbing gp ON s.pembimbing_id = gp.id_pembimbing
    WHERE s.id_siswa = ?
";
$stmt_siswa = $koneksi->prepare($query_detail);
$stmt_siswa->bind_param("i", $siswa_id);
$stmt_siswa->execute();
$siswa = $stmt_siswa->get_result()->fetch_assoc();
$stmt_siswa->close();

if (!$siswa) die("Data siswa tidak ditemukan.");


// --- LOGIKA PENGAMBILAN DATA NILAI DAN TP (TETAP ADA) ---
$semua_nilai_siswa = [];
$graded_jurnal_ids = [];
$stmt_nilai = $koneksi->prepare("SELECT id_tp, jurnal_kegiatan_id, nilai FROM nilai_siswa WHERE siswa_id = ?");
$stmt_nilai->bind_param("i", $siswa_id);
$stmt_nilai->execute();
$result_nilai = $stmt_nilai->get_result();
while ($row = $result_nilai->fetch_assoc()) {
    if (!empty($row['jurnal_kegiatan_id'])) {
        $jurnal_id = $row['jurnal_kegiatan_id'];
        $key = 'jurnal_' . $jurnal_id;
        $semua_nilai_siswa[$key] = $row['nilai'];
        $graded_jurnal_ids[] = $jurnal_id;
    } else if (!empty($row['id_tp'])) {
        $semua_nilai_siswa[$row['id_tp']] = $row['nilai'];
    }
}
$stmt_nilai->close();

$tp_result = $koneksi->query("SELECT * FROM tujuan_pembelajaran ORDER BY id_induk, kode_tp");
$semua_tp = [];
while ($row = $tp_result->fetch_assoc()) {
    $semua_tp[$row['id_tp']] = $row;
}

if (!empty($graded_jurnal_ids)) {
    $induk_jurnal_id = null;
    foreach ($semua_tp as $tp) {
        if ($tp['kode_tp'] === '3') {
            $induk_jurnal_id = $tp['id_tp'];
            break;
        }
    }
    if ($induk_jurnal_id) {
        $in_placeholders = implode(',', array_fill(0, count($graded_jurnal_ids), '?'));
        $stmt_jurnal = $koneksi->prepare("SELECT id_jurnal_kegiatan, nama_pekerjaan FROM jurnal_kegiatan WHERE id_jurnal_kegiatan IN ($in_placeholders)");
        $stmt_jurnal->bind_param(str_repeat('i', count($graded_jurnal_ids)), ...$graded_jurnal_ids);
        $stmt_jurnal->execute();
        $jurnal_list = $stmt_jurnal->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_jurnal->close();
        $sub_kode = 1;
        foreach ($jurnal_list as $jurnal) {
            $jurnal_tp_id = 'jurnal_' . $jurnal['id_jurnal_kegiatan'];
            $semua_tp[$jurnal_tp_id] = [
                'id_tp' => $jurnal_tp_id,
                'id_induk' => $induk_jurnal_id,
                'kode_tp' => '3.' . $sub_kode++,
                'deskripsi_tp' => $jurnal['nama_pekerjaan']
            ];
        }
    }
}

$tp_anak = [];
foreach ($semua_tp as $tp) {
    $id_induk = $tp['id_induk'] ?? 0;
    $tp_anak[$id_induk][] = $tp['id_tp'];
}
$cache_nilai_terhitung = [];

// --- FUNGSI-FUNGSI LOGIKA (TETAP ADA) ---
function hitung_nilai($id_tp, $semua_nilai_siswa, $tp_anak, &$cache_nilai_terhitung)
{
    if (isset($cache_nilai_terhitung[$id_tp])) return $cache_nilai_terhitung[$id_tp];
    $punya_anak = isset($tp_anak[$id_tp]);
    if (!$punya_anak) {
        $nilai = $semua_nilai_siswa[$id_tp] ?? 0;
        $cache_nilai_terhitung[$id_tp] = $nilai;
        return $nilai;
    } else {
        $nilai_anak_arr = [];
        foreach ($tp_anak[$id_tp] as $id_anak) {
            $nilai_anak_arr[] = hitung_nilai($id_anak, $semua_nilai_siswa, $tp_anak, $cache_nilai_terhitung);
        }
        $filtered_nilai = array_filter($nilai_anak_arr, fn($n) => $n > 0);
        $rata_rata = empty($filtered_nilai) ? 0 : array_sum($filtered_nilai) / count($filtered_nilai);
        $cache_nilai_terhitung[$id_tp] = $rata_rata;
        return $rata_rata;
    }
}

function generate_deskripsi_narasi($id_tp_utama, $semua_nilai_siswa, $semua_tp, $tp_anak)
{
    $anak_utama = $tp_anak[$id_tp_utama] ?? [];
    if (empty($anak_utama)) return "-";
    $cache_nilai_lokal = [];
    $nilai_kompetensi = [];
    foreach ($anak_utama as $id_anak) {
        $nilai = hitung_nilai($id_anak, $semua_nilai_siswa, $tp_anak, $cache_nilai_lokal);
        if ($nilai > 0) {
            $nilai_kompetensi[] = ['deskripsi' => $semua_tp[$id_anak]['deskripsi_tp'], 'nilai' => $nilai];
        }
    }
    if (empty($nilai_kompetensi)) return "Nilai belum terisi.";
    $tertinggi = null;
    $terendah = null;
    foreach ($nilai_kompetensi as $kompetensi) {
        if ($tertinggi === null || $kompetensi['nilai'] > $tertinggi['nilai']) $tertinggi = $kompetensi;
        if ($terendah === null || $kompetensi['nilai'] < $terendah['nilai']) $terendah = $kompetensi;
    }
    $desc_tertinggi = lcfirst(trim(explode('(', $tertinggi['deskripsi'])[0]));
    $desc_terendah = lcfirst(trim(explode('(', $terendah['deskripsi'])[0]));
    if ($terendah['nilai'] < 80) {
        if ($tertinggi['nilai'] == $terendah['nilai']) {
            return "Peserta didik masih perlu meningkatkan kompetensi dalam hal {$desc_terendah}.";
        }
        return "Peserta didik menunjukkan kompetensi yang baik dalam {$desc_tertinggi}, namun masih perlu bimbingan pada {$desc_terendah}.";
    } else {
        if ($tertinggi['nilai'] == $terendah['nilai']) {
            return "Peserta didik secara konsisten menunjukkan penguasaan yang sangat baik pada semua kompetensi.";
        }
        return "Peserta didik menunjukkan penguasaan yang sangat baik pada seluruh kompetensi, terutama menonjol dalam hal {$desc_tertinggi}.";
    }
}

function generate_pdf_table_rows($id_induk, $level, $semua_nilai_siswa, $semua_tp, $tp_anak, &$cache_nilai_terhitung)
{
    if (!isset($tp_anak[$id_induk])) return '';
    $html_rows = '';
    foreach ($tp_anak[$id_induk] as $id_tp) {
        $item = $semua_tp[$id_tp];
        $nilai = hitung_nilai($id_tp, $semua_nilai_siswa, $tp_anak, $cache_nilai_terhitung);
        $punya_anak = isset($tp_anak[$id_tp]);
        if (!$punya_anak && $nilai <= 0) continue;
        $padding = $level * 20;
        $fontWeight = ($level == 0 || $punya_anak) ? 'font-weight: bold;' : '';
        $html_rows .= "<tr>";
        if ($level == 0) {
            $html_rows .= "<td style='text-align: center; {$fontWeight}'>" . htmlspecialchars($item['kode_tp']) . "</td>";
            $html_rows .= "<td style='{$fontWeight}'>" . htmlspecialchars($item['deskripsi_tp']) . "</td>";
        } else {
            $html_rows .= "<td></td>";
            $html_rows .= "<td style='padding-left: " . ($padding) . "px;'>- " . htmlspecialchars($item['deskripsi_tp']) . "</td>";
        }
        $html_rows .= "<td style='text-align: center; {$fontWeight}'>";
        $html_rows .= ($nilai > 0) ? number_format($nilai, 1) : '-';
        $html_rows .= "</td>";
        $html_rows .= "<td>" . htmlspecialchars(generate_deskripsi_narasi($id_tp, $semua_nilai_siswa, $semua_tp, $tp_anak)) . "</td></tr>";
        if ($punya_anak) {
            $html_rows .= generate_pdf_table_rows($id_tp, $level + 1, $semua_nilai_siswa, $semua_tp, $tp_anak, $cache_nilai_terhitung);
        }
    }
    return $html_rows;
}

$table_content = generate_pdf_table_rows(0, 0, $semua_nilai_siswa, $semua_tp, $tp_anak, $cache_nilai_terhitung);

$html = '
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Lembar Observasi PKL</title>
<style>
    @page { margin: 20mm; }
    body { font-family: Arial, sans-serif; font-size: 11pt; color: #000; }
    .header-title { text-align: center; font-size: 14pt; font-weight: bold; text-transform: uppercase; margin-bottom: 30px; }
    .info-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
    .info-table td { padding: 4px 0; }
    .info-table .label { width: 200px; }
    .info-table .separator { width: 15px; }
    .report-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    .report-table th, .report-table td { border: 1px solid black; padding: 7px; text-align: left; vertical-align: top; }
    .report-table th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
    .notes-table { width: 100%; border-collapse: collapse; margin-top: 15px; border: 1px solid black; }
    .notes-table td { border: 1px solid black; padding: 10px; vertical-align: top; height: 250px; }
    .notes-label { font-weight: bold; display: block; margin-bottom: 5px; }
    .dotted-lines { line-height: 1.8; color: #555; }
    .signature-section { margin-top: 15px; font-size: 11pt; page-break-inside: avoid; }
    .signature-table { width: 100%; margin-top: 5px; border: none; }
    .signature-table td { width: 50%; text-align: left; border: none; vertical-align: top; }
    .signature-name { text-decoration: underline; font-weight: bold; }
</style>
</head><body>
    <div class="header-title">LEMBAR OBSERVASI PKL</div>

    <table class="info-table">
        <tr><td class="label">Nama Peserta Didik</td><td class="separator">:</td><td>' . htmlspecialchars($siswa['nama_siswa']) . '</td></tr>
        <tr><td class="label">Dunia Kerja Tempat PKL</td><td class="separator">:</td><td>' . htmlspecialchars($siswa['nama_tempat_pkl'] ?? '-') . '</td></tr>
        <tr><td class="label">Nama Instruktur</td><td class="separator">:</td><td>.......................................</td></tr>
        <tr><td class="label">Nama Guru Pembimbing</td><td class="separator">:</td><td>' . htmlspecialchars($siswa['nama_pembimbing'] ?? '-') . '</td></tr>
    </table>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 45%;">Tujuan Pembelajaran/Indikator</th>
                <th style="width: 10%;">Nilai</th>
                <th>Deskripsi Pencapaian</th>
            </tr>
        </thead>
        <tbody>' . $table_content . '</tbody>
    </table>

    <table class="notes-table">
        <tr>
            <td>
                <span class="notes-label">Catatan Guru Pembimbing :</span>
                <div class="dotted-lines">
                ...................................................................................................................<br>
                ...................................................................................................................<br>
                </div>
                <br><br>
                <span class="notes-label">Catatan Instruktur Industri :</span>
                <div class="dotted-lines">
                ...................................................................................................................<br>
                ...................................................................................................................<br>
                </div>
            </td>
        </tr>
    </table>

    <div class="signature-section">
        <strong>Keterangan:</strong>
        <table class="signature-table">
            <tbody>
                <tr>
                    <td>
                        Guru Pembimbing
                        <br><br><br><br><br>
                        <span class="signature-name">' . htmlspecialchars($siswa['nama_pembimbing'] ?? '.........................') . '</span>
                    </td>
                    <td>
                        .................................., ...................................... ' . date('Y') . '
                        <br>
                        Pembimbing Dunia Kerja
                        <br><br><br><br><br>
                        <span class="signature-name">.........................</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body></html>';

// Proses Generate PDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Laporan_Observasi_Nilai_" . str_replace(' ', '_', $siswa['nama_siswa']) . ".pdf", ["Attachment" => false]);
exit();
