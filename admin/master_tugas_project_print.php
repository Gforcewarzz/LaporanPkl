<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

require_once 'partials/db.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/* ===================== Ambil Data ===================== */

$id_jurnal_kegiatan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT
            jk.nama_pekerjaan,
            jk.perencanaan_kegiatan,
            jk.pelaksanaan_kegiatan,
            jk.catatan_instruktur,
            jk.gambar,
            jk.tanggal_laporan,
            s.nama_siswa AS nama_peserta,
            tp.nama_tempat_pkl AS dunia_kerja_tempat_pkl
        FROM jurnal_kegiatan jk
        LEFT JOIN siswa s ON jk.siswa_id = s.id_siswa
        LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl
        WHERE jk.id_jurnal_kegiatan = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $id_jurnal_kegiatan);
$stmt->execute();
$result = $stmt->get_result();
$laporan_data = $result->fetch_assoc();
$stmt->close();
$koneksi->close();

/* ===================== Heuristik Gambar di Halaman 1 ===================== */
$hasPhoto   = !empty($laporan_data['gambar']);
$imgWebPath = $hasPhoto ? 'images/' . basename($laporan_data['gambar']) : null;

$lenA = mb_strlen($laporan_data['nama_pekerjaan'] ?? '');
$lenB = mb_strlen($laporan_data['perencanaan_kegiatan'] ?? '');
$lenC = mb_strlen($laporan_data['pelaksanaan_kegiatan'] ?? '');
$totalChars = $lenA + $lenB + $lenC;

function imgMaxHeightMm(int $chars): int
{
    if ($chars <= 400)  return 75;
    if ($chars <= 700)  return 65;
    if ($chars <= 1000) return 55;
    if ($chars <= 1300) return 50;
    return 42;
}
$imgMax = $hasPhoto ? imgMaxHeightMm($totalChars) : 0;

$currentDate = date('d F Y', strtotime($laporan_data['tanggal_laporan'] ?? 'now'));

/* ===================== Render HTML ===================== */
ob_start();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Tugas PKL</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.2cm;
        }

        body {
            font-family: "Times New Roman", serif;
            font-size: 10pt;
            line-height: 1.35;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .wrap {
            width: 178mm;
            margin: 0 auto;
        }

        .header {
            margin-bottom: 4pt;
            text-align: center;
        }

        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            color: #222;
        }

        .subheader {
            font-size: 11pt;
            font-weight: bold;
            margin: 2pt 0 12pt;
            text-align: center;
            text-transform: uppercase;
            color: #444;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10pt;
        }

        .info-table td {
            padding: 2pt 3pt;
            vertical-align: top;
            color: #333;
        }

        .info-table td.label {
            width: 140pt;
            font-weight: bold;
        }

        .section {
            margin-bottom: 10pt;
            page-break-inside: auto;
        }

        .section-title {
            font-weight: bold;
            margin: 4pt 0 3pt;
            font-size: 10.5pt;
            color: #222;
        }

        .content-box {
            border: 0.6pt solid #666;
            padding: 5pt 7pt;
            margin: 0;
            line-height: 1.3;
            text-align: justify;
            white-space: normal;
            word-wrap: break-word;
            color: #333;
            page-break-inside: auto;
        }

        /* === Frame gambar full width, gambar center + padding === */
        .image-on-page1 {
            margin: 6pt 0 8pt;
            border: 1px solid #aaa;
            padding: 6pt;
            /* padding kotak */
            page-break-before: avoid;
            page-break-inside: avoid;
            overflow: hidden;
            text-align: center;
            /* isi gambar center */
        }

        .image-on-page1 img {
            max-width: 95%;
            /* kasih napas dikit */
            max-height: 100%;
            height: auto;
            object-fit: contain;
            display: inline-block;
        }

        .caption {
            font-size: 7pt;
            text-align: center;
            margin-top: 3pt;
            color: #555;
        }

        .signature {
            margin-top: 1.2rem;
            font-size: 10pt;
            line-height: 1.2;
            color: #333;
        }

        .signature table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature .right {
            width: 30%;
            text-align: left;
        }

        /* === D selalu mulai halaman 2 === */
        #section-d {
            page-break-before: always;
        }
    </style>
</head>

<body>
    <div class="wrap">

        <div class="header">
            <h1>CATATAN KEGIATAN PKL</h1>
            <div class="subheader">PESERTA DIDIK SMKN 1 GANTAR</div>
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Nama Peserta Didik</td>
                <td>: <?php echo strtoupper($laporan_data['nama_peserta'] ?? '-'); ?></td>
            </tr>
            <tr>
                <td class="label">Dunia Kerja Tempat PKL</td>
                <td>: <?php echo strtoupper($laporan_data['dunia_kerja_tempat_pkl'] ?? '-'); ?></td>
            </tr>
            <tr>
                <td class="label">Pembimbing Dunia Kerja</td>
                <td>: ........................................</td>
            </tr>
            <tr>
                <td class="label">Guru Pembimbing Sekolah</td>
                <td>: ........................................</td>
            </tr>
        </table>

        <!-- A -->
        <div class="section" id="section-a">
            <div class="section-title">A. Nama Pekerjaan</div>
            <div class="content-box"><?php echo nl2br(htmlspecialchars($laporan_data['nama_pekerjaan'] ?? '-')); ?>
            </div>
        </div>

        <!-- B -->
        <div class="section" id="section-b">
            <div class="section-title">B. Perencanaan Kegiatan</div>
            <div class="content-box">
                <?php echo nl2br(htmlspecialchars($laporan_data['perencanaan_kegiatan'] ?? '-')); ?></div>
        </div>

        <!-- C -->
        <div class="section" id="section-c">
            <div class="section-title">C. Pelaksanaan Kegiatan/Hasil</div>
            <div class="content-box">
                <?php echo nl2br(htmlspecialchars($laporan_data['pelaksanaan_kegiatan'] ?? '-')); ?></div>

            <?php if ($hasPhoto): ?>
                <?php
                $boxH = max(30, $imgMax);
                $imgH = max(26, $imgMax - 4);
                ?>
                <div class="image-on-page1" style="max-height: <?php echo $boxH; ?>mm;">
                    <img src="<?php echo htmlspecialchars($imgWebPath); ?>" alt="Bukti Kegiatan"
                        style="max-height: <?php echo $imgH; ?>mm;">
                    <div class="caption">(Bukti Kegiatan Visual)</div>
                </div>
            <?php endif; ?>
        </div>

        <!-- D -->
        <div class="section" id="section-d">
            <div class="section-title">D. Catatan Instruktur</div>
            <div class="content-box"><?php echo nl2br(htmlspecialchars($laporan_data['catatan_instruktur'] ?? '-')); ?>
            </div>

            <div class="signature">
                <table>
                    <tr>
                        <td></td>
                        <td class="right">
                            <p>...................., <?php echo htmlspecialchars($currentDate); ?></p>
                            <p style="margin-top:1.5rem;margin-left:25px">Pembimbing Dunia Kerja</p>
                            <p style="margin-top:60pt;">(................................................)</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

    </div>
</body>

</html>
<?php
$html = ob_get_clean();

/* ===================== DOMPDF ===================== */
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('chroot', realpath(__DIR__));
$options->set('dpi', 110);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = 'Laporan_Tugas_PKL_' . date('Ymd') . '.pdf';
$dompdf->stream($filename, ["Attachment" => false]);
exit();
