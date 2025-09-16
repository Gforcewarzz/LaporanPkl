<?php
session_start();
require_once 'partials/db.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Keamanan: Admin, Guru, dan Siswa boleh akses
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';

if (!$is_admin && !$is_guru && !$is_siswa) {
    die("Akses ditolak. Anda harus login.");
}

// Ambil ID dari URL
$siswa_id = isset($_GET['siswa_id']) ? (int)$_GET['siswa_id'] : 0;
$jurnal_ids_str = isset($_GET['jurnal_ids']) ? $_GET['jurnal_ids'] : '';

if ($siswa_id === 0 || empty($jurnal_ids_str)) {
    die("Data siswa atau pilihan jurnal tidak lengkap.");
}

// Ubah string ID jurnal menjadi array integer
$jurnal_ids = array_map('intval', explode(',', $jurnal_ids_str));

// Ambil data detail siswa
$query_detail = "SELECT s.nama_siswa, s.nisn, s.kelas, tp.nama_tempat_pkl FROM siswa s LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl WHERE s.id_siswa = ?";
$stmt_siswa = $koneksi->prepare($query_detail);
$stmt_siswa->bind_param("i", $siswa_id);
$stmt_siswa->execute();
$siswa = $stmt_siswa->get_result()->fetch_assoc();
$stmt_siswa->close();
if (!$siswa) die("Data siswa tidak ditemukan.");

// Ambil semua TP statis
$tp_result = $koneksi->query("SELECT * FROM tujuan_pembelajaran ORDER BY id_induk, kode_tp");
$semua_tp = [];
while ($row = $tp_result->fetch_assoc()) {
    $semua_tp[$row['id_tp']] = $row;
}

// Gabungkan data jurnal YANG DIPILIH ke dalam struktur TP
$induk_jurnal_id = null;
foreach ($semua_tp as $tp) {
    if ($tp['kode_tp'] === '3') {
        $induk_jurnal_id = $tp['id_tp'];
        break;
    }
}
if ($induk_jurnal_id) {
    $in_placeholders = implode(',', array_fill(0, count($jurnal_ids), '?'));
    $stmt_jurnal = $koneksi->prepare("SELECT id_jurnal_kegiatan, nama_pekerjaan FROM jurnal_kegiatan WHERE id_jurnal_kegiatan IN ($in_placeholders)");
    $stmt_jurnal->bind_param(str_repeat('i', count($jurnal_ids)), ...$jurnal_ids);
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

// Buat struktur pohon (tree) dari data yang sudah digabung
$tp_anak = [];
foreach ($semua_tp as $tp) {
    $id_induk = $tp['id_induk'] ?? 0;
    $tp_anak[$id_induk][] = $tp['id_tp'];
}

// Fungsi untuk membuat baris tabel PDF (versi KOSONG)
function generate_pdf_table_rows_kosong($id_induk, $level, $semua_tp, $tp_anak)
{
    if (!isset($tp_anak[$id_induk])) return '';
    $html_rows = '';
    foreach ($tp_anak[$id_induk] as $id_tp) {
        $item = $semua_tp[$id_tp];
        $punya_anak = isset($tp_anak[$id_tp]);
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
        $html_rows .= "<td style='text-align: center;'>";
        if (!$punya_anak) $html_rows .= '...'; // Titik-titik untuk diisi
        $html_rows .= "</td>";
        $html_rows .= "<td></td>"; // Kolom deskripsi kosong
        $html_rows .= "</tr>";
        if ($punya_anak) {
            $html_rows .= generate_pdf_table_rows_kosong($id_tp, $level + 1, $semua_tp, $tp_anak);
        }
    }
    return $html_rows;
}

// Membuat Konten HTML untuk PDF
$table_content = generate_pdf_table_rows_kosong(0, 0, $semua_tp, $tp_anak);
$html = '...'; // Isi variabel $html dengan template HTML PDF Anda yang sudah ada

// (Kode HTML lengkap untuk PDF sama seperti sebelumnya, tidak perlu diubah)
$html = '
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Formulir Penilaian DUDI</title>
<style>
    @page { margin: 25mm; } body { font-family: Arial, sans-serif; font-size: 11pt; color: #333; }
    h2 { text-align: center; margin-bottom: 25px; line-height: 1.4; }
    .header-info table { width: 100%; border-collapse: collapse; margin-bottom: 20px;}
    .header-info td { padding: 3px 0; } .header-info .label { width: 200px; }
    table.report { width: 100%; border-collapse: collapse; margin-top: 15px; }
    table.report th, table.report td { border: 1px solid black; padding: 7px; text-align: left; vertical-align: top; }
    table.report th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
    .signature-section { margin-top: 40px; page-break-inside: avoid; }
    .signature-section table { width: 100%; border: none; }
    .signature-section .signature-cell { width: 50%; text-align: center; }
    .signature-name { font-weight: bold; }
</style>
</head><body>
    <h2>FORMULIR PENILAIAN KOMPETENSI<br>PRAKTIK KERJA LAPANGAN (PKL)</h2>
    <div class="header-info">
        <table>
            <tr><td class="label">Nama Peserta Didik</td><td>: ' . htmlspecialchars($siswa['nama_siswa']) . '</td></tr>
            <tr><td class="label">Dunia Kerja Tempat PKL</td><td>: ' . htmlspecialchars($siswa['nama_tempat_pkl'] ?? '-') . '</td></tr>
            <tr><td class="label">Nama Instruktur</td><td>: .....................................................</td></tr>
        </table>
    </div>
    <table class="report">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 45%;">Tujuan Pembelajaran/Indikator</th>
                <th style="width: 10%;">Nilai</th>
                <th>Catatan / Deskripsi Pencapaian</th>
            </tr>
        </thead>
        <tbody>' . $table_content . '</tbody>
    </table>
    <div class="signature-section">
        <table>
            <tr>
                <td class="signature-cell" style="text-align: right; padding-right: 50px;">
                    ...................., ............................................
                    <br>Pembimbing Dunia Kerja<br><br><br><br><br>
                    <span class="signature-name">(....................)</span>
                </td>
            </tr>
        </table>
    </div>
</body></html>';


// Proses Generate PDF dengan Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Formulir_Penilaian_DUDI_" . str_replace(' ', '_', $siswa['nama_siswa']) . ".pdf", ["Attachment" => false]);
exit();