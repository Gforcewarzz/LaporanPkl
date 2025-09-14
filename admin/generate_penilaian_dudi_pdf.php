<?php
session_start();
require_once 'partials/db.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// --- PERUBAHAN KEAMANAN: Siswa sekarang boleh akses ---
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';

if (!$is_admin && !$is_guru && !$is_siswa) {
    die("Akses ditolak. Anda harus login.");
}

// Ambil ID siswa dari URL
$siswa_id = isset($_GET['siswa_id']) ? (int)$_GET['siswa_id'] : 0;
if ($siswa_id === 0) {
    die("ID Siswa tidak valid.");
}

// Ambil data detail siswa
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

// Ambil ID jurnal yang sudah dinilai untuk membangun struktur TP yang benar
$graded_jurnal_ids = [];
$stmt_nilai = $koneksi->prepare("SELECT jurnal_kegiatan_id FROM nilai_siswa WHERE siswa_id = ? AND jurnal_kegiatan_id IS NOT NULL");
$stmt_nilai->bind_param("i", $siswa_id);
$stmt_nilai->execute();
$result_nilai = $stmt_nilai->get_result();
while ($row = $result_nilai->fetch_assoc()) {
    $graded_jurnal_ids[] = $row['jurnal_kegiatan_id'];
}
$stmt_nilai->close();

// Ambil semua TP statis
$tp_result = $koneksi->query("SELECT * FROM tujuan_pembelajaran ORDER BY id_induk, kode_tp");
$semua_tp = [];
while ($row = $tp_result->fetch_assoc()) {
    $semua_tp[$row['id_tp']] = $row;
}

// Gabungkan data jurnal yang sudah dinilai ke dalam struktur TP
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
        // Kolom No & Tujuan Pembelajaran
        if ($level == 0) {
            $html_rows .= "<td style='text-align: center; {$fontWeight}'>" . htmlspecialchars($item['kode_tp']) . "</td>";
            $html_rows .= "<td style='{$fontWeight}'>" . htmlspecialchars($item['deskripsi_tp']) . "</td>";
        } else {
            $html_rows .= "<td></td>"; // Kolom nomor kosong untuk anak
            $html_rows .= "<td style='padding-left: " . ($padding) . "px;'>- " . htmlspecialchars($item['deskripsi_tp']) . "</td>";
        }

        // Kolom Nilai (dibuat kosong)
        $html_rows .= "<td style='text-align: center; {$fontWeight}'>";
        if ($punya_anak) {
            $html_rows .= 'Rata-rata';
        } else {
            $html_rows .= ''; // Kosong untuk diisi manual
        }
        $html_rows .= "</td>";

        // Kolom Deskripsi (dibuat kosong)
        $html_rows .= "<td></td>";
        $html_rows .= "</tr>";

        if ($punya_anak) {
            $html_rows .= generate_pdf_table_rows_kosong($id_tp, $level + 1, $semua_tp, $tp_anak);
        }
    }
    return $html_rows;
}

// Membuat Konten HTML untuk PDF
$table_content = generate_pdf_table_rows_kosong(0, 0, $semua_tp, $tp_anak);

$html = '
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Formulir Penilaian DUDI</title>
<style>
    @page { margin: 25mm; } body { font-family: Arial, sans-serif; font-size: 11pt; color: #333; }
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
    <h2 style="text-align: center; margin-bottom: 25px;">FORMULIR PENILAIAN KOMPETENSI<br>PRAKTIK KERJA LAPANGAN (PKL)</h2>
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